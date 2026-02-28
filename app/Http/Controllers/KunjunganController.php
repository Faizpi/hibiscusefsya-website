<?php

namespace App\Http\Controllers;

use App\Kunjungan;
use App\KunjunganItem;
use App\User;
use App\Kontak;
use App\Gudang;
use App\GudangProduk;
use App\Produk;
use App\Services\InvoiceEmailService;
use App\Exports\KunjunganExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class KunjunganController extends Controller
{
    /**
     * Display a listing of kunjungan.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Kunjungan::with(['user', 'approver', 'gudang']);

        if ($user->role == 'super_admin') {
            // Super admin lihat semua
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            // Admin/Spectator: lihat data pada gudang yang dia akses, atau yang dia buat, atau yang ditunjuk ke dia
            $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
            $query->where(function ($q) use ($user, $accessibleGudangIds) {
                $q->whereIn('gudang_id', $accessibleGudangIds)
                    ->orWhere('approver_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        } else {
            // User biasa hanya lihat miliknya
            $query->where('user_id', $user->id);
        }

        // Filter tujuan jika ada
        if (request()->has('tujuan') && request('tujuan') != '') {
            $query->where('tujuan', request('tujuan'));
        }

        // Clone query untuk summary calculations
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        // Summary cards
        $totalPemeriksaanStock = $allForSummary->where('tujuan', 'Pemeriksaan Stock')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalPenagihan = $allForSummary->where('tujuan', 'Penagihan')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalPenawaran = $allForSummary->where('tujuan', 'Penawaran')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalPromo = $allForSummary->where('tujuan', 'Promo')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalCanceled = $allForSummary->where('status', 'Canceled')->count();

        // Paginated data untuk table display
        /** @var \Illuminate\Pagination\LengthAwarePaginator $kunjungans */
        $kunjungans = $query->latest()->paginate(20);
        $kunjungans->getCollection()->transform(function ($item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->custom_number = "VST-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            return $item;
        });

        // =====================================================
        // CHART: Total Produk Diperiksa per Sales (Pemeriksaan Stock only)
        // =====================================================
        // Default: all-time (kosong = tampilkan semua data)
        $chartStartDate = request('chart_start_date', '');
        $chartEndDate = request('chart_end_date', '');
        $chartProdukFilter = request('chart_produk_filter', '');

        // Query for chart data: total qty per sales (simple bar chart)
        $chartQuery = KunjunganItem::select(
            'users.name as sales_name',
            DB::raw('SUM(kunjungan_items.jumlah) as total_qty')
        )
            ->join('kunjungans', 'kunjungan_items.kunjungan_id', '=', 'kunjungans.id')
            ->join('users', 'kunjungans.user_id', '=', 'users.id')
            ->where('kunjungans.tujuan', 'Pemeriksaan Stock')
            ->whereIn('kunjungans.status', ['Pending', 'Approved']);

        // Apply date range filter if specified
        if ($chartStartDate && $chartEndDate) {
            $chartQuery->whereBetween('kunjungans.tgl_kunjungan', [$chartStartDate, $chartEndDate]);
        }

        // Apply same access control for chart
        if ($user->role == 'super_admin') {
            // Super admin sees all
        } elseif (in_array($user->role, ['admin', 'spectator'])) {
            $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
            $chartQuery->where(function ($q) use ($user, $accessibleGudangIds) {
                $q->whereIn('kunjungans.gudang_id', $accessibleGudangIds)
                    ->orWhere('kunjungans.approver_id', $user->id)
                    ->orWhere('kunjungans.user_id', $user->id);
            });
        } else {
            $chartQuery->where('kunjungans.user_id', $user->id);
        }

        // Filter by produk if specified
        if ($chartProdukFilter) {
            $chartQuery->where('kunjungan_items.produk_id', $chartProdukFilter);
        }

        $chartData = $chartQuery
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        // Transform data for Chart.js (simple bar chart)
        $chartLabels = $chartData->pluck('sales_name')->toArray();
        $chartValues = $chartData->pluck('total_qty')->map(function ($val) {
            return (int) $val;
        })->toArray();

        // Get all products for filter dropdown
        $allProduks = Produk::orderBy('nama_produk')->get();

        return view('kunjungan.index', [
            'kunjungans' => $kunjungans,
            'totalPemeriksaanStock' => $totalPemeriksaanStock,
            'totalPenagihan' => $totalPenagihan,
            'totalPenawaran' => $totalPenawaran,
            'totalPromo' => $totalPromo,
            'totalCanceled' => $totalCanceled,
            // Chart data
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'chartStartDate' => $chartStartDate,
            'chartEndDate' => $chartEndDate,
            'chartProdukFilter' => $chartProdukFilter,
            'allProduks' => $allProduks,
        ]);
    }

    /**
     * Show the form for creating a new kunjungan.
     */
    public function create()
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('kunjungan.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        $kontaks = Kontak::all();

        // Get user's gudang
        $gudang = $user->getCurrentGudang();

        // Get all products (kunjungan tidak perlu batasan gudang)
        $produks = Produk::orderBy('nama_produk')->get();

        // Generate preview nomor kunjungan
        $countToday = Kunjungan::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $previewNomor = Kunjungan::generateNomor(Auth::id(), $noUrut, Carbon::now());

        return view('kunjungan.create', compact('kontaks', 'gudang', 'produks', 'previewNomor'));
    }

    /**
     * Store a newly created kunjungan.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('kunjungan.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        // Conditional validation rules based on tujuan
        $rules = [
            'kontak_id' => 'required|exists:kontaks,id',
            'sales_nama' => 'required|string|max:255',
            'sales_email' => 'nullable|email|max:255',
            'sales_alamat' => 'nullable|string',
            'tgl_kunjungan' => 'required|date',
            'tujuan' => 'required|in:Pemeriksaan Stock,Penagihan,Promo',
            'koordinat' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'jumlah' => 'nullable|array',
            'jumlah.*' => 'integer|min:1',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ];

        // Produk wajib hanya untuk Pemeriksaan Stock
        if ($request->tujuan === 'Pemeriksaan Stock') {
            $rules['produk_id'] = 'required|array|min:1';
            $rules['produk_id.*'] = 'required|exists:produks,id';
        } else {
            $rules['produk_id'] = 'nullable|array';
            $rules['produk_id.*'] = 'nullable|exists:produks,id';
        }

        $request->validate($rules, [
            'produk_id.required' => 'Produk wajib diisi untuk kunjungan Pemeriksaan Stock.',
            'produk_id.min' => 'Minimal 1 produk harus dipilih untuk kunjungan Pemeriksaan Stock.',
            'produk_id.*.required' => 'Pilih produk yang valid.',
        ]);

        $user = Auth::user();

        // Tentukan approver otomatis berdasarkan role user
        $approverId = null;
        $initialStatus = 'Pending';
        $gudangId = null;

        if ($user->role == 'user') {
            // Sales: cari admin gudang tempat dia bekerja
            $gudang = $user->getCurrentGudang();
            if ($gudang) {
                $gudangId = $gudang->id;
                // Cari admin yang handle gudang ini
                $adminGudang = User::where('role', 'admin')
                    ->where('current_gudang_id', $gudang->id)
                    ->first();

                if ($adminGudang) {
                    $approverId = $adminGudang->id;
                } else {
                    // Kalau tidak ada admin gudang, ke super admin
                    $superAdmin = User::where('role', 'super_admin')->first();
                    $approverId = $superAdmin ? $superAdmin->id : null;
                }
            } else {
                // Sales tidak punya gudang, ke super admin
                $superAdmin = User::where('role', 'super_admin')->first();
                $approverId = $superAdmin ? $superAdmin->id : null;
            }
        } elseif ($user->role == 'admin') {
            // Admin: approver ke super admin
            $gudang = $user->getCurrentGudang();
            $gudangId = $gudang ? $gudang->id : null;
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } elseif ($user->role == 'super_admin') {
            // Super admin: langsung approved, tapi tetap harus isi approver_id
            $initialStatus = 'Approved';
            // Ambil gudang yang dipilih super admin
            $gudang = $user->getCurrentGudang();
            $gudangId = $request->gudang_id ?? ($gudang ? $gudang->id : null);

            // Cari admin gudang untuk approver_id
            if ($gudangId) {
                $adminGudang = User::where('role', 'admin')
                    ->where('current_gudang_id', $gudangId)
                    ->first();
                $approverId = $adminGudang ? $adminGudang->id : $user->id;
            } else {
                $approverId = $user->id;
            }
        } else {
            // Fallback: gunakan super_admin sebagai approver
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : $user->id;
        }

        // Generate nomor urut
        $countToday = Kunjungan::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;

        // Generate nomor kunjungan: VST-YYYYMMDD-USERID-NOURUT
        $dateCode = Carbon::now()->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        $nomor = "VST-{$dateCode}-" . Auth::id() . "-{$noUrutPadded}";

        // Upload lampiran dengan nama sesuai kode invoice
        $lampiranPaths = [];
        $publicFolder = public_path('storage/lampiran_kunjungan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        // Handle multiple lampiran
        if ($request->hasFile('lampiran')) {
            $counter = 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                // Format: VST-xxx-1.jpg, VST-xxx-2.jpg, etc
                $filename = $nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_kunjungan/' . $filename;
                $counter++;
            }
        }

        DB::beginTransaction();
        try {
            $kunjungan = Kunjungan::create([
                'user_id' => Auth::id(),
                'status' => $initialStatus,
                'approver_id' => $approverId,
                'gudang_id' => $gudangId,
                'kontak_id' => $request->kontak_id,
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'sales_nama' => $request->sales_nama,
                'sales_email' => $request->sales_email,
                'sales_alamat' => $request->sales_alamat,
                'tgl_kunjungan' => $request->tgl_kunjungan,
                'tujuan' => $request->tujuan,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
            ]);

            // Simpan produk items jika ada
            if ($request->has('produk_id') && is_array($request->produk_id)) {
                foreach ($request->produk_id as $index => $produkId) {
                    if ($produkId) {
                        KunjunganItem::create([
                            'kunjungan_id' => $kunjungan->id,
                            'produk_id' => $produkId,
                            'jumlah' => $request->jumlah[$index] ?? 1,
                            'keterangan' => $request->keterangan[$index] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            // Kirim email notification (jika bukan super admin yang langsung approved)
            if ($initialStatus == 'Pending') {
                InvoiceEmailService::sendCreatedNotification($kunjungan, 'kunjungan');
            }

            return redirect()->route('kunjungan.show', $kunjungan->id)
                ->with('success', 'Kunjungan berhasil dibuat dengan nomor ' . $nomor);
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus file yang sudah diupload jika error
            foreach ($lampiranPaths as $lampiranPath) {
                if (File::exists(public_path('storage/' . $lampiranPath))) {
                    File::delete(public_path('storage/' . $lampiranPath));
                }
            }
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified kunjungan.
     */
    public function show(Kunjungan $kunjungan)
    {
        $kunjungan->load(['user', 'approver', 'gudang', 'kontak', 'items.produk']);
        return view('kunjungan.show', compact('kunjungan'));
    }

    /**
     * Show the form for editing the specified kunjungan.
     */
    public function edit(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Only super_admin dapat mengedit
        if ($user->role !== 'super_admin') {
            return redirect()->route('kunjungan.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data kunjungan.');
        }

        $kontaks = Kontak::all();

        // Get all products (kunjungan tidak perlu batasan gudang)
        $produks = Produk::orderBy('nama_produk')->get();

        $kunjungan->load('items.produk');

        return view('kunjungan.edit', compact('kunjungan', 'kontaks', 'produks'));
    }

    /**
     * Update the specified kunjungan.
     */
    public function update(Request $request, Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Only super_admin dapat update
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah data kunjungan.');
        }

        $request->validate([
            'kontak_id' => 'required|exists:kontaks,id',
            'sales_nama' => 'required|string|max:255',
            'sales_email' => 'nullable|email|max:255',
            'sales_alamat' => 'nullable|string',
            'tujuan' => 'required|in:Pemeriksaan Stock,Penagihan,Promo',
            'memo' => 'nullable|string',
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'nullable|array',
            'produk_id.*' => 'exists:produks,id',
            'jumlah' => 'nullable|array',
            'jumlah.*' => 'integer|min:1',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        // Handle lampiran upload - append ke existing
        $lampiranPaths = $kunjungan->lampiran_paths ?? [];
        $publicFolder = public_path('storage/lampiran_kunjungan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        // Handle multiple lampiran baru (append ke existing)
        if ($request->hasFile('lampiran')) {
            $counter = count($lampiranPaths) + 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $kunjungan->nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_kunjungan/' . $filename;
                $counter++;
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
        if ($request->has('produk_id') && is_array($request->produk_id)) {
            foreach ($request->produk_id as $index => $produkId) {
                if ($produkId) {
                    KunjunganItem::create([
                        'kunjungan_id' => $kunjungan->id,
                        'produk_id' => $produkId,
                        'jumlah' => $request->jumlah[$index] ?? 1,
                        'keterangan' => $request->keterangan[$index] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('kunjungan.show', $kunjungan->id)
            ->with('success', 'Kunjungan berhasil diperbarui.');
    }

    /**
     * Remove the specified kunjungan.
     */
    public function destroy(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Only super_admin dapat hapus
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Delete lampiran if exists
        if ($kunjungan->lampiran_path && File::exists(public_path('storage/' . $kunjungan->lampiran_path))) {
            File::delete(public_path('storage/' . $kunjungan->lampiran_path));
        }

        $kunjungan->delete();
        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan berhasil dihapus.');
    }

    /**
     * Approve kunjungan
     */
    public function approve(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Hanya super_admin atau admin yang memiliki akses ke gudang/ditunjuk yang bisa approve
        $canApproveAsAdmin = $user->role == 'admin' && (
            $kunjungan->approver_id == $user->id || ($kunjungan->gudang_id && method_exists($user, 'canAccessGudang') && $user->canAccessGudang($kunjungan->gudang_id))
        );

        if ($user->role == 'super_admin' || $canApproveAsAdmin) {
            $kunjungan->update([
                'status' => 'Approved',
                'approver_id' => $user->id
            ]);

            // Kirim email notification ke pembuat kunjungan
            InvoiceEmailService::sendApprovedNotification($kunjungan, 'kunjungan');

            return back()->with('success', 'Kunjungan berhasil disetujui.');
        }

        return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui kunjungan ini.');
    }

    /**
     * Cancel kunjungan
     */
    public function cancel(Request $request, Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Super admin bisa cancel kapan saja, admin hanya jika Pending
        if ($user->role == 'super_admin') {
            $kunjungan->update(['status' => 'Canceled']);
            return back()->with('success', 'Kunjungan berhasil dibatalkan.');
        }

        if ($user->role == 'admin' && $kunjungan->status == 'Pending') {
            $kunjungan->update(['status' => 'Canceled']);
            return back()->with('success', 'Kunjungan berhasil dibatalkan.');
        }

        return back()->with('error', 'Anda tidak memiliki akses untuk membatalkan kunjungan ini.');
    }

    /**
     * Uncancel kunjungan - kembalikan ke status Pending
     */
    public function uncancel(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Hanya super_admin yang bisa uncancel
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan pembatalan kunjungan.');
        }

        if ($kunjungan->status !== 'Canceled') {
            return back()->with('error', 'Kunjungan tidak dalam status dibatalkan.');
        }

        // Tentukan approver berdasarkan gudang transaksi
        $gudangId = $kunjungan->gudang_id;
        $approverId = null;

        if ($gudangId) {
            // Cari admin yang handle gudang ini
            $adminGudang = User::where('role', 'admin')
                ->where(function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId)
                        ->orWhereHas('gudangs', function ($sub) use ($gudangId) {
                            $sub->where('gudangs.id', $gudangId);
                        });
                })
                ->first();

            if ($adminGudang) {
                $approverId = $adminGudang->id;
            } else {
                // Fallback ke super admin yang melakukan uncancel
                $approverId = $user->id;
            }
        } else {
            // Tidak ada gudang, super admin jadi approver
            $approverId = $user->id;
        }

        // Set status kembali ke Pending agar perlu di-approve ulang
        $kunjungan->status = 'Pending';
        $kunjungan->approver_id = $approverId;
        $kunjungan->save();

        return back()->with('success', 'Pembatalan kunjungan dibatalkan. Status kembali ke Pending.');
    }

    /**
     * Delete individual lampiran
     */
    public function deleteLampiran(Kunjungan $kunjungan, $index)
    {
        $user = Auth::user();

        // Hanya super_admin yang bisa hapus lampiran
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus lampiran.');
        }

        $lampiranPaths = $kunjungan->lampiran_paths ?? [];

        if (!isset($lampiranPaths[$index])) {
            return back()->with('error', 'Lampiran tidak ditemukan.');
        }

        // Hapus file fisik
        $filePath = public_path('storage/' . $lampiranPaths[$index]);
        if (\File::exists($filePath)) {
            \File::delete($filePath);
        }

        // Hapus dari array
        unset($lampiranPaths[$index]);
        $kunjungan->lampiran_paths = array_values($lampiranPaths); // Re-index array
        $kunjungan->save();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    /**
     * Print struk kunjungan
     */
    public function print(Kunjungan $kunjungan)
    {
        $kunjungan->load(['user', 'approver', 'gudang', 'kontak', 'items.produk']);
        return view('kunjungan.print', compact('kunjungan'));
    }

    /**
     * Get kunjungan data as JSON for Bluetooth printing
     */
    public function printJson(Kunjungan $kunjungan)
    {
        $kunjungan->load(['user', 'approver', 'gudang']);

        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'nomor' => "VST-{$kunjungan->user_id}-{$dateCode}-{$noUrut}",
            'tanggal' => $kunjungan->tgl_kunjungan->format('d/m/Y'),
            'waktu' => $kunjungan->created_at->format('H:i'),
            'tujuan' => $kunjungan->tujuan,
            'sales_nama' => $kunjungan->sales_nama,
            'sales_email' => $kunjungan->sales_email ?? '-',
            'sales_alamat' => $kunjungan->sales_alamat ?? '-',
            'pembuat' => optional($kunjungan->user)->name ?? '-',
            'approver' => ($kunjungan->status != 'Pending' && $kunjungan->approver) ? $kunjungan->approver->name : '-',
            'gudang' => optional($kunjungan->gudang)->nama_gudang ?? '-',
            'status' => $kunjungan->status,
            'koordinat' => $kunjungan->koordinat ?? '-',
            'memo' => $kunjungan->memo ?? '-',
            'invoice_url' => url('invoice/kunjungan/' . $kunjungan->uuid)
        ]);
    }
}
