<?php

namespace App\Http\Controllers;

use App\Pembayaran;
use App\Penjualan;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PembayaranController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Pembayaran::with(['user', 'approver', 'penjualan', 'gudang']);
        
        if ($user->role == 'super_admin') {
            // Super admin dapat melihat semua pembayaran
        } elseif ($user->role == 'admin') {
            // Admin dapat melihat pembayaran di gudang yang dia kelola
            $adminGudangIds = $user->gudangs->pluck('id')->toArray();
            if ($user->current_gudang_id) {
                $adminGudangIds[] = $user->current_gudang_id;
            }
            if ($user->gudang_id) {
                $adminGudangIds[] = $user->gudang_id;
            }
            $adminGudangIds = array_unique($adminGudangIds);

            $query->whereIn('gudang_id', $adminGudangIds);
        } elseif ($user->role == 'spectator') {
            // Spectator dapat melihat pembayaran di gudang yang dia akses
            $spectatorGudangIds = $user->spectatorGudangs->pluck('id')->toArray();
            if ($user->current_gudang_id) {
                $spectatorGudangIds[] = $user->current_gudang_id;
            }
            $spectatorGudangIds = array_unique($spectatorGudangIds);

            $query->whereIn('gudang_id', $spectatorGudangIds);
        } else {
            // User biasa hanya melihat pembayaran miliknya sendiri
            $query->where('user_id', $user->id);
        }

        // Clone query untuk summary calculations
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        $totalBulanIni = $allForSummary->filter(function ($item) {
            return Carbon::parse($item->tgl_pembayaran)->gte(Carbon::now()->startOfMonth());
        })->whereIn('status', ['Pending', 'Approved'])->sum('jumlah_bayar');

        $total30Hari = $allForSummary->filter(function ($item) {
            return Carbon::parse($item->tgl_pembayaran)->gte(Carbon::now()->subDays(30));
        })->whereIn('status', ['Pending', 'Approved'])->sum('jumlah_bayar');

        $totalPending = $allForSummary->where('status', 'Pending')->sum('jumlah_bayar');
        $totalApproved = $allForSummary->where('status', 'Approved')->sum('jumlah_bayar');
        $totalCanceled = $allForSummary->where('status', 'Canceled')->count();

        // Paginated data
        $pembayarans = $query->latest()->paginate(20);

        return view('pembayaran.index', [
            'pembayarans' => $pembayarans,
            'totalBulanIni' => $totalBulanIni,
            'total30Hari' => $total30Hari,
            'totalPending' => $totalPending,
            'totalApproved' => $totalApproved,
            'totalCanceled' => $totalCanceled,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('pembayaran.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        // Ambil gudang aktif user
        $gudang = $user->getCurrentGudang();
        if (!$gudang) {
            return redirect()->route('pembayaran.index')->with('error', 'Anda belum memiliki gudang aktif.');
        }

        // Ambil penjualan yang belum lunas di gudang aktif
        $penjualanBelumLunas = Penjualan::where('gudang_id', $gudang->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function($q) {
                $q->where('cara_pembayaran', 'like', '%Tempo%')
                    ->orWhere('cara_pembayaran', 'like', '%tempo%')
                    ->orWhere('cara_pembayaran', 'Hutang');
            })
            ->get()
            ->filter(function($penjualan) {
                // Hitung total pembayaran yang sudah approved
                $totalBayar = Pembayaran::where('penjualan_id', $penjualan->id)
                    ->where('status', 'Approved')
                    ->sum('jumlah_bayar');
                $sisa = $penjualan->grand_total - $totalBayar;
                return $sisa > 0;
            });

        // Generate preview nomor invoice
        $countToday = Pembayaran::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $previewNomor = Pembayaran::generateNomor(Auth::id(), $noUrut, Carbon::now());

        return view('pembayaran.create', compact('penjualanBelumLunas', 'previewNomor', 'gudang'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'spectator') {
            return redirect()->route('pembayaran.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'tgl_pembayaran' => 'required|date',
            'metode_pembayaran' => 'required|string|max:100',
            'jumlah_bayar' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $gudang = $user->getCurrentGudang();
        if (!$gudang) {
            return redirect()->back()->with('error', 'Anda belum memiliki gudang aktif.')->withInput();
        }

        // Validasi penjualan ada di gudang user
        $penjualan = Penjualan::findOrFail($request->penjualan_id);
        if ($penjualan->gudang_id != $gudang->id) {
            return redirect()->back()->with('error', 'Invoice penjualan tidak valid untuk gudang Anda.')->withInput();
        }

        // Tentukan approver dan status
        $approverId = null;
        $initialStatus = 'Pending';

        if ($user->role == 'user') {
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($gudang) {
                    $q->where('gudang_id', $gudang->id)
                        ->orWhereHas('gudangs', function ($sub) use ($gudang) {
                            $sub->where('gudangs.id', $gudang->id);
                        });
                })
                ->first();
            $approverId = $adminGudang ? $adminGudang->id : User::where('role', 'super_admin')->first()?->id;
        } elseif ($user->role == 'admin') {
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } elseif ($user->role == 'super_admin') {
            $initialStatus = 'Approved';
            $approverId = $user->id;
        }

        $countToday = Pembayaran::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $nomor = Pembayaran::generateNomor(Auth::id(), $noUrut, Carbon::now());

        // Upload lampiran
        $lampiranPaths = [];
        $publicFolder = public_path('storage/lampiran_pembayaran');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $counter = 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_pembayaran/' . $filename;
                $counter++;
            }
        }

        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::create([
                'user_id' => Auth::id(),
                'approver_id' => $approverId,
                'gudang_id' => $gudang->id,
                'penjualan_id' => $request->penjualan_id,
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'tgl_pembayaran' => $request->tgl_pembayaran,
                'metode_pembayaran' => $request->metode_pembayaran,
                'jumlah_bayar' => $request->jumlah_bayar,
                'lampiran_paths' => $lampiranPaths,
                'keterangan' => $request->keterangan,
                'status' => $initialStatus,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($lampiranPaths as $path) {
                if (File::exists(public_path('storage/' . $path))) {
                    File::delete(public_path('storage/' . $path));
                }
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        $message = ($initialStatus == 'Approved')
            ? 'Pembayaran berhasil disimpan dan langsung approved.'
            : 'Pembayaran berhasil diajukan untuk approval.';

        return redirect()->route('pembayaran.index')->with('success', $message);
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['user', 'approver', 'penjualan', 'gudang']);
        
        // Hitung sisa hutang
        $totalBayar = Pembayaran::where('penjualan_id', $pembayaran->penjualan_id)
            ->where('status', 'Approved')
            ->sum('jumlah_bayar');
        $sisaHutang = $pembayaran->penjualan->grand_total - $totalBayar;
        
        return view('pembayaran.show', compact('pembayaran', 'sisaHutang'));
    }

    public function approve(Pembayaran $pembayaran)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($pembayaran->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan, tidak bisa di-approve.');
        }

        if ($pembayaran->status === 'Approved') {
            return back()->with('error', 'Transaksi sudah disetujui.');
        }

        $pembayaran->status = 'Approved';
        $pembayaran->approver_id = $user->id;
        $pembayaran->save();

        return back()->with('success', 'Pembayaran berhasil disetujui.');
    }

    public function cancel(Pembayaran $pembayaran)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($pembayaran->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan.');
        }

        if ($pembayaran->status === 'Approved' && $user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.');
        }

        $pembayaran->status = 'Canceled';
        $pembayaran->save();
        
        return back()->with('success', 'Pembayaran dibatalkan.');
    }

    public function uncancel(Pembayaran $pembayaran)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan pembatalan transaksi.');
        }

        if ($pembayaran->status !== 'Canceled') {
            return back()->with('error', 'Transaksi tidak dalam status dibatalkan.');
        }

        $pembayaran->status = 'Pending';
        $pembayaran->approver_id = $user->id;
        $pembayaran->save();

        return back()->with('success', 'Pembatalan transaksi dibatalkan. Status kembali ke Pending.');
    }

    public function destroy(Pembayaran $pembayaran)
    {
        $user = Auth::user();
        
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus pembayaran.');
        }

        // Hapus lampiran
        if ($pembayaran->lampiran_paths) {
            foreach ($pembayaran->lampiran_paths as $path) {
                $filePath = public_path('storage/' . $path);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }

        $pembayaran->delete();
        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function deleteLampiran(Pembayaran $pembayaran, $index)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus lampiran.');
        }

        $lampiranPaths = $pembayaran->lampiran_paths ?? [];

        if (!isset($lampiranPaths[$index])) {
            return back()->with('error', 'Lampiran tidak ditemukan.');
        }

        $filePath = public_path('storage/' . $lampiranPaths[$index]);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        unset($lampiranPaths[$index]);
        $pembayaran->lampiran_paths = array_values($lampiranPaths);
        $pembayaran->save();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    // API untuk mendapatkan detail penjualan
    public function getPenjualanDetail($id)
    {
        $penjualan = Penjualan::with('items.produk', 'kontak')->findOrFail($id);
        
        // Hitung total pembayaran yang sudah approved
        $totalBayar = Pembayaran::where('penjualan_id', $id)
            ->where('status', 'Approved')
            ->sum('jumlah_bayar');
        
        $sisaHutang = $penjualan->grand_total - $totalBayar;
        
        return response()->json([
            'nomor' => $penjualan->nomor ?? $penjualan->custom_number,
            'kontak' => $penjualan->kontak ? $penjualan->kontak->nama : ($penjualan->pelanggan ?? '-'),
            'tgl_transaksi' => $penjualan->tgl_transaksi->format('d/m/Y'),
            'grand_total' => $penjualan->grand_total,
            'total_bayar' => $totalBayar,
            'sisa_hutang' => $sisaHutang,
        ]);
    }
}
