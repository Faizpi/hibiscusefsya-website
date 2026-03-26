<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Pembelian;
use App\PembelianItem;
use App\Produk;
use App\User;
use App\Services\InvoiceEmailService;
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

        if (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $pembelian->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
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
            'urgensi' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.kuantitas' => 'required|numeric|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        if (in_array($user->role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $request->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Gudang transaksi harus sesuai gudang aktif.'], 403);
            }
        } elseif ($user->role !== 'super_admin' && !$user->canAccessGudang($request->gudang_id)) {
            return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
        }

        $subTotal = 0;
        foreach ($request->items as $item) {
            $disc = $item['diskon'] ?? 0;
            $subTotal += ($item['kuantitas'] * $item['harga_satuan']) * (1 - ($disc / 100));
        }

        $diskonAkhir = $request->diskon_akhir ?? 0;
        $kenaPajak = max(0, $subTotal - $diskonAkhir);
        $grandTotal = $kenaPajak + ($kenaPajak * (($request->tax_percentage ?? 0) / 100));

        // Calculate jatuh tempo
        $term = $request->syarat_pembayaran;
        $tglJatuhTempo = null;
        if ($term != 'Cash') {
            $tglJatuhTempo = Carbon::parse($request->tgl_transaksi);
            $days = ['Net 7' => 7, 'Net 14' => 14, 'Net 30' => 30, 'Net 60' => 60];
            if (isset($days[$term])) {
                $tglJatuhTempo->addDays($days[$term]);
            }
        }

        $countToday = Pembelian::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Pembelian::generateNomor($user->id, $noUrut, Carbon::now());

        // Tentukan approver
        $approverId = null;
        $stafPenyetuju = null;
        if ($user->role == 'user') {
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($request) {
                    $q->where('gudang_id', $request->gudang_id)
                        ->orWhereHas('gudangs', function ($sub) use ($request) {
                            $sub->where('gudangs.id', $request->gudang_id);
                        });
                })->first();
            if ($adminGudang) {
                $approverId = $adminGudang->id;
                $stafPenyetuju = $adminGudang->name;
            } else {
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $approverId = $superAdmin->id;
                    $stafPenyetuju = $superAdmin->name;
                }
            }
        } elseif ($user->role == 'admin') {
            $superAdmin = User::where('role', 'super_admin')->first();
            if ($superAdmin) {
                $approverId = $superAdmin->id;
                $stafPenyetuju = $superAdmin->name;
            }
        } elseif ($user->role == 'super_admin') {
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($request) {
                    $q->where('gudang_id', $request->gudang_id)
                        ->orWhereHas('gudangs', function ($sub) use ($request) {
                            $sub->where('gudangs.id', $request->gudang_id);
                        });
                })->first();
            if ($adminGudang) {
                $approverId = $adminGudang->id;
                $stafPenyetuju = $adminGudang->name;
            } else {
                $approverId = $user->id;
                $stafPenyetuju = $user->name;
            }
        }

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::create([
                'user_id' => $user->id,
                'status' => 'Pending',
                'approver_id' => $approverId,
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'gudang_id' => $request->gudang_id,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'staf_penyetuju' => $stafPenyetuju,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $request->tax_percentage ?? 0,
                'grand_total' => $grandTotal,
                'lampiran_paths' => [],
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
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'kuantitas' => $item['kuantitas'],
                    'unit' => $item['unit'] ?? null,
                    'satuan' => $produk->satuan,
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon' => $disc,
                    'jumlah_baris' => $total,
                ]);
            }

            DB::commit();

            try {
                InvoiceEmailService::sendCreatedNotification($pembelian, 'pembelian');
            } catch (\Exception $emailErr) {
                // Email gagal tidak menggagalkan transaksi
            }

            return response()->json(['message' => 'Pembelian berhasil dibuat.', 'data' => $pembelian->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pembelian.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat mengubah data pembelian.'], 403);
        }

        $pembelian = Pembelian::findOrFail($id);

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'syarat_pembayaran' => 'required|string',
            'urgensi' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'diskon_akhir' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.kuantitas' => 'required|numeric|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        // Handle lampiran append
        $lampiranPaths = $pembelian->lampiran_paths ?? [];
        if ($request->hasFile('lampiran')) {
            $publicFolder = public_path('storage/lampiran_pembelian');
            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0755, true);
            }
            $counter = count($lampiranPaths) + 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $pembelian->nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_pembelian/' . $filename;
                $counter++;
            }
        }

        // Calculate totals
        $subTotal = 0;
        foreach ($request->items as $item) {
            $disc = $item['diskon'] ?? 0;
            $subTotal += ($item['kuantitas'] * $item['harga_satuan']) * (1 - ($disc / 100));
        }
        $diskonAkhir = $request->diskon_akhir ?? 0;
        $kenaPajak = max(0, $subTotal - $diskonAkhir);
        $pajakPersen = $request->tax_percentage ?? 0;
        $grandTotal = $kenaPajak + ($kenaPajak * ($pajakPersen / 100));

        // Calculate jatuh tempo
        $term = $request->syarat_pembayaran;
        $tglJatuhTempo = null;
        $statusBaru = 'Pending';
        if ($term == 'Cash') {
            $statusBaru = 'Lunas';
        } else {
            $tglJatuhTempo = Carbon::parse($request->tgl_transaksi);
            $days = ['Net 7' => 7, 'Net 14' => 14, 'Net 30' => 30, 'Net 60' => 60];
            if (isset($days[$term])) {
                $tglJatuhTempo->addDays($days[$term]);
            }
        }

        // Re-calculate approver
        $approverId = $pembelian->approver_id;
        if ($statusBaru == 'Pending') {
            $approverId = $this->findApprover($user, $request->gudang_id);
        }

        DB::beginTransaction();
        try {
            $pembelian->update([
                'status' => $statusBaru,
                'approver_id' => $approverId,
                'gudang_id' => $request->gudang_id,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
                'diskon_akhir' => $diskonAkhir,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $pembelian->items()->delete();

            foreach ($request->items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $disc = $item['diskon'] ?? 0;
                $total = ($item['kuantitas'] * $item['harga_satuan']) * (1 - ($disc / 100));

                PembelianItem::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'kuantitas' => $item['kuantitas'],
                    'unit' => $item['unit'] ?? null,
                    'satuan' => $produk->satuan,
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon' => $disc,
                    'jumlah_baris' => $total,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Pembelian berhasil diperbarui.', 'data' => $pembelian->load('items')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah pembelian.'], 500);
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

        if ($user->role === 'admin') {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $pembelian->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Hanya bisa approve transaksi di gudang aktif.'], 403);
            }
        }

        $pembelian->update(['status' => 'Approved', 'approver_id' => $user->id]);

        try {
            InvoiceEmailService::sendApprovedNotification($pembelian, 'pembelian');
        } catch (\Exception $emailErr) {
            // Email gagal tidak menggagalkan proses
        }

        return response()->json(['message' => 'Pembelian berhasil di-approve.', 'data' => $pembelian]);
    }

    public function cancel($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $user = auth()->user();

        if ($user->role == 'user' && $pembelian->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role === 'admin') {
            $currentGudang = $user->getCurrentGudang();
            if (!$currentGudang || (int) $pembelian->gudang_id !== (int) $currentGudang->id) {
                return response()->json(['message' => 'Hanya bisa cancel transaksi di gudang aktif.'], 403);
            }
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

        $approverId = $this->findApprover($user, $pembelian->gudang_id);

        $pembelian->update([
            'status' => 'Pending',
            'approver_id' => $approverId,
        ]);

        return response()->json(['message' => 'Pembelian berhasil di-uncancel. Status kembali ke Pending.', 'data' => $pembelian]);
    }

    private function findApprover($user, $gudangId)
    {
        if ($user->role == 'user') {
            $admin = User::where('role', 'admin')
                ->where(function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId)
                        ->orWhereHas('gudangs', function ($sub) use ($gudangId) {
                            $sub->where('gudangs.id', $gudangId);
                        });
                })->first();
            return $admin ? $admin->id : optional(User::where('role', 'super_admin')->first())->id;
        }
        if ($user->role == 'admin') {
            return optional(User::where('role', 'super_admin')->first())->id;
        }
        return $user->id;
    }
}
