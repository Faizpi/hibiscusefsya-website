<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Produk;
use App\GudangProduk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Produk::query();

        // User biasa hanya lihat produk di gudangnya
        if ($user->role == 'user' && $user->gudang_id) {
            $query->whereHas('stokDiGudang', function ($q) use ($user) {
                $q->where('gudang_id', $user->gudang_id);
            });
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if ($currentGudang) {
                $query->whereHas('stokDiGudang', function ($q) use ($currentGudang) {
                    $q->where('gudang_id', $currentGudang->id);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $produks = $query->paginate($request->per_page ?? 50);

        return response()->json($produks);
    }

    public function show($id)
    {
        $produk = Produk::with('stokDiGudang.gudang:id,nama_gudang')->findOrFail($id);

        return response()->json($produk);
    }

    /**
     * Get stok produk di gudang tertentu.
     */
    public function stokByGudang(Request $request, $gudangId)
    {
        $user = auth()->user();

        if ($user->role != 'super_admin' && !$user->canAccessGudang($gudangId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stok = GudangProduk::with('produk:id,nama_produk,item_code,satuan,harga,harga_grosir')
            ->where('gudang_id', $gudangId)
            ->get();

        return response()->json($stok);
    }
}
