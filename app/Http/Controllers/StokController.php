<?php

namespace App\Http\Controllers;

use App\Gudang;
use App\Produk;
use App\GudangProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StokController extends Controller
{
    public function index()
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
             return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $gudangs = Gudang::all();
        $produks = Produk::all();

        $gudangsWithStok = Gudang::with('produkStok.produk')->get();

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
}