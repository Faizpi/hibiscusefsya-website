<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Pembayaran;
use App\Penjualan;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
        $user = auth()->user();
        $pembayaran = Pembayaran::with(['user:id,name', 'gudang:id,nama_gudang', 'penjualan.items', 'approver:id,name'])
            ->findOrFail($id);

        if ($user->role == 'user' && $pembayaran->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $pembayaran->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Tidak memiliki akses ke gudang aktif untuk data ini.'], 403);
            }
        }

        return response()->json($pembayaran);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->isSpectator()) {
            return response()->json(['message' => 'Spectator tidak bisa membuat transaksi.'], 403);
        }

        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tgl_pembayaran' => 'required|date',
            'metode_pembayaran' => 'required|string',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $penjualan = Penjualan::findOrFail($request->penjualan_id);

        if (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $penjualan->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Gudang transaksi harus sesuai gudang aktif.'], 403);
            }
        } elseif ($user->role !== 'super_admin' && !$user->canAccessGudang($penjualan->gudang_id)) {
            return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
        }

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
            'lampiran_paths' => [],
        ]);

        $lampiranPaths = [];
        if ($request->hasFile('lampiran')) {
            $publicFolder = public_path('storage/lampiran_pembayaran');
            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0755, true);
            }
            $counter = 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_pembayaran/' . $filename;
                $counter++;
            }
            $pembayaran->update(['lampiran_paths' => $lampiranPaths]);
        }

        return response()->json(['message' => 'Pembayaran berhasil dibuat.', 'data' => $pembayaran], 201);
    }

    public function approve($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        if ($user->role === 'admin') {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $pembayaran->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Hanya bisa approve pembayaran di gudang aktif.'], 403);
            }
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

        if ($user->role === 'admin') {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $pembayaran->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Hanya bisa cancel pembayaran di gudang aktif.'], 403);
            }
        }

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

    public function uncancel($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat membatalkan pembatalan.'], 403);
        }

        if ($pembayaran->status !== 'Canceled') {
            return response()->json(['message' => 'Transaksi ini tidak dalam status Canceled.'], 422);
        }

        $pembayaran->update(['status' => 'Pending']);

        return response()->json(['message' => 'Pembayaran berhasil di-uncancel. Status kembali ke Pending.', 'data' => $pembayaran]);
    }
}
