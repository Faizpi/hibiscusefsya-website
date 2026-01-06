<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\PenjualanItem;
use App\Produk;
use App\Gudang;
use App\Kontak;
use App\GudangProduk;
use App\User;
use App\Services\InvoiceEmailService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PenjualanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Penjualan::with(['user', 'gudang', 'approver']);

        if ($user->role == 'super_admin') {
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            // Admin/Spectator hanya lihat transaksi di gudang yang sedang aktif (current_gudang_id)
            $currentGudang = $user->getCurrentGudang();
            if ($currentGudang) {
                $query->where('gudang_id', $currentGudang->id);
            } else {
                // Jika tidak punya gudang, tidak bisa lihat apapun
                // Return empty paginator agar view tidak error
                $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
                return view('penjualan.index', [
                    'penjualans' => $emptyPaginator,
                    'totalBelumDibayar' => 0,
                    'totalTelatDibayar' => 0,
                    'pelunasan30Hari' => 0,
                    'totalCanceled' => 0,
                ]);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        // Clone query untuk summary calculations (semua data)
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        $totalBelumDibayar = $allForSummary->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');

        $totalTelatDibayar = $allForSummary->where('status', 'Approved')
            ->whereNotNull('tgl_jatuh_tempo')
            ->filter(function ($item) {
                return Carbon::parse($item->tgl_jatuh_tempo)->lt(Carbon::now());
            })
            ->sum('grand_total');

        $pelunasan30Hari = $allForSummary->where('status', 'Lunas')
            ->filter(function ($item) {
                return Carbon::parse($item->updated_at)->gte(Carbon::now()->subDays(30));
            })
            ->sum('grand_total');

        $totalCanceled = $allForSummary->where('status', 'Canceled')->count();

        // Paginated data untuk table display
        /** @var \Illuminate\Pagination\LengthAwarePaginator $penjualans */
        $penjualans = $query->latest()->paginate(20);
        $penjualans->getCollection()->transform(function ($item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->custom_number = "INV-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            return $item;
        });

        return view('penjualan.index', [
            'penjualans' => $penjualans,
            'totalBelumDibayar' => $totalBelumDibayar,
            'totalTelatDibayar' => $totalTelatDibayar,
            'pelunasan30Hari' => $pelunasan30Hari,
            'totalCanceled' => $totalCanceled,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('penjualan.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        // Untuk user biasa, hanya tampilkan produk yang ada di gudang mereka
        if ($user->role == 'user' && $user->gudang_id) {
            // Ambil produk yang ada di gudang user (via tabel gudang_produk)
            $produks = Produk::whereHas('stokDiGudang', function ($query) use ($user) {
                $query->where('gudang_id', $user->gudang_id);
            })->get();
            $gudangProduks = null; // User tidak perlu data ini
            $gudangs = Gudang::where('id', $user->gudang_id)->get();
        } elseif ($user->role == 'admin' && $user->current_gudang_id) {
            // Admin hanya lihat gudang yang ditugaskan
            $gudangs = Gudang::where('id', $user->current_gudang_id)->get();
            // Produk sesuai gudang admin
            $produks = Produk::whereHas('stokDiGudang', function ($query) use ($user) {
                $query->where('gudang_id', $user->current_gudang_id);
            })->get();
            $gudangProduks = null;
        } else {
            // Super Admin bisa lihat semua produk dan gudang
            $gudangs = Gudang::all();
            $produks = Produk::all();

            // Siapkan data stok per gudang untuk filter dinamis
            $gudangProduks = GudangProduk::with('produk')
                ->where('stok', '>', 0)
                ->get()
                ->groupBy('gudang_id')
                ->map(function ($items) {
                    return $items->pluck('produk_id')->toArray();
                });
        }

        $kontaks = Kontak::all();

        // Generate preview nomor invoice
        $countToday = Penjualan::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $previewNomor = Penjualan::generateNomor(Auth::id(), $noUrut, Carbon::now());

        return view('penjualan.create', compact('produks', 'gudangs', 'kontaks', 'gudangProduks', 'previewNomor'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('penjualan.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        $request->validate([
            'pelanggan' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',

            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        // VALIDASI STOK: Cek apakah stok mencukupi untuk semua produk
        $gudangId = $request->gudang_id;
        $stokErrors = [];

        foreach ($request->produk_id as $index => $produkId) {
            $qty = $request->kuantitas[$index];

            $stokGudang = GudangProduk::where('gudang_id', $gudangId)
                ->where('produk_id', $produkId)
                ->first();

            $stokTersedia = $stokGudang ? $stokGudang->stok : 0;

            if ($stokTersedia < $qty) {
                $produk = Produk::find($produkId);
                $namaProduk = $produk->nama_produk ?? "ID: $produkId";
                $stokErrors[] = "Stok {$namaProduk} tidak cukup. Tersedia: {$stokTersedia}, Diminta: {$qty}";
            }
        }

        if (!empty($stokErrors)) {
            return redirect()->back()
                ->with('error', implode('<br>', $stokErrors))
                ->withInput();
        }

        // Hitung jatuh tempo dan tentukan status
        $term = $request->syarat_pembayaran;
        $isCash = ($term == 'Cash');

        // Semua transaksi memerlukan approval terlebih dahulu
        $statusAwal = 'Pending';

        if ($isCash) {
            // Cash = tidak perlu jatuh tempo, tapi tetap pending approval
            $tglJatuhTempo = null;
        } else {
            // Kredit = hitung jatuh tempo
            $tglJatuhTempo = Carbon::parse($request->tgl_transaksi);

            if ($term == 'Net 7')
                $tglJatuhTempo->addDays(7);
            elseif ($term == 'Net 14')
                $tglJatuhTempo->addDays(14);
            elseif ($term == 'Net 30')
                $tglJatuhTempo->addDays(30);
            elseif ($term == 'Net 60')
                $tglJatuhTempo->addDays(60);
        }

        // Hitung subtotal
        $subTotal = 0;
        foreach ($request->produk_id as $index => $produkId) {
            $qty = $request->kuantitas[$index];
            $price = $request->harga_satuan[$index];
            $disc = $request->diskon[$index] ?? 0;
            $subTotal += ($qty * $price) * (1 - ($disc / 100));
        }

        $diskonAkhir = $request->diskon_akhir ?? 0;
        $kenaPajak = max(0, $subTotal - $diskonAkhir);
        $pajakPersen = $request->tax_percentage ?? 0;
        $grandTotal = $kenaPajak + ($kenaPajak * ($pajakPersen / 100));

        // Generate nomor urut
        $countToday = Penjualan::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;

        // Generate nomor transaksi
        $nomor = Penjualan::generateNomor(Auth::id(), $noUrut, Carbon::now());

        // Upload lampiran dengan nama sesuai kode invoice
        $lampiranPaths = [];
        $publicFolder = public_path('storage/lampiran_penjualan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        // Handle multiple lampiran (semua file masuk ke lampiran_paths)
        if ($request->hasFile('lampiran')) {
            $counter = 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                // Format: INV-xxx-1.jpg, INV-xxx-2.jpg, etc
                $filename = $nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_penjualan/' . $filename;
                $counter++;
            }
        }

        // Tentukan approver berdasarkan gudang
        $user = Auth::user();
        $approverId = null;

        if ($user->role == 'user') {
            // Sales: cari admin yang handle gudang yang dipilih
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($request) {
                    $q->where('gudang_id', $request->gudang_id)
                        ->orWhereHas('gudangs', function ($sub) use ($request) {
                            $sub->where('gudangs.id', $request->gudang_id);
                        });
                })
                ->first();

            if ($adminGudang) {
                $approverId = $adminGudang->id;
            } else {
                // Jika tidak ada admin gudang, ke super admin
                $superAdmin = User::where('role', 'super_admin')->first();
                $approverId = $superAdmin ? $superAdmin->id : null;
            }
        } elseif ($user->role == 'admin') {
            // Admin: ke super admin
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } elseif ($user->role == 'super_admin') {
            // Super admin: cari admin gudang untuk approver_id (agar tidak null)
            // Meskipun super_admin bisa langsung approved, approver_id tetap harus diisi
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($request) {
                    $q->where('gudang_id', $request->gudang_id)
                        ->orWhere('current_gudang_id', $request->gudang_id)
                        ->orWhereHas('gudangs', function ($sub) use ($request) {
                            $sub->where('gudangs.id', $request->gudang_id);
                        });
                })
                ->first();

            if ($adminGudang) {
                $approverId = $adminGudang->id;
            } else {
                // Jika tidak ada admin gudang, super_admin jadi approver sendiri
                $approverId = $user->id;
            }
        }

        DB::beginTransaction();
        try {
            $penjualanInduk = Penjualan::create([
                'user_id' => Auth::id(),
                'status' => $statusAwal,
                'approver_id' => $approverId, // Set approver saat create
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'gudang_id' => $request->gudang_id,
                'pelanggan' => $request->pelanggan,
                'email' => $request->email,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'no_referensi' => $request->no_referensi,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            // Simpan detail item
            foreach ($request->produk_id as $index => $produkId) {
                $qty = $request->kuantitas[$index];
                $price = $request->harga_satuan[$index];
                $disc = $request->diskon[$index] ?? 0;

                PenjualanItem::create([
                    'penjualan_id' => $penjualanInduk->id,
                    'produk_id' => $produkId,
                    'deskripsi' => $request->deskripsi[$index] ?? null,
                    'kuantitas' => $qty,
                    'unit' => $request->unit[$index] ?? null,
                    'harga_satuan' => $price,
                    'diskon' => $disc,
                    'jumlah_baris' => ($qty * $price) * (1 - ($disc / 100)),
                ]);
            }

            DB::commit();

            // Kirim notifikasi email ke pembuat + approvers
            InvoiceEmailService::sendCreatedNotification($penjualanInduk, 'penjualan');

        } catch (\Exception $e) {

            // Hapus semua lampiran jika error
            foreach ($lampiranPaths as $lampiranPath) {
                if (File::exists(public_path('storage/' . $lampiranPath))) {
                    File::delete(public_path('storage/' . $lampiranPath));
                }
            }

            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('penjualan.index')
            ->with('success', 'Penjualan berhasil diajukan.');
    }

    public function edit(Penjualan $penjualan)
    {
        $user = Auth::user();

        // Only super_admin dapat mengedit
        if ($user->role !== 'super_admin') {
            return redirect()->route('penjualan.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data penjualan.');
        }

        // Untuk user biasa, hanya tampilkan produk yang ada di gudang mereka
        if ($user->role == 'user' && $user->gudang_id) {
            $produks = Produk::whereHas('stokDiGudang', function ($query) use ($user) {
                $query->where('gudang_id', $user->gudang_id);
            })->get();
        } else {
            $produks = Produk::all();
        }

        $gudangs = Gudang::all();
        $kontaks = Kontak::all();

        $penjualan->load('items');

        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrutPadded = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $penjualan->custom_number = "INV-{$dateCode}-{$penjualan->user_id}-{$noUrutPadded}";

        return view('penjualan.edit', compact('penjualan', 'produks', 'gudangs', 'kontaks'));
    }

    public function update(Request $request, Penjualan $penjualan)
    {
        $user = Auth::user();
        $canUpdate = false;

        // Admin tidak boleh mengedit/update
        if ($user->role === 'admin') {
            return back()->with('error', 'Admin tidak diperbolehkan mengubah data penjualan.');
        }

        if ($user->role === 'super_admin')
            $canUpdate = true;

        if (!$canUpdate)
            return back()->with('error', 'Akses ditolak.');

        $request->validate([
            'pelanggan' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        // VALIDASI STOK: Cek apakah stok mencukupi untuk semua produk
        $gudangId = $request->gudang_id;
        $stokErrors = [];

        foreach ($request->produk_id as $index => $produkId) {
            $qty = $request->kuantitas[$index];

            $stokGudang = GudangProduk::where('gudang_id', $gudangId)
                ->where('produk_id', $produkId)
                ->first();

            $stokTersedia = $stokGudang ? $stokGudang->stok : 0;

            if ($stokTersedia < $qty) {
                $produk = Produk::find($produkId);
                $namaProduk = $produk->nama_produk ?? "ID: $produkId";
                $stokErrors[] = "Stok {$namaProduk} tidak cukup. Tersedia: {$stokTersedia}, Diminta: {$qty}";
            }
        }

        if (!empty($stokErrors)) {
            return redirect()->back()
                ->with('error', implode('<br>', $stokErrors))
                ->withInput();
        }

        $lampiranPaths = $penjualan->lampiran_paths ?? [];
        $newUploadedPaths = []; // Track newly uploaded files for cleanup on error

        // Folder public storage
        $publicFolder = public_path('storage/lampiran_penjualan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        // Handle multiple lampiran baru - append ke existing
        if ($request->hasFile('lampiran')) {
            // Hitung counter dari existing lampiran_paths
            $counter = count($lampiranPaths) + 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                // Format: INV-xxx-1.jpg, INV-xxx-2.jpg, etc
                $filename = $penjualan->nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $newPath = 'lampiran_penjualan/' . $filename;
                $lampiranPaths[] = $newPath;
                $newUploadedPaths[] = $newPath;
                $counter++;
            }
        }

        // Hitung subtotal
        $subTotal = 0;
        foreach ($request->produk_id as $index => $produkId) {
            $qty = $request->kuantitas[$index];
            $price = $request->harga_satuan[$index];
            $disc = $request->diskon[$index] ?? 0;

            $subTotal += ($qty * $price) * (1 - ($disc / 100));
        }

        $diskonAkhir = $request->diskon_akhir ?? 0;
        $kenaPajak = max(0, $subTotal - $diskonAkhir);
        $pajakPersen = $request->tax_percentage ?? 0;
        $grandTotal = $kenaPajak + ($kenaPajak * ($pajakPersen / 100));

        // Hitung jatuh tempo dan tentukan status berdasarkan metode pembayaran
        $term = $request->syarat_pembayaran;
        $isCash = ($term == 'Cash');

        if ($isCash) {
            // Cash = langsung lunas, tidak perlu jatuh tempo
            $tglJatuhTempo = null;
            $statusBaru = 'Lunas';
        } else {
            // Kredit = perlu approval, hitung jatuh tempo
            $tglJatuhTempo = Carbon::parse($request->tgl_transaksi);
            $statusBaru = 'Pending';

            if ($term == 'Net 7')
                $tglJatuhTempo->addDays(7);
            elseif ($term == 'Net 14')
                $tglJatuhTempo->addDays(14);
            elseif ($term == 'Net 30')
                $tglJatuhTempo->addDays(30);
            elseif ($term == 'Net 60')
                $tglJatuhTempo->addDays(60);
        }

        // Tentukan approver jika status pending (sama seperti store)
        $approverId = $penjualan->approver_id; // Keep existing approver

        if ($statusBaru == 'Pending') {
            // Re-calculate approver berdasarkan gudang yang dipilih
            if ($user->role == 'user') {
                $adminGudang = User::where('role', 'admin')
                    ->where(function ($q) use ($request) {
                        $q->where('gudang_id', $request->gudang_id)
                            ->orWhereHas('gudangs', function ($sub) use ($request) {
                                $sub->where('gudangs.id', $request->gudang_id);
                            });
                    })
                    ->first();

                if ($adminGudang) {
                    $approverId = $adminGudang->id;
                } else {
                    $superAdmin = User::where('role', 'super_admin')->first();
                    $approverId = $superAdmin ? $superAdmin->id : null;
                }
            } elseif ($user->role == 'admin') {
                $superAdmin = User::where('role', 'super_admin')->first();
                $approverId = $superAdmin ? $superAdmin->id : null;
            }
        }

        DB::beginTransaction();

        try {
            $penjualan->update([
                'status' => $statusBaru,
                'approver_id' => $approverId,
                'gudang_id' => $request->gudang_id,
                'pelanggan' => $request->pelanggan,
                'email' => $request->email,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'no_referensi' => $request->no_referensi,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $penjualan->items()->delete();

            foreach ($request->produk_id as $index => $produkId) {
                $qty = $request->kuantitas[$index];
                $price = $request->harga_satuan[$index];
                $disc = $request->diskon[$index] ?? 0;

                PenjualanItem::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $produkId,
                    'deskripsi' => $request->deskripsi[$index] ?? null,
                    'kuantitas' => $qty,
                    'unit' => $request->unit[$index] ?? null,
                    'harga_satuan' => $price,
                    'diskon' => $disc,
                    'jumlah_baris' => ($qty * $price) * (1 - ($disc / 100)),
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            // Hapus file baru yang diupload jika error
            foreach ($newUploadedPaths as $newPath) {
                if (File::exists(public_path('storage/' . $newPath))) {
                    File::delete(public_path('storage/' . $newPath));
                }
            }

            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('penjualan.index')
            ->with('success', 'Data penjualan berhasil diperbarui.');
    }

    public function approve(Penjualan $penjualan)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin']))
            return back()->with('error', 'Akses ditolak.');

        if ($penjualan->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan, tidak bisa di-approve.');
        }

        if ($user->role === 'admin' && in_array($penjualan->status, ['Approved', 'Lunas'])) {
            return back()->with('error', 'Transaksi sudah disetujui. Admin tidak bisa melakukan approve ulang.');
        }

        // Admin/super admin hanya bisa approve di gudang yang dia pegang
        if ($user->role == 'admin') {
            if (!$user->canAccessGudang($penjualan->gudang_id)) {
                return back()->with('error', 'Anda hanya bisa approve di gudang yang Anda pegang.');
            }
        }

        $gudangId = $penjualan->gudang_id;
        if (!$gudangId)
            return back()->with('error', 'Gagal! Transaksi ini tidak terhubung ke gudang manapun.');

        DB::beginTransaction();
        try {
            foreach ($penjualan->items as $item) {

                // lockForUpdate() untuk mencegah race condition
                $stok = GudangProduk::where('gudang_id', $gudangId)
                    ->where('produk_id', $item->produk_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stok || $stok->stok < $item->kuantitas) {
                    $namaProduk = $item->produk->nama_produk ?? 'ID: ' . $item->produk_id;
                    throw new \Exception("Stok tidak cukup untuk produk: $namaProduk");
                }

                $stok->decrement('stok', $item->kuantitas);
            }

            // Set approver_id ke user yang sedang approve
            $penjualan->approver_id = $user->id;

            // Set status ke Approved (Cash juga harus ditandai manual sebagai Lunas)
            $penjualan->status = 'Approved';
            $penjualan->save();

            DB::commit();

            // Kirim notifikasi email ke pembuat bahwa transaksi telah disetujui
            InvoiceEmailService::sendApprovedNotification($penjualan, 'penjualan');

            return redirect()->route('penjualan.index')
                ->with('success', 'Penjualan disetujui. Stok dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Return HTML struk untuk di-screenshot/print sebagai image
     * Untuk iWare: Screenshot → Image Mode → Print
     */
    public function printJson(Penjualan $penjualan)
    {
        // HTML akan di-render jadi image oleh html2canvas di client side
        return view('penjualan.struk', compact('penjualan'));
    }

    public function cancel(Penjualan $penjualan)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($penjualan->status === 'Canceled') {
            return redirect()->route('penjualan.index')->with('error', 'Transaksi sudah dibatalkan.');
        }

        // Hanya super_admin yang bisa cancel Approved/Lunas
        if (in_array($penjualan->status, ['Approved', 'Lunas']) && $user->role !== 'super_admin') {
            return redirect()->route('penjualan.index')->with('error', 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.');
        }

        DB::beginTransaction();
        try {
            // Kembalikan stok jika sudah Approved atau Lunas (stok sudah dikurangi)
            if (in_array($penjualan->status, ['Approved', 'Lunas'])) {
                foreach ($penjualan->items as $item) {
                    // lockForUpdate() untuk mencegah race condition
                    $stok = GudangProduk::where('gudang_id', $penjualan->gudang_id)
                        ->where('produk_id', $item->produk_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stok) {
                        $stok->increment('stok', $item->kuantitas);
                    } else {
                        GudangProduk::create([
                            'gudang_id' => $penjualan->gudang_id,
                            'produk_id' => $item->produk_id,
                            'stok' => $item->kuantitas
                        ]);
                    }
                }
            }

            $penjualan->status = 'Canceled';
            $penjualan->save();

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Transaksi dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }

    /**
     * Uncancel - Mengembalikan transaksi yang dibatalkan ke status Pending
     * Hanya super_admin yang bisa melakukan uncancel
     */
    public function uncancel(Penjualan $penjualan)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan pembatalan.');
        }

        if ($penjualan->status !== 'Canceled') {
            return back()->with('error', 'Transaksi ini tidak dalam status Canceled.');
        }

        // Tentukan approver berdasarkan gudang transaksi
        $gudangId = $penjualan->gudang_id;
        $approverId = null;

        // Cari admin yang handle gudang ini
        $adminGudang = User::where('role', 'admin')
            ->where(function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId)
                    ->orWhereHas('gudangs', function ($sub) use ($gudangId) {
                        $sub->where('gudangs.id', $gudangId);
                    });
            })
            ->first();

        if ($adminGudang) {
            $approverId = $adminGudang->id;
        } else {
            // Fallback ke super admin yang melakukan uncancel
            $approverId = $user->id;
        }

        // Set status kembali ke Pending agar perlu approve ulang
        $penjualan->status = 'Pending';
        $penjualan->approver_id = $approverId;
        $penjualan->save();

        return redirect()->route('penjualan.index')
            ->with('success', 'Transaksi berhasil di-uncancel. Status kembali ke Pending dan perlu di-approve ulang.');
    }

    /**
     * Delete specific lampiran by index
     */
    public function deleteLampiran(Penjualan $penjualan, $index)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus lampiran.');
        }

        $lampiran = $penjualan->lampiran_paths ?? [];

        if (!isset($lampiran[$index])) {
            return back()->with('error', 'Lampiran tidak ditemukan.');
        }

        // Delete file
        $filePath = public_path('storage/' . $lampiran[$index]);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Remove from array
        unset($lampiran[$index]);
        $penjualan->lampiran_paths = array_values($lampiran); // Re-index array
        $penjualan->save();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function markAsPaid(Penjualan $penjualan)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }
        $penjualan->status = 'Lunas';
        $penjualan->save();
        return redirect()->route('penjualan.index')
            ->with('success', 'Penjualan ditandai LUNAS.');
    }

    public function destroy(Penjualan $penjualan)
    {
        $user = Auth::user();
        $canDelete = false;

        if ($user->role === 'super_admin') {
            $canDelete = true;
        }

        if (!$canDelete)
            return back()->with('error', 'Akses ditolak.');

        DB::beginTransaction();
        try {
            // Kembalikan stok jika transaksi sudah Approved atau Lunas
            if (in_array($penjualan->status, ['Approved', 'Lunas'])) {
                foreach ($penjualan->items as $item) {
                    $stok = GudangProduk::where('gudang_id', $penjualan->gudang_id)
                        ->where('produk_id', $item->produk_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stok) {
                        $stok->increment('stok', $item->kuantitas);
                    } else {
                        GudangProduk::create([
                            'gudang_id' => $penjualan->gudang_id,
                            'produk_id' => $item->produk_id,
                            'stok' => $item->kuantitas
                        ]);
                    }
                }
            }

            // Hapus lampiran
            if ($penjualan->lampiran_path) {
                $full = public_path('storage/' . $penjualan->lampiran_path);
                if (File::exists($full)) {
                    File::delete($full);
                }
            }

            // Hapus multiple lampiran
            if ($penjualan->lampiran_paths) {
                foreach ($penjualan->lampiran_paths as $path) {
                    $full = public_path('storage/' . $path);
                    if (File::exists($full)) {
                        File::delete($full);
                    }
                }
            }

            $penjualan->delete();

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Data dihapus. Stok telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function show(Penjualan $penjualan)
    {
        $user = Auth::user();
        $allow = false;

        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $user->canAccessGudang($penjualan->gudang_id))
            $allow = true;
        elseif ($penjualan->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');

        $penjualan->load('items.produk', 'user', 'gudang', 'approver');

        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrutPadded = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $penjualan->custom_number = "INV-{$dateCode}-{$penjualan->user_id}-{$noUrutPadded}";

        return view('penjualan.show', compact('penjualan'));
    }

    public function print(Penjualan $penjualan)
    {
        $user = Auth::user();
        $allow = false;

        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $user->canAccessGudang($penjualan->gudang_id))
            $allow = true;
        elseif ($penjualan->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');

        $penjualan->load('items.produk', 'user', 'gudang', 'approver');
        return view('penjualan.print', compact('penjualan'));
    }
}
