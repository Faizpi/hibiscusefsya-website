<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\PenjualanItem;
use App\Produk;
use App\Gudang;
use App\Kontak;
use App\GudangProduk;
use App\User;
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
        $kontaks = Kontak::all();
        $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();

        return view('penjualan.create', compact('produks', 'gudangs', 'kontaks', 'approvers', 'gudangProduks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'approver_id' => 'required|exists:users,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',

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

        $path = null;

        // Pastikan folder storage public ada
        $publicFolder = public_path('storage/lampiran_penjualan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;

            $file->move($publicFolder, $filename);
            $path = 'lampiran_penjualan/' . $filename;
        }

        // Hitung jatuh tempo
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

        DB::beginTransaction();
        try {
            $penjualanInduk = Penjualan::create([
                'user_id' => Auth::id(),
                'status' => 'Pending',
                'approver_id' => $request->approver_id,
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
                'lampiran_path' => $path,
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

        } catch (\Exception $e) {

            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
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
        $canEdit = false;

        if (in_array($user->role, ['admin', 'super_admin'])) {
            $canEdit = true;
        } elseif ($penjualan->user_id == $user->id && $penjualan->status == 'Pending') {
            $canEdit = true;
        }

        if (!$canEdit) {
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
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
        $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();

        $penjualan->load('items');

        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrutPadded = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $penjualan->custom_number = "INV-{$dateCode}-{$penjualan->user_id}-{$noUrutPadded}";

        return view('penjualan.edit', compact('penjualan', 'produks', 'gudangs', 'kontaks', 'approvers'));
    }

    public function update(Request $request, Penjualan $penjualan)
    {
        $user = Auth::user();
        $canUpdate = false;

        if (in_array($user->role, ['admin', 'super_admin']))
            $canUpdate = true;
        elseif ($penjualan->user_id == $user->id && $penjualan->status == 'Pending')
            $canUpdate = true;

        if (!$canUpdate)
            return back()->with('error', 'Akses ditolak.');

        $request->validate([
            'pelanggan' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'approver_id' => 'required|exists:users,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
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

        $path = $penjualan->lampiran_path;

        // Folder public storage
        $publicFolder = public_path('storage/lampiran_penjualan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {

            // Hapus file lama
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }

            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;

            $file->move($publicFolder, $filename);
            $path = 'lampiran_penjualan/' . $filename;
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

        DB::beginTransaction();

        try {
            $penjualan->update([
                'status' => 'Pending',
                'approver_id' => $request->approver_id,
                'gudang_id' => $request->gudang_id,
                'pelanggan' => $request->pelanggan,
                'email' => $request->email,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => Carbon::parse($request->tgl_transaksi),
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'no_referensi' => $request->no_referensi,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_path' => $path,
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

            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
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
        if ($user->role == 'user')
            return back()->with('error', 'Akses ditolak.');
        if ($user->role == 'admin' && $penjualan->approver_id != $user->id)
            return back()->with('error', 'Bukan wewenang Anda.');

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

            $penjualan->status = 'Approved';
            $penjualan->save();

            DB::commit();

            return redirect()->route('penjualan.index')
                ->with('success', 'Penjualan disetujui. Stok dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cancel(Penjualan $penjualan)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }

        DB::beginTransaction();
        try {
            if ($penjualan->status == 'Approved') {
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

        if (in_array($user->role, ['admin', 'super_admin']))
            $canDelete = true;
        elseif ($penjualan->user_id == $user->id && $penjualan->status == 'Pending')
            $canDelete = true;

        if (!$canDelete)
            return back()->with('error', 'Akses ditolak.');

        if ($penjualan->lampiran_path) {
            $full = public_path('storage/' . $penjualan->lampiran_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $penjualan->delete();

        return redirect()->route('penjualan.index')->with('success', 'Data dihapus.');
    }

    public function show(Penjualan $penjualan)
    {
        $user = Auth::user();
        $allow = false;

        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $penjualan->approver_id == $user->id)
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
        elseif ($user->role == 'admin' && $penjualan->approver_id == $user->id)
            $allow = true;
        elseif ($penjualan->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');

        $penjualan->load('items.produk', 'user', 'gudang', 'approver');
        return view('penjualan.print', compact('penjualan'));
    }
}
