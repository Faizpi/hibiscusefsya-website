<?php

namespace App\Http\Controllers;

use App\Produk;
use App\GudangProduk;
use Illuminate\Http\Request;
use PDF;

class ProdukController extends Controller
{
    // Pastikan hanya admin yang bisa akses
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $produks = Produk::all();
        return view('produk.index', compact('produks'));
    }

    public function show(Produk $produk)
    {
        // Load stok di semua gudang
        $produk->load('gudangProduks.gudang');
        return view('produk.show', compact('produk'));
    }

    public function print(Produk $produk)
    {
        $produk->load('gudangProduks.gudang');
        return view('produk.print', compact('produk'));
    }

    public function downloadPdf(Produk $produk)
    {
        $produk->load('gudangProduks.gudang');
        $itemKode = $produk->item_kode ?? $produk->item_code ?? 'PRD' . $produk->id;

        $pdf = PDF::loadView('produk.print', compact('produk'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('produk-' . $itemKode . '.pdf');
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:255|unique:produks',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        Produk::create($request->all());

        return redirect()->route('produk.index')->with('success', 'Produk baru berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:255|unique:produks,item_code,' . $produk->id,
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        $produk->update($request->all());

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {

        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}