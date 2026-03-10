<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Kunjungan;
use App\KunjunganItem;
use App\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Kunjungan::with(['user:id,name', 'gudang:id,nama_gudang', 'kontak:id,nama']);

        if ($user->role == 'super_admin') {
            // lihat semua
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if ($currentGudang) {
                $query->where('gudang_id', $currentGudang->id);
            } else {
                return response()->json(['data' => []]);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }

    public function show($id)
    {
        $kunjungan = Kunjungan::with(['user:id,name', 'gudang:id,nama_gudang', 'kontak', 'approver:id,name', 'items.produk:id,nama_produk,item_code,satuan'])
            ->findOrFail($id);

        return response()->json($kunjungan);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->isSpectator()) {
            return response()->json(['message' => 'Spectator tidak bisa membuat transaksi.'], 403);
        }

        $request->validate([
            'kontak_id' => 'required|exists:kontaks,id',
            'gudang_id' => 'required|exists:gudangs,id',
            'tgl_kunjungan' => 'required|date',
            'tujuan' => 'required|string',
        ]);

        $countToday = Kunjungan::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Kunjungan::generateNomor($user->id, $noUrut, Carbon::now());

        DB::beginTransaction();
        try {
            $kunjungan = Kunjungan::create([
                'user_id' => $user->id,
                'kontak_id' => $request->kontak_id,
                'gudang_id' => $request->gudang_id,
                'nomor' => $nomor,
                'sales_nama' => $user->name,
                'sales_email' => $user->email,
                'sales_alamat' => $user->alamat,
                'tgl_kunjungan' => $request->tgl_kunjungan,
                'tujuan' => $request->tujuan,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'status' => 'Pending',
                'lampiran_paths' => json_encode([]),
            ]);

            // Upload lampiran
            $lampiranPaths = [];
            if ($request->hasFile('lampiran')) {
                $publicFolder = public_path('storage/lampiran_kunjungan');
                if (!File::exists($publicFolder)) {
                    File::makeDirectory($publicFolder, 0755, true);
                }
                $counter = 1;
                foreach ($request->file('lampiran') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = $nomor . '-' . $counter . '.' . $extension;
                    $file->move($publicFolder, $filename);
                    $lampiranPaths[] = 'lampiran_kunjungan/' . $filename;
                    $counter++;
                }
                $kunjungan->update(['lampiran_paths' => $lampiranPaths]);
            }

            // Items (jika ada produk yang dibawa)
            if ($request->filled('items')) {
                foreach ($request->items as $item) {
                    $produk = Produk::findOrFail($item['produk_id']);
                    KunjunganItem::create([
                        'kunjungan_id' => $kunjungan->id,
                        'produk_id' => $produk->id,
                        'nama_produk' => $produk->nama_produk,
                        'kuantitas' => $item['kuantitas'],
                        'satuan' => $produk->satuan,
                        'tipe_stok' => $item['tipe_stok'] ?? 'stok',
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Kunjungan berhasil dibuat.', 'data' => $kunjungan->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat kunjungan.'], 500);
        }
    }

    public function approve($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kunjungan = Kunjungan::findOrFail($id);

        if ($kunjungan->status !== 'Pending') {
            return response()->json(['message' => 'Hanya transaksi Pending yang bisa di-approve.'], 422);
        }

        $kunjungan->update(['status' => 'Approved', 'approver_id' => $user->id]);

        return response()->json(['message' => 'Kunjungan berhasil di-approve.', 'data' => $kunjungan]);
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $kunjungan = Kunjungan::findOrFail($id);

        if ($kunjungan->status === 'Canceled') {
            return response()->json(['message' => 'Transaksi sudah dibatalkan.'], 422);
        }

        if ($user->role === 'super_admin') {
            $kunjungan->update(['status' => 'Canceled']);
            return response()->json(['message' => 'Kunjungan berhasil dibatalkan.']);
        }

        if ($user->role === 'admin' && $kunjungan->status === 'Pending') {
            $kunjungan->update(['status' => 'Canceled']);
            return response()->json(['message' => 'Kunjungan berhasil dibatalkan.']);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function uncancel($id)
    {
        $kunjungan = Kunjungan::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat membatalkan pembatalan.'], 403);
        }

        if ($kunjungan->status !== 'Canceled') {
            return response()->json(['message' => 'Transaksi ini tidak dalam status Canceled.'], 422);
        }

        $kunjungan->update(['status' => 'Pending']);

        return response()->json(['message' => 'Kunjungan berhasil di-uncancel. Status kembali ke Pending.', 'data' => $kunjungan]);
    }
}
