<?php

namespace App\Http\Controllers;

use App\Kontak; // atau App\Models\Kontak
use Illuminate\Http\Request;

class KontakController extends Controller
{
    // Pastikan hanya admin yang bisa akses
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $kontaks = Kontak::all();
        return view('kontak.index', compact('kontaks'));
    }

    public function create()
    {
        return view('kontak.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:kontaks',
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        Kontak::create($request->all());

        return redirect()->route('kontak.index')->with('success', 'Kontak baru berhasil ditambahkan.');
    }

    public function edit(Kontak $kontak)
    {
        return view('kontak.edit', compact('kontak'));
    }

    public function update(Request $request, Kontak $kontak)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:kontaks,email,' . $kontak->id,
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $kontak->update($request->all());

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil diperbarui.');
    }

    public function destroy(Kontak $kontak)
    {
        // TODO: Cek dulu apakah kontak ini dipakai di transaksi
        $kontak->delete();
        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil dihapus.');
    }
}