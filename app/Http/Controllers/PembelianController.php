<?php

namespace App\Http\Controllers;

use App\Pembelian;
use App\PembelianItem;
use App\Produk;
use App\Gudang;
use App\GudangProduk;
use App\User;
use App\Kontak;
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
        } elseif ($user->role == 'admin') {
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
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

        // Untuk user biasa, hanya tampilkan produk yang ada di gudang mereka
        if ($user->role == 'user' && $user->gudang_id) {
            // Ambil produk yang ada di gudang user (via tabel gudang_produk)
            $produks = Produk::whereHas('stokDiGudang', function ($query) use ($user) {
                $query->where('gudang_id', $user->gudang_id);
            })->get();
            $gudangProduks = null; // User tidak perlu data ini
        } else {
            // Admin & Super Admin bisa lihat semua produk
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

        $gudangs = Gudang::all();
        $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();

        return view('pembelian.create', compact('produks', 'gudangs', 'approvers', 'gudangProduks'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role == 'user' && !Auth::user()->gudang_id) {
            return back()->with('error', 'Gagal: Akun Anda belum terhubung ke Gudang manapun. Hubungi Super Admin.')->withInput();
        }

        $request->validate([
            'approver_id' => 'required|exists:users,id',
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

        $adminUser = User::findOrFail($request->approver_id);
        $namaStaf = $adminUser->name;
        $emailStaf = $adminUser->email;

        $path = null;
        $publicFolder = public_path('storage/lampiran_pembelian');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $file->move($publicFolder, $filename);
            $path = 'lampiran_pembelian/' . $filename;
        }

        $tglJatuhTempo = Carbon::parse($request->tgl_transaksi);
        $term = $request->syarat_pembayaran;
        if ($term == 'Net 7')
            $tglJatuhTempo->addDays(7);
        elseif ($term == 'Net 14')
            $tglJatuhTempo->addDays(14);
        elseif ($term == 'Net 30')
            $tglJatuhTempo->addDays(30);
        elseif ($term == 'Net 60')
            $tglJatuhTempo->addDays(60);

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

        DB::beginTransaction();
        try {
            $pembelianInduk = Pembelian::create([
                'user_id' => Auth::id(),
                'status' => 'Pending',
                'approver_id' => $request->approver_id,
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'gudang_id' => $request->gudang_id,
                'staf_penyetuju' => $namaStaf,
                'email_penyetuju' => $emailStaf,
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
        } catch (\Exception $e) {
            DB::rollBack();
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('pembelian.index')->with('success', 'Permintaan pembelian berhasil diajukan.');
    }

    public function edit(Pembelian $pembelian)
    {
        $user = Auth::user();
        $canEdit = false;

        if (in_array($user->role, ['admin', 'super_admin'])) {
            $canEdit = true;
        } elseif ($pembelian->user_id == $user->id && $pembelian->status == 'Pending') {
            $canEdit = true;
        }

        if (!$canEdit) {
            return redirect()->route('pembelian.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data ini.');
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
        $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();
        $kontaks = Kontak::all();
        $pembelian->load('items');

        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrutPadded = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $pembelian->custom_number = "PR-{$dateCode}-{$pembelian->user_id}-{$noUrutPadded}";

        return view('pembelian.edit', compact('pembelian', 'produks', 'gudangs', 'approvers', 'kontaks'));
    }

    public function update(Request $request, Pembelian $pembelian)
    {
        $user = Auth::user();
        $canUpdate = false;
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $canUpdate = true;
        } elseif ($pembelian->user_id == $user->id && $pembelian->status == 'Pending') {
            $canUpdate = true;
        }

        if (!$canUpdate)
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');

        $request->validate([
            'approver_id' => 'required|exists:users,id',
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

        $adminUser = User::findOrFail($request->approver_id);
        $namaStaf = $adminUser->name;
        $emailStaf = $adminUser->email;

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

        $tglJatuhTempo = Carbon::parse($request->tgl_transaksi);
        $term = $request->syarat_pembayaran;
        if ($term == 'Net 7')
            $tglJatuhTempo->addDays(7);
        elseif ($term == 'Net 14')
            $tglJatuhTempo->addDays(14);
        elseif ($term == 'Net 30')
            $tglJatuhTempo->addDays(30);
        elseif ($term == 'Net 60')
            $tglJatuhTempo->addDays(60);

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

        DB::beginTransaction();
        try {
            $pembelian->update([
                'status' => 'Pending',
                'approver_id' => $request->approver_id,
                'gudang_id' => $request->gudang_id,
                'staf_penyetuju' => $namaStaf,
                'email_penyetuju' => $emailStaf,
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

    public function approve(Pembelian $pembelian)
    {
        $user = Auth::user();
        if ($user->role == 'user')
            return back()->with('error', 'Akses ditolak.');
        if ($user->role == 'admin' && $pembelian->approver_id != $user->id)
            return back()->with('error', 'Bukan wewenang Anda.');

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
            $pembelian->status = 'Approved';
            $pembelian->save();
            DB::commit();
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

    public function destroy(Pembelian $pembelian)
    {
        $user = Auth::user();
        $canDelete = false;
        if (in_array($user->role, ['admin', 'super_admin']))
            $canDelete = true;
        elseif ($pembelian->user_id == $user->id && $pembelian->status == 'Pending')
            $canDelete = true;

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
        elseif ($user->role == 'admin' && $pembelian->approver_id == $user->id)
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
        elseif ($user->role == 'admin' && $pembelian->approver_id == $user->id)
            $allow = true;
        elseif ($pembelian->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');

        $pembelian->load('items.produk', 'user', 'gudang', 'approver');
        return view('pembelian.print', compact('pembelian'));
    }
}
