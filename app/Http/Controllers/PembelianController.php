<?php

namespace App\Http\Controllers;

use App\Pembelian;
use App\PembelianItem;
use App\Produk;
use App\Gudang;
use App\GudangProduk;
use App\User;
use App\Kontak;
use App\Services\InvoiceEmailService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PembelianController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Pembelian::with(['user', 'gudang', 'approver']);

        if ($user->role == 'super_admin') {
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            // Admin/Spectator hanya lihat transaksi di gudang yang sedang aktif (current_gudang_id)
            $currentGudang = $user->getCurrentGudang();
            if ($currentGudang) {
                $query->where('gudang_id', $currentGudang->id);
            } else {
                // Jika tidak punya gudang, tidak bisa lihat apapun
                $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
                return view('pembelian.index', [
                    'pembelians' => $emptyPaginator,
                    'fakturPending' => 0,
                    'fakturBelumDibayar' => 0,
                    'fakturCanceled' => 0,
                    'fakturTelatBayar' => 0,
                ]);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        // Clone query untuk summary calculations (semua data)
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        $fakturPending = $allForSummary->where('status', 'Pending')->sum('grand_total');
        $fakturBelumDibayar = $allForSummary->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');
        $fakturCanceled = $allForSummary->where('status', 'Canceled')->count();
        $fakturTelatBayar = $allForSummary->where('status', 'Approved')
            ->whereNotNull('tgl_jatuh_tempo')
            ->filter(function ($item) {
                return Carbon::parse($item->tgl_jatuh_tempo)->lt(Carbon::now());
            })
            ->sum('grand_total');

        // Paginated data untuk table display
        /** @var \Illuminate\Pagination\LengthAwarePaginator $pembelians */
        $pembelians = $query->latest()->paginate(20);
        $pembelians->getCollection()->transform(function ($item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->custom_number = "PR-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            return $item;
        });

        return view('pembelian.index', [
            'pembelians' => $pembelians,
            'fakturPending' => $fakturPending,
            'fakturBelumDibayar' => $fakturBelumDibayar,
            'fakturCanceled' => $fakturCanceled,
            'fakturTelatBayar' => $fakturTelatBayar,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('pembelian.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
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

        // Generate preview nomor invoice
        $countToday = Pembelian::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $previewNomor = Pembelian::generateNomor(Auth::id(), $noUrut, Carbon::now());

        return view('pembelian.create', compact('produks', 'gudangs', 'gudangProduks', 'previewNomor'));
    }

    public function store(Request $request)
    {
        \Log::info('PembelianController@store - START', ['user_id' => Auth::id(), 'request_data' => $request->all()]);

        // Spectator tidak bisa membuat transaksi
        if (Auth::user()->role === 'spectator') {
            return redirect()->route('pembelian.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        if (Auth::user()->role == 'user' && !Auth::user()->gudang_id) {
            \Log::warning('PembelianController@store - User tanpa gudang', ['user_id' => Auth::id()]);
            return back()->with('error', 'Gagal: Akun Anda belum terhubung ke Gudang manapun. Hubungi Super Admin.')->withInput();
        }

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'urgensi' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas' => 'required|array|min:1',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan' => 'required|array|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        // Hitung jatuh tempo dan tentukan status berdasarkan metode pembayaran
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

        $countToday = Pembelian::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;

        // Generate nomor transaksi
        $nomor = Pembelian::generateNomor(Auth::id(), $noUrut, Carbon::now());

        // Upload lampiran dengan nama sesuai kode invoice
        $path = null;
        $publicFolder = public_path('storage/lampiran_pembelian');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            // Gunakan nomor invoice sebagai nama file
            $filename = $nomor . '.' . $extension;
            $file->move($publicFolder, $filename);
            $path = 'lampiran_pembelian/' . $filename;
        }

        // Tentukan approver berdasarkan gudang (sama seperti penjualan)
        $user = Auth::user();
        $approverId = null;
        $stafPenyetuju = null;

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
                $stafPenyetuju = $adminGudang->name;
            } else {
                // Jika tidak ada admin gudang, ke super admin
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $approverId = $superAdmin->id;
                    $stafPenyetuju = $superAdmin->name;
                }
            }
        } elseif ($user->role == 'admin') {
            // Admin: ke super admin
            $superAdmin = User::where('role', 'super_admin')->first();
            if ($superAdmin) {
                $approverId = $superAdmin->id;
                $stafPenyetuju = $superAdmin->name;
            }
        }

        DB::beginTransaction();
        try {
            $pembelianInduk = Pembelian::create([
                'user_id' => Auth::id(),
                'status' => $statusAwal,
                'approver_id' => $approverId, // Set approver saat create
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'gudang_id' => $request->gudang_id,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'staf_penyetuju' => $stafPenyetuju,
                'lampiran_path' => $path,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            foreach ($request->produk_id as $index => $produkId) {
                $qty = $request->kuantitas[$index];
                $price = $request->harga_satuan[$index];
                $disc = $request->diskon[$index] ?? 0;
                $jumlahBaris = ($qty * $price) * (1 - ($disc / 100));

                PembelianItem::create([
                    'pembelian_id' => $pembelianInduk->id,
                    'produk_id' => $produkId,
                    'deskripsi' => $request->deskripsi[$index] ?? null,
                    'kuantitas' => $qty,
                    'unit' => $request->unit[$index] ?? null,
                    'harga_satuan' => $price,
                    'diskon' => $disc,
                    'jumlah_baris' => $jumlahBaris,
                ]);
            }
            DB::commit();

            // Kirim notifikasi email ke pembuat + approvers
            InvoiceEmailService::sendCreatedNotification($pembelianInduk, 'pembelian');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }
            \Log::error('PembelianController@store - ERROR', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        \Log::info('PembelianController@store - SUCCESS', ['pembelian_id' => $pembelianInduk->id]);
        return redirect()->route('pembelian.index')->with('success', 'Permintaan pembelian berhasil diajukan.');
    }

    public function edit(Pembelian $pembelian)
    {
        $user = Auth::user();

        // Only super_admin dapat mengedit
        if ($user->role !== 'super_admin') {
            return redirect()->route('pembelian.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data pembelian.');
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
        $pembelian->load('items');

        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrutPadded = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $pembelian->custom_number = "PR-{$dateCode}-{$pembelian->user_id}-{$noUrutPadded}";

        return view('pembelian.edit', compact('pembelian', 'produks', 'gudangs', 'kontaks'));
    }

    public function update(Request $request, Pembelian $pembelian)
    {
        $user = Auth::user();

        // Admin tidak boleh mengedit/update
        if ($user->role === 'admin') {
            return redirect()->route('pembelian.index')->with('error', 'Admin tidak diperbolehkan mengubah data pembelian.');
        }
        $canUpdate = false;
        if ($user->role === 'super_admin') {
            $canUpdate = true;
        }

        if (!$canUpdate)
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'urgensi' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        $path = $pembelian->lampiran_path;
        $publicFolder = public_path('storage/lampiran_pembelian');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $file->move($publicFolder, $filename);
            $path = 'lampiran_pembelian/' . $filename;
        }

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

        // Tentukan approver jika status pending
        $approverId = $pembelian->approver_id; // Keep existing

        if ($statusBaru == 'Pending') {
            // Re-calculate approver berdasarkan gudang
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
            $pembelian->update([
                'status' => $statusBaru,
                'approver_id' => $approverId,
                'gudang_id' => $request->gudang_id,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $pembelian->items()->delete();

            foreach ($request->produk_id as $index => $produkId) {
                $qty = $request->kuantitas[$index];
                $price = $request->harga_satuan[$index];
                $disc = $request->diskon[$index] ?? 0;
                $jumlahBaris = ($qty * $price) * (1 - ($disc / 100));

                PembelianItem::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $produkId,
                    'deskripsi' => $request->deskripsi[$index] ?? null,
                    'kuantitas' => $qty,
                    'unit' => $request->unit[$index] ?? null,
                    'harga_satuan' => $price,
                    'diskon' => $disc,
                    'jumlah_baris' => $jumlahBaris,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil diperbarui.');
    }

    /**
     * Return HTML struk untuk di-screenshot/print sebagai image
     * Untuk iWare: Screenshot → Image Mode → Print
     * URL: /pembelian/{pembelian}/print-json
     */
    public function printJson(Pembelian $pembelian)
    {
        // HTML akan di-render jadi image oleh html2canvas di client side
        return view('pembelian.struk', compact('pembelian'));
    }

    public function approve(Pembelian $pembelian)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin']))
            return back()->with('error', 'Akses ditolak.');

        if ($pembelian->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan, tidak bisa di-approve.');
        }

        if ($user->role === 'admin' && in_array($pembelian->status, ['Approved', 'Lunas'])) {
            return back()->with('error', 'Transaksi sudah disetujui. Admin tidak bisa melakukan approve ulang.');
        }

        // Admin/super admin hanya bisa approve di gudang yang dia pegang
        if ($user->role == 'admin') {
            if (!$user->canAccessGudang($pembelian->gudang_id)) {
                return back()->with('error', 'Anda hanya bisa approve di gudang yang Anda pegang.');
            }
        }

        DB::beginTransaction();
        try {
            foreach ($pembelian->items as $item) {
                // lockForUpdate() untuk mencegah race condition
                $stok = GudangProduk::where('gudang_id', $pembelian->gudang_id)
                    ->where('produk_id', $item->produk_id)
                    ->lockForUpdate()
                    ->first();

                if ($stok) {
                    $stok->increment('stok', $item->kuantitas);
                } else {
                    GudangProduk::create([
                        'gudang_id' => $pembelian->gudang_id,
                        'produk_id' => $item->produk_id,
                        'stok' => $item->kuantitas
                    ]);
                }
            }

            // Set approver_id ke user yang sedang approve
            $pembelian->approver_id = $user->id;

            // Jika cash, langsung set status Lunas, jika tidak cash set ke Approved
            if ($pembelian->syarat_pembayaran == 'Cash') {
                $pembelian->status = 'Lunas';
            } else {
                $pembelian->status = 'Approved';
            }
            $pembelian->save();
            DB::commit();

            // Kirim notifikasi email ke pembuat bahwa transaksi telah disetujui
            InvoiceEmailService::sendApprovedNotification($pembelian, 'pembelian');

            return back()->with('success', 'Disetujui. Stok ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Pembelian $pembelian)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin']))
            return back()->with('error', 'Akses ditolak.');

        if ($pembelian->status === 'Canceled') {
            return redirect()->route('pembelian.index')->with('error', 'Transaksi sudah dibatalkan.');
        }

        // Hanya super_admin yang bisa cancel Approved/Lunas
        if (in_array($pembelian->status, ['Approved', 'Lunas']) && $user->role !== 'super_admin') {
            return redirect()->route('pembelian.index')->with('error', 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.');
        }

        DB::beginTransaction();
        try {
            if ($pembelian->status == 'Approved') {
                foreach ($pembelian->items as $item) {
                    // lockForUpdate() untuk mencegah race condition
                    $stok = GudangProduk::where('gudang_id', $pembelian->gudang_id)
                        ->where('produk_id', $item->produk_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stok && $stok->stok >= $item->kuantitas) {
                        $stok->decrement('stok', $item->kuantitas);
                    }
                }
            }

            $pembelian->status = 'Canceled';
            $pembelian->save();

            DB::commit();
            return back()->with('success', 'Transaksi dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }

    /**
     * Uncancel - Mengembalikan transaksi yang dibatalkan ke status Pending
     * Hanya super_admin yang bisa melakukan uncancel
     */
    public function uncancel(Pembelian $pembelian)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan pembatalan.');
        }

        if ($pembelian->status !== 'Canceled') {
            return back()->with('error', 'Transaksi ini tidak dalam status Canceled.');
        }

        // Set status kembali ke Pending agar perlu approve ulang
        $pembelian->status = 'Pending';
        $pembelian->approver_id = null; // Reset approver
        $pembelian->save();

        return redirect()->route('pembelian.index')
            ->with('success', 'Transaksi berhasil di-uncancel. Status kembali ke Pending dan perlu di-approve ulang.');
    }

    /**
     * Delete specific lampiran by index
     */
    public function deleteLampiran(Pembelian $pembelian, $index)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus lampiran.');
        }

        $lampiran = $pembelian->lampiran_paths ?? [];

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
        $pembelian->lampiran_paths = array_values($lampiran); // Re-index array
        $pembelian->save();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function destroy(Pembelian $pembelian)
    {
        $user = Auth::user();
        $canDelete = false;
        if ($user->role === 'super_admin') {
            $canDelete = true;
        }
        if (!$canDelete)
            return back()->with('error', 'Akses ditolak.');

        if ($pembelian->lampiran_path) {
            $full = public_path('storage/' . $pembelian->lampiran_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $pembelian->delete();
        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil dihapus.');
    }

    public function show(Pembelian $pembelian)
    {
        $user = Auth::user();
        $allow = false;
        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $user->canAccessGudang($pembelian->gudang_id))
            $allow = true;
        elseif ($pembelian->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');

        $pembelian->load('items.produk', 'user', 'gudang', 'approver');
        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrutPadded = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $pembelian->custom_number = "PR-{$dateCode}-{$pembelian->user_id}-{$noUrutPadded}";

        return view('pembelian.show', compact('pembelian'));
    }

    public function print(Pembelian $pembelian)
    {
        $user = Auth::user();
        $allow = false;
        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $user->canAccessGudang($pembelian->gudang_id))
            $allow = true;
        elseif ($pembelian->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');

        $pembelian->load('items.produk', 'user', 'gudang', 'approver');
        return view('pembelian.print', compact('pembelian'));
    }
}
