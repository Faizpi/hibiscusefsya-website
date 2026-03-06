<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Biaya;
use App\BiayaItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiayaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Biaya::with(['user:id,name', 'approver:id,name']);

        if ($user->role != 'super_admin') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }

    public function show($id)
    {
        return response()->json(
            Biaya::with(['user:id,name', 'approver:id,name', 'items'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->isSpectator()) {
            return response()->json(['message' => 'Spectator tidak bisa membuat transaksi.'], 403);
        }

        $request->validate([
            'jenis_biaya' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'cara_pembayaran' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.deskripsi' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:0',
        ]);

        $subTotal = collect($request->items)->sum('jumlah');
        $grandTotal = $subTotal + ($subTotal * (($request->tax_percentage ?? 0) / 100));

        $countToday = Biaya::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Biaya::generateNomor($user->id, $noUrut, Carbon::now());

        DB::beginTransaction();
        try {
            $biaya = Biaya::create([
                'user_id' => $user->id,
                'jenis_biaya' => $request->jenis_biaya,
                'nomor' => $nomor,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'status' => 'Pending',
                'tax_percentage' => $request->tax_percentage ?? 0,
                'grand_total' => $grandTotal,
                'lampiran_paths' => json_encode([]),
            ]);

            foreach ($request->items as $item) {
                BiayaItem::create([
                    'biaya_id' => $biaya->id,
                    'deskripsi' => $item['deskripsi'],
                    'jumlah' => $item['jumlah'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Biaya berhasil dibuat.', 'data' => $biaya->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat biaya.'], 500);
        }
    }

    public function approve($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $biaya = Biaya::findOrFail($id);

        if ($biaya->status === 'Canceled') {
            return response()->json(['message' => 'Transaksi sudah dibatalkan, tidak bisa di-approve.'], 422);
        }

        if ($biaya->status === 'Approved' && $user->role === 'admin') {
            return response()->json(['message' => 'Transaksi sudah disetujui.'], 422);
        }

        $biaya->update(['status' => 'Approved', 'approver_id' => $user->id]);

        return response()->json(['message' => 'Biaya berhasil di-approve.', 'data' => $biaya]);
    }

    public function cancel($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $biaya = Biaya::findOrFail($id);

        if ($biaya->status === 'Canceled') {
            return response()->json(['message' => 'Transaksi sudah dibatalkan.'], 422);
        }

        if ($biaya->status === 'Approved' && $user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.'], 403);
        }

        $biaya->update(['status' => 'Canceled']);
        return response()->json(['message' => 'Biaya berhasil dibatalkan.']);
    }
}
