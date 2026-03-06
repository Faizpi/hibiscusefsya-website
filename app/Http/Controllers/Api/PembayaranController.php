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

    public function approve($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        if ($user->role === 'admin' && !$user->canAccessGudang($pembayaran->gudang_id)) {
            return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
        }

        if ($pembayaran->status === 'Canceled') {
            return response()->json(['message' => 'Transaksi sudah dibatalkan, tidak bisa di-approve.'], 422);
        }
        if ($pembayaran->status === 'Approved') {
            return response()->json(['message' => 'Transaksi sudah disetujui.'], 422);
        }

        DB::beginTransaction();
        try {
            $pembayaran->update(['status' => 'Approved', 'approver_id' => $user->id]);

            $totalBayar = Pembayaran::where('penjualan_id', $pembayaran->penjualan_id)
                ->where('status', 'Approved')
                ->sum('jumlah_bayar');

            $penjualan = $pembayaran->penjualan;
            if ($totalBayar >= $penjualan->grand_total) {
                $penjualan->update(['status' => 'Lunas']);
            }

            DB::commit();
            return response()->json(['message' => 'Pembayaran berhasil di-approve.', 'data' => $pembayaran]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal approve pembayaran.'], 500);
        }
    }

    public function cancel($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        if ($pembayaran->status === 'Canceled') {
            return response()->json(['message' => 'Transaksi sudah dibatalkan.'], 422);
        }

        if ($pembayaran->status === 'Approved' && $user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.'], 403);
        }

        DB::beginTransaction();
        try {
            if ($pembayaran->status === 'Approved') {
                $penjualan = $pembayaran->penjualan;
                if ($penjualan && $penjualan->status === 'Lunas') {
                    $penjualan->update(['status' => 'Approved']);
                }
            }

            $pembayaran->update(['status' => 'Canceled']);
            DB::commit();
            return response()->json(['message' => 'Pembayaran berhasil dibatalkan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membatalkan pembayaran.'], 500);
        }
    }
}
