<?php

namespace App\Http\Controllers;

use App\Produk; 
use Illuminate\Http\Request;

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