<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Pembelian;
use App\PembelianItem;
use App\Produk;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Pembelian::with(['user:id,name', 'gudang:id,nama_gudang', 'approver:id,name']);

        if ($user->role == 'super_admin') {
            // lihat semua
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if ($currentGudang) {
                $query->where('gudang_id', $currentGudang->id);
            } else {
                return response()->json(['data' => [], 'meta' => ['total' => 0]]);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }

    public function show($id)
    {
        $user = auth()->user();
        $pembelian = Pembelian::with(['user:id,name', 'gudang:id,nama_gudang', 'approver:id,name', 'items.produk:id,nama_produk,item_code,satuan'])
            ->findOrFail($id);

        if ($user->role == 'user' && $pembelian->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($pembelian);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->isSpectator()) {
            return response()->json(['message' => 'Spectator tidak bisa membuat transaksi.'], 403);
        }

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.kuantitas' => 'required|numeric|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $subTotal = 0;
        foreach ($request->items as $item) {
            $disc = $item['diskon'] ?? 0;
            $subTotal += ($item['kuantitas'] * $item['harga_satuan']) * (1 - ($disc / 100));
        }

        $diskonAkhir = $request->diskon_akhir ?? 0;
        $kenaPajak = max(0, $subTotal - $diskonAkhir);
        $grandTotal = $kenaPajak + ($kenaPajak * (($request->tax_percentage ?? 0) / 100));

        $countToday = Pembelian::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Pembelian::generateNomor($user->id, $noUrut, Carbon::now());

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::create([
                'user_id' => $user->id,
                'status' => 'Pending',
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'gudang_id' => $request->gudang_id,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $request->tax_percentage ?? 0,
                'grand_total' => $grandTotal,
                'lampiran_paths' => json_encode([]),
            ]);

            // Upload lampiran
            $lampiranPaths = [];
            if ($request->hasFile('lampiran')) {
                $publicFolder = public_path('storage/lampiran_pembelian');
                if (!File::exists($publicFolder)) {
                    File::makeDirectory($publicFolder, 0755, true);
                }
                $counter = 1;
                foreach ($request->file('lampiran') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = $nomor . '-' . $counter . '.' . $extension;
                    $file->move($publicFolder, $filename);
                    $lampiranPaths[] = 'lampiran_pembelian/' . $filename;
                    $counter++;
                }
                $pembelian->update(['lampiran_paths' => $lampiranPaths]);
            }

            foreach ($request->items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $disc = $item['diskon'] ?? 0;
                $total = ($item['kuantitas'] * $item['harga_satuan']) * (1 - ($disc / 100));

                PembelianItem::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'kuantitas' => $item['kuantitas'],
                    'satuan' => $produk->satuan,
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon' => $disc,
                    'total' => $total,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Pembelian berhasil dibuat.', 'data' => $pembelian->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pembelian.'], 500);
        }
    }

    public function approve($id)
    {
        $user = auth()->user();
        $pembelian = Pembelian::findOrFail($id);

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($pembelian->status !== 'Pending') {
            return response()->json(['message' => 'Hanya transaksi Pending yang bisa di-approve.'], 422);
        }

        $pembelian->update(['status' => 'Approved', 'approver_id' => $user->id]);

        return response()->json(['message' => 'Pembelian berhasil di-approve.', 'data' => $pembelian]);
    }

    public function cancel($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $user = auth()->user();

        if ($user->role == 'user' && $pembelian->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pembelian->update(['status' => 'Canceled']);
        return response()->json(['message' => 'Pembelian berhasil dibatalkan.']);
    }

    public function uncancel($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat membatalkan pembatalan.'], 403);
        }

        if ($pembelian->status !== 'Canceled') {
            return response()->json(['message' => 'Transaksi ini tidak dalam status Canceled.'], 422);
        }

        $pembelian->update(['status' => 'Pending']);

        return response()->json(['message' => 'Pembelian berhasil di-uncancel. Status kembali ke Pending.', 'data' => $pembelian]);
    }
}
