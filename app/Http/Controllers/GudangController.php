<?php

namespace App\Http\Controllers;

use App\Gudang; // atau App\Models\Gudang
use Illuminate\Http\Request;

class GudangController extends Controller
{
    // Pastikan hanya admin yang bisa akses
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $gudangs = Gudang::all();
        return view('gudang.index', compact('gudangs'));
    }

    public function create()
    {
        return view('gudang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_gudang' => 'required|string|max:255|unique:gudangs',
            'alamat_gudang' => 'nullable|string',
        ]);

        Gudang::create($request->all());

        return redirect()->route('gudang.index')->with('success', 'Gudang baru berhasil ditambahkan.');
    }

    public function edit(Gudang $gudang)
    {
        return view('gudang.edit', compact('gudang'));
    }

    public function update(Request $request, Gudang $gudang)
    {
        $request->validate([
            'nama_gudang' => 'required|string|max:255|unique:gudangs,nama_gudang,' . $gudang->id,
            'alamat_gudang' => 'nullable|string',
        ]);

        $gudang->update($request->all());

        return redirect()->route('gudang.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Gudang $gudang)
    {
        // TODO: Cek dulu apakah gudang ini dipakai oleh user
        // $isUsed = $gudang->users()->exists();
        // if($isUsed) {
        //     return redirect()->route('gudang.index')->with('error', 'Gudang tidak bisa dihapus karena sedang digunakan oleh user.');
        // }

        $gudang->delete();
        return redirect()->route('gudang.index')->with('success', 'Gudang berhasil dihapus.');
    }
}