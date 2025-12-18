<?php

namespace App\Http\Controllers;

use App\Gudang;
use App\Produk;
use App\GudangProduk;
use App\StokLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StokController extends Controller
{
    public function index()
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = Auth::user();

        // Filter gudang sesuai akses user
        if ($user->role == 'super_admin') {
            $gudangs = Gudang::all();
            $gudangsWithStok = Gudang::with('produkStok.produk')->get();
        } else {
            // Admin: hanya gudang yang dia punya akses (pivot admin_gudang)
            $gudangs = $user->gudangs()->with('produkStok.produk')->get();
            $gudangsWithStok = $gudangs;
        }

        // Produk tetap semua (hanya super_admin yang pakai form create)
        $produks = Produk::all();

        return view('stok.index', compact('gudangs', 'produks', 'gudangsWithStok'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role != 'super_admin') {
            return redirect()->route('stok.index')->with('error', 'Hanya Super Admin yang boleh mengubah stok manual.');
        }

        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'produk_id' => 'required|exists:produks,id',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:500'
        ]);

        // Ambil data produk dan gudang untuk logging
        $produk = Produk::findOrFail($request->produk_id);
        $gudang = Gudang::findOrFail($request->gudang_id);
        $user = Auth::user();

        // Cek stok sebelumnya
        $existing = GudangProduk::where('gudang_id', $request->gudang_id)
            ->where('produk_id', $request->produk_id)
            ->first();

        $stokSebelum = $existing ? $existing->stok : 0;
        $stokSesudah = $request->stok;
        $selisih = $stokSesudah - $stokSebelum;

        // Update atau create stok
        $gudangProduk = GudangProduk::updateOrCreate(
            ['gudang_id' => $request->gudang_id, 'produk_id' => $request->produk_id],
            ['stok' => $request->stok]
        );

        // Log perubahan stok jika ada perubahan
        if ($selisih != 0) {
            StokLog::create([
                'gudang_produk_id' => $gudangProduk->id,
                'produk_id' => $produk->id,
                'gudang_id' => $gudang->id,
                'user_id' => $user->id,
                'produk_nama' => $produk->nama_produk,
                'gudang_nama' => $gudang->nama_gudang,
                'user_nama' => $user->name,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'selisih' => $selisih,
                'keterangan' => $request->keterangan ?? 'Perubahan stok manual'
            ]);
        }

        return redirect()->route('stok.index')->with('success', 'Stok berhasil diperbarui.');
    }

    public function exportStok(Request $request)
    {
        $user = Auth::user();

        // Validasi request
        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
        ]);

        $gudang = Gudang::findOrFail($request->gudang_id);

        // Cek authorization
        if ($user->role == 'admin' && !$user->canAccessGudang($gudang->id)) {
            return redirect()->route('stok.index')->with('error', 'Anda tidak memiliki akses ke gudang ini.');
        }

        // Ambil data stok
        $stokData = GudangProduk::where('gudang_id', $gudang->id)
            ->with('produk')
            ->get();

        // Generate file name
        $fileName = 'Stok_' . str_replace(' ', '_', $gudang->nama_gudang) . '_' . date('Y-m-d_His') . '.xlsx';

        // Export menggunakan library Excel dengan FromView
        return Excel::download(
            new \App\Exports\StokExport($gudang, $stokData),
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Menampilkan riwayat perubahan stok
     */
    public function log(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $user = Auth::user();
        $query = StokLog::with(['produk', 'gudang', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan gudang untuk admin
        if ($user->role == 'admin') {
            $gudangIds = $user->gudangs()->pluck('gudangs.id');
            $query->whereIn('gudang_id', $gudangIds);
        }

        // Filter berdasarkan request
        if ($request->filled('gudang_id')) {
            $query->where('gudang_id', $request->gudang_id);
        }
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        $logs = $query->paginate(50);

        // Data untuk filter dropdown
        if ($user->role == 'super_admin') {
            $gudangs = Gudang::all();
        } else {
            $gudangs = $user->gudangs;
        }
        $produks = Produk::orderBy('nama_produk')->get();

        return view('stok.log', compact('logs', 'gudangs', 'produks'));
    }
}