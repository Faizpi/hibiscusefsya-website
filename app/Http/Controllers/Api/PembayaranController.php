<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Pembayaran;
use App\Penjualan;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Pembayaran::with(['user:id,name', 'gudang:id,nama_gudang', 'penjualan:id,nomor,pelanggan,grand_total']);

        if ($user->role == 'super_admin') {
            // lihat semua
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if ($currentGudang) {
                $query->where('gudang_id', $currentGudang->id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }

    public function show($id)
    {
        return response()->json(
            Pembayaran::with(['user:id,name', 'gudang:id,nama_gudang', 'penjualan.items', 'approver:id,name'])
                ->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tgl_pembayaran' => 'required|date',
            'metode_pembayaran' => 'required|string',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $penjualan = Penjualan::findOrFail($request->penjualan_id);

        $countToday = Pembayaran::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Pembayaran::generateNomor($user->id, $noUrut, Carbon::now());

        $pembayaran = Pembayaran::create([
            'user_id' => $user->id,
            'penjualan_id' => $penjualan->id,
            'gudang_id' => $penjualan->gudang_id,
            'nomor' => $nomor,
            'tgl_pembayaran' => $request->tgl_pembayaran,
            'metode_pembayaran' => $request->metode_pembayaran,
            'jumlah_bayar' => $request->jumlah_bayar,
            'keterangan' => $request->keterangan,
            'status' => 'Pending',
            'lampiran_paths' => json_encode([]),
        ]);

        return response()->json(['message' => 'Pembayaran berhasil dibuat.', 'data' => $pembayaran], 201);
    }
}
