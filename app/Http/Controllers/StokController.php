<?php

namespace App\Http\Controllers;

use App\Gudang;
use App\Produk;
use App\GudangProduk;
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
        ]);

        GudangProduk::updateOrCreate(
            ['gudang_id' => $request->gudang_id, 'produk_id' => $request->produk_id],
            ['stok' => $request->stok]
        );

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
        return Excel::download(new \App\Exports\StokExport($gudang, $stokData), $fileName);
    }
}