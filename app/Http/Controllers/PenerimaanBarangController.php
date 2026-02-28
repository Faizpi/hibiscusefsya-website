<?php

namespace App\Http\Controllers;

use App\PenerimaanBarang;
use App\PenerimaanBarangItem;
use App\Pembelian;
use App\User;
use App\Gudang;
use App\GudangProduk;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PenerimaanBarangController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = PenerimaanBarang::with(['user', 'approver', 'pembelian', 'gudang', 'items.produk']);

        if ($user->role == 'super_admin') {
            // Super admin dapat melihat semua penerimaan
        } elseif ($user->role == 'admin') {
            // Admin dapat melihat penerimaan di gudang yang dia kelola
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
            // Spectator dapat melihat penerimaan di gudang yang dia akses
            $spectatorGudangIds = $user->spectatorGudangs->pluck('id')->toArray();
            if ($user->current_gudang_id) {
                $spectatorGudangIds[] = $user->current_gudang_id;
            }
            $spectatorGudangIds = array_unique($spectatorGudangIds);

            $query->whereIn('gudang_id', $spectatorGudangIds);
        } else {
            // User biasa hanya melihat penerimaan miliknya sendiri
            $query->where('user_id', $user->id);
        }

        // Clone query untuk summary calculations
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        $totalBulanIni = $allForSummary->filter(function ($item) {
            return Carbon::parse($item->tgl_penerimaan)->gte(Carbon::now()->startOfMonth());
        })->whereIn('status', ['Pending', 'Approved'])->count();

        $total30Hari = $allForSummary->filter(function ($item) {
            return Carbon::parse($item->tgl_penerimaan)->gte(Carbon::now()->subDays(30));
        })->whereIn('status', ['Pending', 'Approved'])->count();

        $totalPending = $allForSummary->where('status', 'Pending')->count();
        $totalApproved = $allForSummary->where('status', 'Approved')->count();
        $totalCanceled = $allForSummary->where('status', 'Canceled')->count();

        // Paginated data
        $penerimaans = $query->latest()->paginate(20);

        return view('penerimaan-barang.index', [
            'penerimaans' => $penerimaans,
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
            return redirect()->route('penerimaan-barang.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        // Super admin bisa pilih gudang, role lain pakai gudang aktifnya
        $gudangs = collect();
        $selectedGudang = null;

        if ($user->role === 'super_admin') {
            // Super admin bisa pilih semua gudang
            $gudangs = Gudang::all();
            $selectedGudang = $gudangs->first();
        } else {
            // User/admin pakai gudang aktif
            $selectedGudang = $user->getCurrentGudang();
            if (!$selectedGudang) {
                return redirect()->route('penerimaan-barang.index')->with('error', 'Anda belum memiliki gudang aktif.');
            }
        }

        // Generate preview nomor invoice
        $countToday = PenerimaanBarang::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $previewNomor = PenerimaanBarang::generateNomor(Auth::id(), $noUrut, Carbon::now());

        return view('penerimaan-barang.create', compact('previewNomor', 'selectedGudang', 'gudangs'));
    }

    /**
     * API: Get pembelian berdasarkan gudang
     */
    public function getPembelianByGudang($gudangId)
    {
        $pembelians = Pembelian::where('gudang_id', $gudangId)
            ->whereIn('status', ['Approved', 'Pending'])
            ->with('items.produk')
            ->get()
            ->filter(function ($pembelian) {
                // Filter hanya pembelian yang masih ada item yang belum diterima
                // Hanya hitung dari penerimaan barang yang sudah APPROVED (bukan Pending)
                $hasUnreceivedItems = false;
                foreach ($pembelian->items as $item) {
                    $qtyDiterima = PenerimaanBarangItem::whereHas('penerimaanBarang', function ($q) use ($pembelian) {
                        $q->where('pembelian_id', $pembelian->id)
                            ->where('status', 'Approved');
                    })->where('produk_id', $item->produk_id)->sum('qty_diterima');

                    $qtyPesan = $item->kuantitas ?? $item->jumlah ?? 0;
                    $qtySisa = $qtyPesan - $qtyDiterima;

                    if ($qtySisa > 0) {
                        $hasUnreceivedItems = true;
                        break;
                    }
                }
                return $hasUnreceivedItems;
            })
            ->map(function ($pembelian) {
                // Hitung jumlah item yang masih belum diterima sepenuhnya
                // Hanya hitung dari penerimaan barang yang sudah APPROVED
                $itemsWithSisa = 0;
                foreach ($pembelian->items as $item) {
                    $qtyDiterima = PenerimaanBarangItem::whereHas('penerimaanBarang', function ($q) use ($pembelian) {
                        $q->where('pembelian_id', $pembelian->id)
                            ->where('status', 'Approved');
                    })->where('produk_id', $item->produk_id)->sum('qty_diterima');

                    $qtyPesan = $item->kuantitas ?? $item->jumlah ?? 0;
                    if ($qtyPesan - $qtyDiterima > 0) {
                        $itemsWithSisa++;
                    }
                }

                return [
                    'id' => $pembelian->id,
                    'nomor' => $pembelian->nomor ?? $pembelian->custom_number ?? 'PO-' . $pembelian->id,
                    'nama_supplier' => $pembelian->nama_supplier ?? $pembelian->kontak->nama ?? '-',
                    'tgl_transaksi' => $pembelian->tgl_transaksi ? $pembelian->tgl_transaksi->format('d/m/Y') : '-',
                    'status' => $pembelian->status,
                    'total_items' => $itemsWithSisa . '/' . $pembelian->items->count(),
                ];
            })
            ->values();

        return response()->json($pembelians);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'spectator') {
            return redirect()->route('penerimaan-barang.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'pembelian_ids' => 'required|array|min:1',
            'pembelian_ids.*' => 'exists:pembelians,id',
            'tgl_penerimaan' => 'required|date',
            'no_surat_jalan' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'items' => 'required|array|min:1',
            'items.*.pembelian_id' => 'required|exists:pembelians,id',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.qty_diterima' => 'required|integer|min:0',
            'items.*.qty_reject' => 'nullable|integer|min:0',
            'items.*.tipe_stok' => 'nullable|in:penjualan,gratis,sample',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.expired_date' => 'nullable|date',
        ]);

        // Validasi akses gudang
        $gudangId = $request->gudang_id;
        if ($user->role !== 'super_admin') {
            $gudang = $user->getCurrentGudang();
            if (!$gudang || $gudang->id != $gudangId) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke gudang ini.')->withInput();
            }
        }

        // Validasi semua pembelian ada di gudang yang dipilih
        foreach ($request->pembelian_ids as $pembelianId) {
            $pembelian = Pembelian::findOrFail($pembelianId);
            if ($pembelian->gudang_id != $gudangId) {
                return redirect()->back()->with('error', 'Invoice pembelian tidak valid untuk gudang yang dipilih.')->withInput();
            }
        }

        // Tentukan approver dan status
        $approverId = null;
        $initialStatus = 'Pending';
        $gudang = Gudang::find($gudangId);

        if ($user->role == 'user') {
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($gudang) {
                    $q->where('gudang_id', $gudang->id)
                        ->orWhereHas('gudangs', function ($sub) use ($gudang) {
                            $sub->where('gudangs.id', $gudang->id);
                        });
                })
                ->first();
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $adminGudang ? $adminGudang->id : ($superAdmin ? $superAdmin->id : null);
        } elseif ($user->role == 'admin') {
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } elseif ($user->role == 'super_admin') {
            $initialStatus = 'Approved';
            $approverId = $user->id;
        }

        $countToday = PenerimaanBarang::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $nomor = PenerimaanBarang::generateNomor(Auth::id(), $noUrut, Carbon::now());

        // Upload lampiran
        $lampiranPaths = [];
        $publicFolder = public_path('storage/lampiran_penerimaan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $counter = 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_penerimaan/' . $filename;
                $counter++;
            }
        }

        DB::beginTransaction();
        try {
            // Group items by pembelian_id
            $itemsByPembelian = [];
            foreach ($request->items as $item) {
                $qtyDiterima = $item['qty_diterima'] ?? 0;
                $qtyReject = $item['qty_reject'] ?? 0;
                // Include item if qty_diterima > 0 OR qty_reject > 0
                if ($qtyDiterima > 0 || $qtyReject > 0) {
                    $pembelianId = $item['pembelian_id'];
                    if (!isset($itemsByPembelian[$pembelianId])) {
                        $itemsByPembelian[$pembelianId] = [];
                    }
                    $itemsByPembelian[$pembelianId][] = $item;
                }
            }

            // Create penerimaan for each pembelian
            $penerimaanIds = [];
            $indexPenerimaan = 0;

            foreach ($itemsByPembelian as $pembelianId => $pembelianItems) {
                // Generate nomor untuk setiap penerimaan
                $nomorPenerimaan = count($itemsByPembelian) > 1
                    ? $nomor . '-' . chr(65 + $indexPenerimaan) // A, B, C...
                    : $nomor;

                $penerimaan = PenerimaanBarang::create([
                    'user_id' => Auth::id(),
                    'approver_id' => $approverId,
                    'gudang_id' => $gudangId,
                    'pembelian_id' => $pembelianId,
                    'no_urut_harian' => $noUrut + $indexPenerimaan,
                    'nomor' => $nomorPenerimaan,
                    'tgl_penerimaan' => $request->tgl_penerimaan,
                    'no_surat_jalan' => $request->no_surat_jalan,
                    'lampiran_paths' => $indexPenerimaan == 0 ? $lampiranPaths : [], // Lampiran hanya di penerimaan pertama
                    'keterangan' => $request->keterangan,
                    'status' => $initialStatus,
                ]);

                $penerimaanIds[] = $penerimaan->id;

                // Simpan items untuk penerimaan ini
                foreach ($pembelianItems as $item) {
                    $qtyDiterima = $item['qty_diterima'] ?? 0;
                    $qtyReject = $item['qty_reject'] ?? 0;
                    $tipeStok = $item['tipe_stok'] ?? 'penjualan';

                    PenerimaanBarangItem::create([
                        'penerimaan_barang_id' => $penerimaan->id,
                        'produk_id' => $item['produk_id'],
                        'qty_diterima' => $qtyDiterima,
                        'qty_reject' => $qtyReject,
                        'tipe_stok' => $tipeStok,
                        'batch_number' => $item['batch_number'] ?? null,
                        'expired_date' => $item['expired_date'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);

                    // Jika langsung approved, tambahkan stok (HANYA qty_diterima, bukan qty_reject)
                    if ($initialStatus === 'Approved' && $qtyDiterima > 0) {
                        $this->tambahStok($gudangId, $item['produk_id'], $qtyDiterima, $tipeStok);
                    }
                }

                $indexPenerimaan++;
            }

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
            ? 'Penerimaan barang berhasil disimpan, langsung approved, dan stok telah ditambahkan.'
            : 'Penerimaan barang berhasil diajukan untuk approval.';

        return redirect()->route('penerimaan-barang.index')->with('success', $message);
    }

    public function show(PenerimaanBarang $penerimaan_barang)
    {
        $penerimaan_barang->load(['user', 'approver', 'pembelian.items.produk', 'gudang', 'items.produk']);

        return view('penerimaan-barang.show', ['penerimaan' => $penerimaan_barang]);
    }

    /**
     * Print view untuk thermal printer
     */
    public function print(PenerimaanBarang $penerimaan_barang)
    {
        $penerimaan_barang->load(['user', 'approver', 'gudang', 'items.produk', 'pembelian']);

        return view('penerimaan-barang.print', ['penerimaan' => $penerimaan_barang]);
    }

    public function approve(PenerimaanBarang $penerimaan_barang)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($penerimaan_barang->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan, tidak bisa di-approve.');
        }

        if ($penerimaan_barang->status === 'Approved') {
            return back()->with('error', 'Transaksi sudah disetujui.');
        }

        DB::beginTransaction();
        try {
            $penerimaan_barang->status = 'Approved';
            $penerimaan_barang->approver_id = $user->id;
            $penerimaan_barang->save();

            // Tambahkan stok untuk setiap item (HANYA qty_diterima, bukan qty_reject)
            foreach ($penerimaan_barang->items as $item) {
                if ($item->qty_diterima > 0) {
                    $this->tambahStok($penerimaan_barang->gudang_id, $item->produk_id, $item->qty_diterima, $item->tipe_stok ?? 'penjualan');
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }

        return back()->with('success', 'Penerimaan barang berhasil disetujui dan stok telah ditambahkan (barang reject tidak masuk stok).');
    }

    public function cancel(PenerimaanBarang $penerimaan_barang)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($penerimaan_barang->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan.');
        }

        // Jika sudah approved, kurangi stok dulu
        if ($penerimaan_barang->status === 'Approved') {
            if ($user->role !== 'super_admin') {
                return back()->with('error', 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.');
            }

            DB::beginTransaction();
            try {
                // Kurangi stok
                foreach ($penerimaan_barang->items as $item) {
                    $this->kurangiStok($penerimaan_barang->gudang_id, $item->produk_id, $item->qty_diterima, $item->tipe_stok ?? 'penjualan');
                }

                $penerimaan_barang->status = 'Canceled';
                $penerimaan_barang->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal cancel: ' . $e->getMessage());
            }
        } else {
            $penerimaan_barang->status = 'Canceled';
            $penerimaan_barang->save();
        }

        return back()->with('success', 'Penerimaan barang dibatalkan.');
    }

    public function uncancel(PenerimaanBarang $penerimaan_barang)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan pembatalan transaksi.');
        }

        if ($penerimaan_barang->status !== 'Canceled') {
            return back()->with('error', 'Transaksi tidak dalam status dibatalkan.');
        }

        $penerimaan_barang->status = 'Pending';
        $penerimaan_barang->approver_id = $user->id;
        $penerimaan_barang->save();

        return back()->with('success', 'Pembatalan transaksi dibatalkan. Status kembali ke Pending.');
    }

    public function destroy(PenerimaanBarang $penerimaan_barang)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus penerimaan barang.');
        }

        // Jika approved, kurangi stok dulu
        if ($penerimaan_barang->status === 'Approved') {
            DB::beginTransaction();
            try {
                foreach ($penerimaan_barang->items as $item) {
                    $this->kurangiStok($penerimaan_barang->gudang_id, $item->produk_id, $item->qty_diterima, $item->tipe_stok ?? 'penjualan');
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
            }
        }

        // Hapus lampiran
        if ($penerimaan_barang->lampiran_paths) {
            foreach ($penerimaan_barang->lampiran_paths as $path) {
                $filePath = public_path('storage/' . $path);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }

        $penerimaan_barang->delete();
        return redirect()->route('penerimaan-barang.index')->with('success', 'Penerimaan barang berhasil dihapus.');
    }

    public function deleteLampiran(PenerimaanBarang $penerimaan_barang, $index)
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus lampiran.');
        }

        $lampiranPaths = $penerimaan_barang->lampiran_paths ?? [];

        if (!isset($lampiranPaths[$index])) {
            return back()->with('error', 'Lampiran tidak ditemukan.');
        }

        $filePath = public_path('storage/' . $lampiranPaths[$index]);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        unset($lampiranPaths[$index]);
        $penerimaan_barang->lampiran_paths = array_values($lampiranPaths);
        $penerimaan_barang->save();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    // API untuk mendapatkan detail pembelian
    public function getPembelianDetail($id)
    {
        $pembelian = Pembelian::with('items.produk')->findOrFail($id);

        // Hitung qty yang sudah diterima (hanya dari penerimaan yang APPROVED)
        $qtyDiterima = [];
        $penerimaanItems = PenerimaanBarangItem::whereHas('penerimaanBarang', function ($q) use ($id) {
            $q->where('pembelian_id', $id)->where('status', 'Approved');
        })->get();

        foreach ($penerimaanItems as $item) {
            if (!isset($qtyDiterima[$item->produk_id])) {
                $qtyDiterima[$item->produk_id] = 0;
            }
            $qtyDiterima[$item->produk_id] += $item->qty_diterima;
        }

        $items = [];
        foreach ($pembelian->items as $item) {
            $sudahDiterima = $qtyDiterima[$item->produk_id] ?? 0;
            $qtyPesan = $item->kuantitas ?? $item->jumlah ?? 0;
            $items[] = [
                'produk_id' => $item->produk_id,
                'produk_nama' => $item->produk ? $item->produk->nama_produk : ($item->nama_produk ?? '-'),
                'produk_kode' => $item->produk ? ($item->produk->item_code ?? '-') : '-',
                'qty_pesan' => $qtyPesan,
                'qty_diterima' => $sudahDiterima,
                'qty_sisa' => max(0, $qtyPesan - $sudahDiterima),
                'satuan' => $item->satuan ?? ($item->produk ? $item->produk->satuan : 'Pcs'),
            ];
        }

        return response()->json([
            'id' => $pembelian->id,
            'nomor' => $pembelian->nomor ?? $pembelian->custom_number ?? 'PO-' . $pembelian->id,
            'supplier' => $pembelian->nama_supplier ?? '-',
            'tgl_transaksi' => $pembelian->tgl_transaksi ? $pembelian->tgl_transaksi->format('d/m/Y') : '-',
            'items' => $items,
        ]);
    }

    /**
     * Helper: Tambah stok di gudang berdasarkan tipe
     */
    private function tambahStok($gudangId, $produkId, $qty, $tipeStok = 'penjualan')
    {
        $gp = GudangProduk::firstOrCreate(
            ['gudang_id' => $gudangId, 'produk_id' => $produkId],
            ['stok' => 0, 'stok_penjualan' => 0, 'stok_gratis' => 0, 'stok_sample' => 0]
        );
        $gp->stok += $qty;

        // Tambah ke kolom tipe stok yang sesuai
        $kolom = 'stok_' . $tipeStok;
        if (in_array($kolom, ['stok_penjualan', 'stok_gratis', 'stok_sample'])) {
            $gp->$kolom += $qty;
        } else {
            $gp->stok_penjualan += $qty;
        }

        $gp->save();
    }

    /**
     * Helper: Kurangi stok di gudang berdasarkan tipe
     */
    private function kurangiStok($gudangId, $produkId, $qty, $tipeStok = 'penjualan')
    {
        $gp = GudangProduk::where('gudang_id', $gudangId)
            ->where('produk_id', $produkId)
            ->first();

        if ($gp) {
            $gp->stok = max(0, $gp->stok - $qty);

            // Kurangi dari kolom tipe stok yang sesuai
            $kolom = 'stok_' . $tipeStok;
            if (in_array($kolom, ['stok_penjualan', 'stok_gratis', 'stok_sample'])) {
                $gp->$kolom = max(0, $gp->$kolom - $qty);
            } else {
                $gp->stok_penjualan = max(0, $gp->stok_penjualan - $qty);
            }

            $gp->save();
        }
    }
}
