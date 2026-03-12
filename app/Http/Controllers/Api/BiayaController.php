<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Biaya;
use App\BiayaItem;
use App\User;
use App\Services\InvoiceEmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BiayaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Biaya::with(['user:id,name', 'approver:id,name', 'gudang:id,nama_gudang']);

        $gudangIds = $this->getAccessibleGudangIds($user);
        if ($gudangIds !== null) {
            $query->where(function ($q) use ($gudangIds, $user) {
                $q->whereIn('gudang_id', $gudangIds)
                    ->orWhere('user_id', $user->id)
                    ->orWhere('approver_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_biaya', $request->jenis);
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
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'penerima' => 'nullable|string|max:255',
            'tax_percentage' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.kategori' => 'required|string|max:255',
            'items.*.jumlah' => 'required|numeric|min:0',
        ]);

        // Tentukan approver otomatis
        $approverId = null;
        $initialStatus = 'Pending';

        if ($user->role == 'user') {
            $gudang = $user->getCurrentGudang();
            if ($gudang) {
                $adminGudang = User::where('role', 'admin')
                    ->where(function ($q) use ($gudang) {
                        $q->where('gudang_id', $gudang->id)
                            ->orWhereHas('gudangs', function ($sub) use ($gudang) {
                                $sub->where('gudangs.id', $gudang->id);
                            });
                    })->first();
                $approverId = $adminGudang ? $adminGudang->id : optional(User::where('role', 'super_admin')->first())->id;
            } else {
                $approverId = optional(User::where('role', 'super_admin')->first())->id;
            }
        } elseif ($user->role == 'admin') {
            $approverId = optional(User::where('role', 'super_admin')->first())->id;
        } elseif ($user->role == 'super_admin') {
            $initialStatus = 'Approved';
            $approverId = $user->id;
        }

        $subTotal = collect($request->items)->sum('jumlah');
        $pajakPersen = $request->tax_percentage ?? 0;
        $grandTotal = $subTotal + ($subTotal * ($pajakPersen / 100));

        $countToday = Biaya::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();
        $noUrut = $countToday + 1;
        $nomor = Biaya::generateNomor($user->id, $noUrut, Carbon::now());

        // Auto-assign gudang_id dari gudang user saat ini
        $gudangId = null;
        $currentGudang = $user->getCurrentGudang();
        if ($currentGudang) {
            $gudangId = $currentGudang->id;
        }

        DB::beginTransaction();
        try {
            $biaya = Biaya::create([
                'user_id' => $user->id,
                'gudang_id' => $gudangId,
                'jenis_biaya' => $request->jenis_biaya ?? 'keluar',
                'nomor' => $nomor,
                'no_urut_harian' => $noUrut,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'status' => $initialStatus,
                'approver_id' => $approverId,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
                'lampiran_paths' => json_encode([]),
            ]);

            // Upload lampiran
            $lampiranPaths = [];
            if ($request->hasFile('lampiran')) {
                $publicFolder = public_path('storage/lampiran_biaya');
                if (!File::exists($publicFolder)) {
                    File::makeDirectory($publicFolder, 0755, true);
                }
                $counter = 1;
                foreach ($request->file('lampiran') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = $nomor . '-' . $counter . '.' . $extension;
                    $file->move($publicFolder, $filename);
                    $lampiranPaths[] = 'lampiran_biaya/' . $filename;
                    $counter++;
                }
                $biaya->update(['lampiran_paths' => $lampiranPaths]);
            }

            foreach ($request->items as $item) {
                BiayaItem::create([
                    'biaya_id' => $biaya->id,
                    'kategori' => $item['kategori'],
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'jumlah' => $item['jumlah'],
                ]);
            }

            DB::commit();

            try {
                InvoiceEmailService::sendCreatedNotification($biaya, 'biaya');
            } catch (\Exception $emailErr) {
                // Email gagal tidak menggagalkan transaksi
            }

            $message = ($initialStatus == 'Approved')
                ? 'Biaya berhasil disimpan dan langsung approved.'
                : 'Biaya berhasil dibuat.';
            return response()->json(['message' => $message, 'data' => $biaya->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat biaya.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat mengubah data biaya.'], 403);
        }

        $biaya = Biaya::findOrFail($id);

        $request->validate([
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'penerima' => 'nullable|string|max:255',
            'tax_percentage' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.kategori' => 'required|string|max:255',
            'items.*.jumlah' => 'required|numeric|min:0',
        ]);

        // Handle lampiran append
        $lampiranPaths = $biaya->lampiran_paths ?? [];
        if ($request->hasFile('lampiran')) {
            $publicFolder = public_path('storage/lampiran_biaya');
            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0755, true);
            }
            $counter = count($lampiranPaths) + 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $biaya->nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_biaya/' . $filename;
                $counter++;
            }
        }

        $subTotal = collect($request->items)->sum('jumlah');
        $pajakPersen = $request->tax_percentage ?? 0;
        $grandTotal = $subTotal + ($subTotal * ($pajakPersen / 100));

        // Super admin update = langsung approved
        $initialStatus = 'Approved';
        $approverId = $user->id;

        DB::beginTransaction();
        try {
            $biaya->update([
                'status' => $initialStatus,
                'approver_id' => $approverId,
                'jenis_biaya' => $request->jenis_biaya ?? $biaya->jenis_biaya,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $biaya->items()->delete();

            foreach ($request->items as $item) {
                BiayaItem::create([
                    'biaya_id' => $biaya->id,
                    'kategori' => $item['kategori'],
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'jumlah' => $item['jumlah'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Biaya berhasil diperbarui.', 'data' => $biaya->load('items')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah biaya.'], 500);
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

        try {
            InvoiceEmailService::sendApprovedNotification($biaya, 'biaya');
        } catch (\Exception $emailErr) {
            // Email gagal tidak menggagalkan proses
        }

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

    public function uncancel($id)
    {
        $biaya = Biaya::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Hanya Super Admin yang dapat membatalkan pembatalan.'], 403);
        }

        if ($biaya->status !== 'Canceled') {
            return response()->json(['message' => 'Transaksi ini tidak dalam status Canceled.'], 422);
        }

        $biaya->update([
            'status' => 'Pending',
            'approver_id' => $user->id,
        ]);

        return response()->json(['message' => 'Biaya berhasil di-uncancel. Status kembali ke Pending.', 'data' => $biaya]);
    }

    private function getAccessibleGudangIds($user)
    {
        if ($user->role === 'super_admin')
            return null;

        $gudangIds = [];
        if ($user->role === 'admin') {
            $gudangIds = $user->gudangs->pluck('id')->toArray();
            if ($user->current_gudang_id)
                $gudangIds[] = $user->current_gudang_id;
            if ($user->gudang_id)
                $gudangIds[] = $user->gudang_id;
        } elseif ($user->role === 'spectator') {
            $gudangIds = $user->spectatorGudangs->pluck('id')->toArray();
            if ($user->current_gudang_id)
                $gudangIds[] = $user->current_gudang_id;
        } else {
            $gudang = $user->getCurrentGudang();
            if ($gudang)
                $gudangIds[] = $gudang->id;
        }
        return array_unique($gudangIds);
    }
}
