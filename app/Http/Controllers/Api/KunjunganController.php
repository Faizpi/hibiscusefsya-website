<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Kunjungan;
use App\KunjunganItem;
use App\Produk;
use App\User;
use App\GudangProduk;
use App\Services\InvoiceEmailService;
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
        $user = auth()->user();
        $kunjungan = Kunjungan::with(['user:id,name', 'gudang:id,nama_gudang', 'kontak', 'approver:id,name', 'items.produk:id,nama_produk,item_code,satuan'])
            ->findOrFail($id);

        if ($user->role == 'user' && $kunjungan->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($user->role, ['admin', 'spectator']) && !$user->canAccessGudang($kunjungan->gudang_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($kunjungan);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->isSpectator()) {
            return response()->json(['message' => 'Spectator tidak bisa membuat transaksi.'], 403);
        }

        $rules = [
            'kontak_id' => 'required|exists:kontaks,id',
            'tgl_kunjungan' => 'required|date',
            'tujuan' => 'required|in:Pemeriksaan Stock,Penagihan,Promo Gratis,Promo Sample',
            'sales_nama' => 'nullable|string|max:255',
            'sales_email' => 'nullable|email|max:255',
            'sales_alamat' => 'nullable|string',
        ];

        // Produk wajib untuk Pemeriksaan Stock dan Promo
        if (in_array($request->tujuan, ['Pemeriksaan Stock', 'Promo Gratis', 'Promo Sample'])) {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.produk_id'] = 'required|exists:produks,id';
        } else {
            $rules['items'] = 'nullable|array';
        }

        $request->validate($rules);

        // Validasi stok untuk Promo Gratis dan Promo Sample
        $gudangForValidation = $user->getCurrentGudang();
        if ($gudangForValidation && in_array($request->tujuan, ['Promo Gratis', 'Promo Sample']) && $request->filled('items')) {
            $stokField = $request->tujuan === 'Promo Gratis' ? 'stok_gratis' : 'stok_sample';
            $stokLabel = $request->tujuan === 'Promo Gratis' ? 'stok gratis' : 'stok sample';
            foreach ($request->items as $item) {
                if (isset($item['produk_id'])) {
                    $qty = $item['jumlah'] ?? $item['kuantitas'] ?? 1;
                    $stokAvailable = GudangProduk::where('gudang_id', $gudangForValidation->id)
                        ->where('produk_id', $item['produk_id'])
                        ->value($stokField) ?? 0;
                    if ($qty > $stokAvailable) {
                        $namaProduk = Produk::find($item['produk_id'])->nama_produk ?? 'Produk';
                        return response()->json([
                            'message' => "Qty {$namaProduk} ({$qty}) melebihi {$stokLabel} yang tersedia ({$stokAvailable})."
                        ], 422);
                    }
                }
            }
        }

        // Tentukan approver otomatis
        $approverId = null;
        $initialStatus = 'Pending';
        $gudangId = $request->gudang_id;

        if ($user->role == 'user') {
            $gudang = $user->getCurrentGudang();
            if ($gudang) {
                $gudangId = $gudangId ?? $gudang->id;
                $adminGudang = User::where('role', 'admin')
                    ->where('current_gudang_id', $gudang->id)->first();
                $approverId = $adminGudang ? $adminGudang->id : optional(User::where('role', 'super_admin')->first())->id;
            } else {
                $approverId = optional(User::where('role', 'super_admin')->first())->id;
            }
        } elseif ($user->role == 'admin') {
            $gudang = $user->getCurrentGudang();
            $gudangId = $gudangId ?? ($gudang ? $gudang->id : null);
            $approverId = optional(User::where('role', 'super_admin')->first())->id;
        } elseif ($user->role == 'super_admin') {
            $initialStatus = 'Approved';
            $gudang = $user->getCurrentGudang();
            $gudangId = $gudangId ?? ($gudang ? $gudang->id : null);
            if ($gudangId) {
                $adminGudang = User::where('role', 'admin')
                    ->where('current_gudang_id', $gudangId)->first();
                $approverId = $adminGudang ? $adminGudang->id : $user->id;
            } else {
                $approverId = $user->id;
            }
        }

        $countToday = Kunjungan::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Kunjungan::generateNomor($user->id, $noUrut, Carbon::now());

        DB::beginTransaction();
        try {
            $kunjungan = Kunjungan::create([
                'user_id' => $user->id,
                'kontak_id' => $request->kontak_id,
                'gudang_id' => $gudangId,
                'nomor' => $nomor,
                'no_urut_harian' => $noUrut,
                'sales_nama' => $request->sales_nama ?? $user->name,
                'sales_email' => $request->sales_email ?? $user->email,
                'sales_alamat' => $request->sales_alamat ?? $user->alamat,
                'tgl_kunjungan' => $request->tgl_kunjungan,
                'tujuan' => $request->tujuan,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'status' => $initialStatus,
                'approver_id' => $approverId,
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

            // Items
            if ($request->filled('items')) {
                foreach ($request->items as $item) {
                    if (isset($item['produk_id'])) {
                        $produk = Produk::findOrFail($item['produk_id']);
                        KunjunganItem::create([
                            'kunjungan_id' => $kunjungan->id,
                            'produk_id' => $produk->id,
                            'jumlah' => $item['jumlah'] ?? $item['kuantitas'] ?? 1,
                            'keterangan' => $item['keterangan'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($initialStatus == 'Pending') {
                try {
                    InvoiceEmailService::sendCreatedNotification($kunjungan, 'kunjungan');
                } catch (\Exception $emailErr) {
                    // Email gagal tidak menggagalkan transaksi
                }
            }

            return response()->json(['message' => 'Kunjungan berhasil dibuat.', 'data' => $kunjungan->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat kunjungan.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat mengubah data kunjungan.'], 403);
        }

        $kunjungan = Kunjungan::findOrFail($id);

        $request->validate([
            'kontak_id' => 'required|exists:kontaks,id',
            'sales_nama' => 'required|string|max:255',
            'sales_email' => 'nullable|email|max:255',
            'sales_alamat' => 'nullable|string',
            'tujuan' => 'required|in:Pemeriksaan Stock,Penagihan,Promo Gratis,Promo Sample',
            'memo' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.produk_id' => 'exists:produks,id',
        ]);

        // Handle lampiran append
        $lampiranPaths = $kunjungan->lampiran_paths ?? [];
        if ($request->hasFile('lampiran')) {
            $publicFolder = public_path('storage/lampiran_kunjungan');
            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0755, true);
            }
            $counter = count($lampiranPaths) + 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $kunjungan->nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_kunjungan/' . $filename;
                $counter++;
            }
        }

        // Validasi stok untuk Promo Gratis dan Promo Sample
        $gudangForValidation = $kunjungan->gudang_id ? \App\Gudang::find($kunjungan->gudang_id) : $user->getCurrentGudang();
        if ($gudangForValidation && in_array($request->tujuan, ['Promo Gratis', 'Promo Sample']) && $request->filled('items')) {
            $stokField = $request->tujuan === 'Promo Gratis' ? 'stok_gratis' : 'stok_sample';
            $stokLabel = $request->tujuan === 'Promo Gratis' ? 'stok gratis' : 'stok sample';
            foreach ($request->items as $item) {
                if (isset($item['produk_id'])) {
                    $qty = $item['jumlah'] ?? $item['kuantitas'] ?? 1;
                    $stokAvailable = GudangProduk::where('gudang_id', $gudangForValidation->id)
                        ->where('produk_id', $item['produk_id'])
                        ->value($stokField) ?? 0;
                    if ($qty > $stokAvailable) {
                        $namaProduk = Produk::find($item['produk_id'])->nama_produk ?? 'Produk';
                        return response()->json([
                            'message' => "Qty {$namaProduk} ({$qty}) melebihi {$stokLabel} yang tersedia ({$stokAvailable})."
                        ], 422);
                    }
                }
            }
        }

        $kunjungan->update([
            'kontak_id' => $request->kontak_id,
            'sales_nama' => $request->sales_nama,
            'sales_email' => $request->sales_email,
            'sales_alamat' => $request->sales_alamat,
            'tujuan' => $request->tujuan,
            'koordinat' => $request->koordinat,
            'memo' => $request->memo,
            'lampiran_paths' => $lampiranPaths,
        ]);

        // Update items: hapus lama, buat baru
        $kunjungan->items()->delete();
        if ($request->filled('items')) {
            foreach ($request->items as $item) {
                if (isset($item['produk_id'])) {
                    KunjunganItem::create([
                        'kunjungan_id' => $kunjungan->id,
                        'produk_id' => $item['produk_id'],
                        'jumlah' => $item['jumlah'] ?? $item['kuantitas'] ?? 1,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Kunjungan berhasil diperbarui.', 'data' => $kunjungan->load('items')]);
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

        try {
            InvoiceEmailService::sendApprovedNotification($kunjungan, 'kunjungan');
        } catch (\Exception $emailErr) {
            // Email gagal tidak menggagalkan proses
        }

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

        $kunjungan->update([
            'status' => 'Pending',
            'approver_id' => $user->id,
        ]);

        return response()->json(['message' => 'Kunjungan berhasil di-uncancel. Status kembali ke Pending.', 'data' => $kunjungan]);
    }
}
