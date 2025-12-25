<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use App\Kunjungan;
use App\User;
use App\Produk;
use App\Gudang;
use App\GudangProduk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = [];
        $now = Carbon::now();
        $role = Auth::user()->role;
        $perPage = 20;
        $userId = Auth::id();
        $user = Auth::user();
        $selectedGudangId = $request->get('gudang_filter');
        $availableGudangs = collect();
        $currentGudang = null;

        // Gudang yang dapat dipilih untuk filter chart
        if ($role === 'super_admin') {
            $availableGudangs = Gudang::all();

            if ($selectedGudangId && !$availableGudangs->pluck('id')->contains((int) $selectedGudangId)) {
                $selectedGudangId = null;
            }
        } elseif ($role === 'admin') {
            $availableGudangs = $user->gudangs()->get();
            $currentGudang = $user->getCurrentGudang();

            if (!$selectedGudangId || !$user->canAccessGudang($selectedGudangId)) {
                $selectedGudangId = $currentGudang ? $currentGudang->id : null;
            }
        } else {
            $selectedGudangId = null;
        }

        // Inisialisasi query berdasarkan role (EXCLUDE Canceled status)
        if ($role == 'super_admin') {
            // Apply gudang filter to queries if selected
            $penjualanQuery = Penjualan::where('status', '!=', 'Canceled')
                ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                });
            $pembelianQuery = Pembelian::where('status', '!=', 'Canceled')
                ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                });
            $biayaQuery = Biaya::where('status', '!=', 'Canceled');
            $kunjunganQuery = Kunjungan::where('status', '!=', 'Canceled')
                ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                });

            $data['card_4_title'] = 'Jumlah User Terdaftar';
            $data['card_4_value'] = User::count();
            $data['card_4_icon'] = 'fa-users';

            // Statistik tambahan (exclude Canceled) - filtered by gudang if selected
            if ($selectedGudangId) {
                $data['totalProduk'] = GudangProduk::where('gudang_id', $selectedGudangId)->count();
                $data['totalTransaksi'] = Penjualan::where('status', '!=', 'Canceled')
                    ->where('gudang_id', $selectedGudangId)->count()
                    + Pembelian::where('status', '!=', 'Canceled')
                        ->where('gudang_id', $selectedGudangId)->count()
                    + Biaya::where('status', '!=', 'Canceled')->count()
                    + Kunjungan::where('status', '!=', 'Canceled')
                        ->where('gudang_id', $selectedGudangId)->count();
            } else {
                $data['totalProduk'] = Produk::count();
                $data['totalTransaksi'] = Penjualan::where('status', '!=', 'Canceled')->count()
                    + Pembelian::where('status', '!=', 'Canceled')->count()
                    + Biaya::where('status', '!=', 'Canceled')->count()
                    + Kunjungan::where('status', '!=', 'Canceled')->count();
            }

            // Ambil semua transaksi untuk tabel (exclude Canceled) - filtered by gudang
            $penjualans = Penjualan::with('user')->where('status', '!=', 'Canceled')
                ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                })->get();
            $pembelians = Pembelian::with('user')->where('status', '!=', 'Canceled')
                ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                })->get();
            $biayas = Biaya::with('user')->where('status', '!=', 'Canceled')->get();
            $kunjungans = Kunjungan::with('user')->where('status', '!=', 'Canceled')
                ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                })->get();

        } elseif ($role == 'admin') {
            // Admin lihat data sesuai gudang yang dipilih dari filter (sudah divalidasi aksesnya di atas)
            if ($selectedGudangId) {
                $penjualanQuery = Penjualan::where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled');
                $pembelianQuery = Pembelian::where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled');
                $biayaQuery = Biaya::where('status', '!=', 'Canceled'); // Biaya tidak punya gudang
                $kunjunganQuery = Kunjungan::where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled');

                $pendingCount = Penjualan::where('gudang_id', $selectedGudangId)->where('status', 'Pending')->count()
                    + Pembelian::where('gudang_id', $selectedGudangId)->where('status', 'Pending')->count()
                    + Biaya::where('status', 'Pending')->count()
                    + Kunjungan::where('gudang_id', $selectedGudangId)->where('status', 'Pending')->count();

                $data['card_4_title'] = 'Menunggu Approval Anda';
                $data['card_4_value'] = $pendingCount;
                $data['card_4_icon'] = 'fa-clock';

                // Statistik tambahan untuk admin (exclude Canceled) - dari gudang terpilih
                $data['totalProduk'] = GudangProduk::where('gudang_id', $selectedGudangId)->count();
                $data['totalTransaksi'] = Penjualan::where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled')->count()
                    + Pembelian::where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled')->count()
                    + Biaya::where('status', '!=', 'Canceled')->count()
                    + Kunjungan::where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled')->count();

                // Ambil transaksi di gudang terpilih (exclude Canceled)
                $penjualans = Penjualan::with('user')->where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled')->get();
                $pembelians = Pembelian::with('user')->where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled')->get();
                $biayas = Biaya::with('user')->where('status', '!=', 'Canceled')->get();
                $kunjungans = Kunjungan::with('user')->where('gudang_id', $selectedGudangId)->where('status', '!=', 'Canceled')->get();
            } else {
                // Admin tanpa gudang tidak bisa melihat apapun
                return view('dashboard', [
                    'card_4_title' => 'Belum Ditugaskan ke Gudang',
                    'card_4_value' => 0,
                    'card_4_icon' => 'fa-exclamation',
                    'totalProduk' => 0,
                    'totalTransaksi' => 0,
                    'penjualanBulanIni' => 0,
                    'pembelianBulanIni' => 0,
                    'biayaBulanIni' => 0,
                    'kunjunganBulanIni' => 0,
                    'biayaMasukBulanIni' => 0,
                    'biayaKeluarBulanIni' => 0,
                    'penjualanCountBulanIni' => 0,
                    'pembelianCountBulanIni' => 0,
                    'biayaCountBulanIni' => 0,
                    'kunjunganCountBulanIni' => 0,
                    'penjualanTotal' => 0,
                    'pembelianTotal' => 0,
                    'biayaTotal' => 0,
                    'kunjunganTotal' => 0,
                    'pembelianNominalBulanIni' => 0,
                    'availableGudangs' => collect(),
                    'selectedGudangId' => null,
                    'gudangs' => collect(),
                ]);
            }
        } else {
            $penjualanQuery = Penjualan::where('user_id', $userId)->where('status', '!=', 'Canceled');
            $pembelianQuery = Pembelian::where('user_id', $userId)->where('status', '!=', 'Canceled');
            $biayaQuery = Biaya::where('user_id', $userId)->where('status', '!=', 'Canceled');
            $kunjunganQuery = Kunjungan::where('user_id', $userId)->where('status', '!=', 'Canceled');

            $pendingCount = (clone $penjualanQuery)->where('status', 'Pending')->count()
                + (clone $pembelianQuery)->where('status', 'Pending')->count()
                + (clone $biayaQuery)->where('status', 'Pending')->count()
                + (clone $kunjunganQuery)->where('status', 'Pending')->count();

            $data['card_4_title'] = 'Data Menunggu Persetujuan';
            $data['card_4_value'] = $pendingCount;
            $data['card_4_icon'] = 'fa-clock';

            // Statistik produk untuk user berdasarkan gudang mereka
            if ($user->gudang_id) {
                $data['totalProduk'] = GudangProduk::where('gudang_id', $user->gudang_id)->count();
            } else {
                $data['totalProduk'] = 0;
            }
            $data['totalTransaksi'] = (clone $penjualanQuery)->count()
                + (clone $pembelianQuery)->count()
                + (clone $biayaQuery)->count()
                + (clone $kunjunganQuery)->count();
        }

        // ==================== STATISTIK TAMBAHAN ====================
        // Jumlah transaksi per tipe bulan ini
        $data['penjualanCountBulanIni'] = (clone $penjualanQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();

        $data['pembelianCountBulanIni'] = (clone $pembelianQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();

        $data['biayaCountBulanIni'] = (clone $biayaQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();

        $data['kunjunganCountBulanIni'] = (clone $kunjunganQuery)
            ->whereYear('tgl_kunjungan', $now->year)
            ->whereMonth('tgl_kunjungan', $now->month)
            ->count();

        // Total nominal keseluruhan (semua waktu)
        $data['penjualanTotal'] = (clone $penjualanQuery)->sum('grand_total');
        $data['pembelianTotal'] = (clone $pembelianQuery)->sum('grand_total');
        $data['biayaTotal'] = (clone $biayaQuery)->sum('grand_total');
        $data['kunjunganTotal'] = (clone $kunjunganQuery)->count();

        // Nominal pembelian bulan ini
        $data['pembelianNominalBulanIni'] = (clone $pembelianQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');
        // ==================== END STATISTIK TAMBAHAN ====================

        // ==================== CHART DATA ====================
        if (in_array($role, ['super_admin', 'admin'])) {
            // LINE CHART: Tren 6 bulan terakhir
            $chartLabels = [];
            $chartPenjualan = [];
            $chartPembelian = [];
            $chartBiaya = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $chartLabels[] = $month->translatedFormat('M Y');

                // Query berdasarkan role
                if ($role == 'super_admin') {
                    $chartPenjualan[] = Penjualan::whereYear('tgl_transaksi', $month->year)
                        ->whereMonth('tgl_transaksi', $month->month)
                        ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                            return $q->where('gudang_id', $selectedGudangId);
                        })
                        ->whereIn('status', ['Approved', 'Lunas'])
                        ->sum('grand_total');

                    $chartPembelian[] = Pembelian::whereYear('tgl_transaksi', $month->year)
                        ->whereMonth('tgl_transaksi', $month->month)
                        ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                            return $q->where('gudang_id', $selectedGudangId);
                        })
                        ->where('status', 'Approved')
                        ->sum('grand_total');

                    $chartBiaya[] = Biaya::whereYear('tgl_transaksi', $month->year)
                        ->whereMonth('tgl_transaksi', $month->month)
                        ->where('status', 'Approved')
                        ->sum('grand_total');
                } else {
                    // Admin: filter berdasarkan gudang yang diakses
                    if ($selectedGudangId) {
                        $chartPenjualan[] = Penjualan::whereYear('tgl_transaksi', $month->year)
                            ->whereMonth('tgl_transaksi', $month->month)
                            ->where('gudang_id', $selectedGudangId)
                            ->whereIn('status', ['Approved', 'Lunas'])
                            ->sum('grand_total');

                        $chartPembelian[] = Pembelian::whereYear('tgl_transaksi', $month->year)
                            ->whereMonth('tgl_transaksi', $month->month)
                            ->where('gudang_id', $selectedGudangId)
                            ->where('status', 'Approved')
                            ->sum('grand_total');
                    } else {
                        $chartPenjualan[] = 0;
                        $chartPembelian[] = 0;
                    }

                    $chartBiaya[] = Biaya::whereYear('tgl_transaksi', $month->year)
                        ->whereMonth('tgl_transaksi', $month->month)
                        ->where('status', 'Approved')
                        ->sum('grand_total');
                }
            }

            $data['chartLabels'] = $chartLabels;
            $data['chartPenjualan'] = $chartPenjualan;
            $data['chartPembelian'] = $chartPembelian;
            $data['chartBiaya'] = $chartBiaya;

            // DOUGHNUT CHART: Status transaksi
            if ($role == 'super_admin') {
                $allForStatus = Penjualan::when($selectedGudangId, function ($q) use ($selectedGudangId) {
                    return $q->where('gudang_id', $selectedGudangId);
                })->get()
                    ->concat(Pembelian::when($selectedGudangId, function ($q) use ($selectedGudangId) {
                        return $q->where('gudang_id', $selectedGudangId);
                    })->get())
                    ->concat(Biaya::all());
            } else {
                $allForStatus = Penjualan::where('gudang_id', $selectedGudangId)->get()
                    ->concat(Pembelian::where('gudang_id', $selectedGudangId)->get())
                    ->concat(Biaya::all());
            }

            $data['statusPending'] = $allForStatus->where('status', 'Pending')->count();
            $data['statusApproved'] = $allForStatus->whereIn('status', ['Approved', 'Lunas'])->count();
            $data['statusCanceled'] = $allForStatus->where('status', 'Canceled')->count();

            // BAR CHART: Transaksi per Gudang (bulan ini)
            $gudangs = $availableGudangs;
            $gudangLabels = [];
            $gudangPenjualan = [];
            $gudangPembelian = [];

            foreach ($gudangs as $gudang) {
                $gudangLabels[] = $gudang->nama_gudang;

                if ($role == 'super_admin') {
                    $gudangPenjualan[] = Penjualan::where('gudang_id', $gudang->id)
                        ->whereYear('tgl_transaksi', $now->year)
                        ->whereMonth('tgl_transaksi', $now->month)
                        ->whereIn('status', ['Approved', 'Lunas'])
                        ->sum('grand_total');

                    $gudangPembelian[] = Pembelian::where('gudang_id', $gudang->id)
                        ->whereYear('tgl_transaksi', $now->year)
                        ->whereMonth('tgl_transaksi', $now->month)
                        ->where('status', 'Approved')
                        ->sum('grand_total');
                } else {
                    // Admin: gunakan akses gudang, bukan approver_id
                    $gudangPenjualan[] = Penjualan::where('gudang_id', $gudang->id)
                        ->whereYear('tgl_transaksi', $now->year)
                        ->whereMonth('tgl_transaksi', $now->month)
                        ->whereIn('status', ['Approved', 'Lunas'])
                        ->sum('grand_total');

                    $gudangPembelian[] = Pembelian::where('gudang_id', $gudang->id)
                        ->whereYear('tgl_transaksi', $now->year)
                        ->whereMonth('tgl_transaksi', $now->month)
                        ->where('status', 'Approved')
                        ->sum('grand_total');
                }
            }

            $data['gudangLabels'] = $gudangLabels;
            $data['gudangPenjualan'] = $gudangPenjualan;
            $data['gudangPembelian'] = $gudangPembelian;
            $data['gudangs'] = $gudangs; // Untuk dropdown export & filter chart
        } else {
            $data['gudangs'] = collect([]); // User biasa tidak perlu filter gudang
        }
        // ==================== END CHART DATA ====================

        // Transform transaksi untuk tabel
        if (in_array($role, ['super_admin', 'admin'])) {
            $penjualans->each(function ($item) {
                $dateCode = $item->created_at->format('Ymd');
                $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
                $item->type = 'Penjualan';
                $item->route = route('penjualan.show', $item->id);
                $item->number = "INV-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            });
            $pembelians->each(function ($item) {
                $dateCode = $item->created_at->format('Ymd');
                $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
                $item->type = 'Pembelian';
                $item->route = route('pembelian.show', $item->id);
                $item->number = "PR-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            });
            $biayas->each(function ($item) {
                $dateCode = $item->created_at->format('Ymd');
                $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
                $item->type = 'Biaya';
                $item->route = route('biaya.show', $item->id);
                $item->number = "EXP-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            });
            $kunjungans->each(function ($item) {
                $dateCode = $item->created_at->format('Ymd');
                $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
                $item->type = 'Kunjungan';
                $item->route = route('kunjungan.show', $item->id);
                $item->number = "VST-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            });

            $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->concat($kunjungans)->sortByDesc('created_at')->values();

            // Manual Pagination
            $currentPage = $request->get('page', 1);
            $currentItems = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $data['allTransactions'] = new LengthAwarePaginator($currentItems, $allTransactions->count(), $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $data['penjualanBulanIni'] = (clone $penjualanQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');

        $data['pembelianBulanIni'] = (clone $pembelianQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();

        $data['biayaBulanIni'] = (clone $biayaQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');

        $data['kunjunganBulanIni'] = (clone $kunjunganQuery)
            ->whereYear('tgl_kunjungan', $now->year)
            ->whereMonth('tgl_kunjungan', $now->month)
            ->count();

        // Biaya Masuk dan Keluar (Approved only)
        $data['biayaMasukBulanIni'] = (clone $biayaQuery)
            ->where('jenis_biaya', 'masuk')
            ->where('status', 'Approved')
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');

        $data['biayaKeluarBulanIni'] = (clone $biayaQuery)
            ->where('jenis_biaya', 'keluar')
            ->where('status', 'Approved')
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');

        // Canceled transactions (Bulan Ini)
        $canceledPenjualan = Penjualan::where('status', 'Canceled')
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                return $q->where('gudang_id', $selectedGudangId);
            })
            ->count();
        
        $canceledPembelian = Pembelian::where('status', 'Canceled')
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                return $q->where('gudang_id', $selectedGudangId);
            })
            ->count();
        
        $canceledBiaya = Biaya::where('status', 'Canceled')
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();
        
        $canceledKunjungan = Kunjungan::where('status', 'Canceled')
            ->whereYear('tgl_kunjungan', $now->year)
            ->whereMonth('tgl_kunjungan', $now->month)
            ->when($selectedGudangId, function ($q) use ($selectedGudangId) {
                return $q->where('gudang_id', $selectedGudangId);
            })
            ->count();
        
        $data['canceledBulanIni'] = $canceledPenjualan + $canceledPembelian + $canceledBiaya + $canceledKunjungan;
        $data['canceledCountBulanIni'] = $data['canceledBulanIni'];

        $data['selectedGudangId'] = $selectedGudangId;

        return view('dashboard', $data);
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_type' => 'required|in:all,penjualan,pembelian,biaya,kunjungan',
            'status_filter' => 'nullable|in:all,Pending,Approved,Rejected,Canceled,Lunas',
            'gudang_id' => 'nullable|exists:gudangs,id',
            'biaya_jenis' => 'nullable|in:masuk,keluar',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $transactionType = $request->transaction_type;
        $statusFilter = $request->status_filter ?? 'all';
        $gudangId = $request->gudang_id;
        $biayaJenis = $request->biaya_jenis; // optional filter khusus biaya
        $user = Auth::user();

        $penjualans = collect();
        $pembelians = collect();
        $biayas = collect();
        $kunjungans = collect();

        // Helper function untuk generate custom number
        $generateNumber = function ($item, $prefix) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            return "{$prefix}-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
        };

        // PENJUALAN
        if (in_array($transactionType, ['all', 'penjualan'])) {
            $query = Penjualan::with('user', 'gudang', 'approver')
                ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo]);

            // Role-based filtering: Admin hanya bisa export dari gudang yang dia akses
            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->whereIn('gudang_id', $accessibleGudangIds);
            }

            // Gudang filter
            if ($gudangId) {
                // Validasi admin hanya bisa pilih gudang yang dia akses
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    abort(403, 'Tidak memiliki akses ke gudang ini');
                }
                $query->where('gudang_id', $gudangId);
            }

            // Status filter
            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }

            $penjualans = $query->get();
            $penjualans->each(function ($item) use ($generateNumber) {
                $item->type = 'Penjualan';
                $item->number = $generateNumber($item, 'INV');
            });
        }

        // PEMBELIAN
        if (in_array($transactionType, ['all', 'pembelian'])) {
            $query = Pembelian::with('user', 'gudang', 'approver')
                ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo]);

            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->whereIn('gudang_id', $accessibleGudangIds);
            }

            // Gudang filter
            if ($gudangId) {
                // Validasi admin hanya bisa pilih gudang yang dia akses
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    abort(403, 'Tidak memiliki akses ke gudang ini');
                }
                $query->where('gudang_id', $gudangId);
            }

            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }

            $pembelians = $query->get();
            $pembelians->each(function ($item) use ($generateNumber) {
                $item->type = 'Pembelian';
                $item->number = $generateNumber($item, 'PR');
            });
        }

        // BIAYA
        if (in_array($transactionType, ['all', 'biaya'])) {
            $query = Biaya::with('user', 'approver')
                ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo]);

            // Note: Biaya tidak memiliki gudang_id, tetap gunakan approver_id untuk admin
            if ($user->role == 'admin') {
                $query->where('approver_id', $user->id);
            }

            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }

            // Filter jenis biaya jika dipilih
            if ($transactionType === 'biaya' && $biayaJenis) {
                $query->where('jenis_biaya', $biayaJenis);
            }

            $biayas = $query->get();
            $biayas->each(function ($item) use ($generateNumber) {
                $item->type = 'Biaya';
                $item->number = $generateNumber($item, 'EXP');
            });
        }

        // KUNJUNGAN
        if (in_array($transactionType, ['all', 'kunjungan'])) {
            $query = Kunjungan::with('user', 'gudang', 'approver')
                ->whereBetween('tgl_kunjungan', [$dateFrom, $dateTo]);

            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->whereIn('gudang_id', $accessibleGudangIds);
            }

            // Gudang filter
            if ($gudangId) {
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    abort(403, 'Tidak memiliki akses ke gudang ini');
                }
                $query->where('gudang_id', $gudangId);
            }

            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }

            $kunjungans = $query->get();
            $kunjungans->each(function ($item) use ($generateNumber) {
                $item->type = 'Kunjungan';
                $item->number = $generateNumber($item, 'VST');
            });
        }

        // Determine export type and file name
        $typeLabel = [
            'all' => 'Semua_Transaksi',
            'penjualan' => 'Penjualan',
            'pembelian' => 'Pembelian',
            'biaya' => 'Biaya',
            'kunjungan' => 'Kunjungan'
        ];

        $gudangLabel = '';
        if ($gudangId) {
            $gudang = Gudang::find($gudangId);
            $gudangLabel = '_' . str_replace(' ', '_', $gudang->nama_gudang);
        }

        $fileName = 'Laporan_' . $typeLabel[$transactionType] . $gudangLabel . '_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        // Export based on type
        if ($transactionType == 'all') {
            $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->concat($kunjungans)->sortBy('created_at');
            return Excel::download(new TransactionsExport($allTransactions, 'all'), $fileName);
        } else {
            $data = ${$transactionType . 's'}; // $penjualans, $pembelians, $biayas, $kunjungans
            return Excel::download(new TransactionsExport($data, $transactionType), $fileName);
        }
    }
}