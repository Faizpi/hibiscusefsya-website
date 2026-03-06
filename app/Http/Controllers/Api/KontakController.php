<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Kontak;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Kontak::query();

        if ($user->role === 'super_admin') {
            // lihat semua
        } else {
            $currentGudang = $user->getCurrentGudang();
            $query->where(function ($q) use ($currentGudang) {
                $q->whereNull('gudang_id');
                if ($currentGudang) {
                    $q->orWhere('gudang_id', $currentGudang->id);
                }
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_kontak', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('nama')->paginate($request->per_page ?? 50));
    }

    public function show($id)
    {
        return response()->json(Kontak::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
        ]);

        $user = auth()->user();
        $currentGudang = $user->getCurrentGudang();

        $kontak = Kontak::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,
            'diskon_persen' => $request->diskon_persen ?? 0,
            'gudang_id' => $request->gudang_id ?? ($currentGudang ? $currentGudang->id : null),
        ]);

        return response()->json(['message' => 'Kontak berhasil dibuat.', 'data' => $kontak], 201);
    }

    public function update(Request $request, $id)
    {
        $kontak = Kontak::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $kontak->update($request->only(['nama', 'email', 'no_telp', 'alamat', 'diskon_persen']));

        return response()->json(['message' => 'Kontak berhasil diupdate.', 'data' => $kontak]);
    }

    public function destroy($id)
    {
        $kontak = Kontak::findOrFail($id);
        $kontak->delete();

        return response()->json(['message' => 'Kontak berhasil dihapus.']);
    }
}
