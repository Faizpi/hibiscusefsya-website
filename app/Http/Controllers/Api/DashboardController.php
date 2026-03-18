<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use App\Kunjungan;
use App\Kontak;
use App\User;
use App\Produk;
use App\GudangProduk;
use App\Gudang;
use App\Exports\TransactionsExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role;
        $now = Carbon::now();

        $data = [];

        if ($role == 'super_admin') {
            $penjualanQuery = Penjualan::where('status', '!=', 'Canceled');
            $pembelianQuery = Pembelian::where('status', '!=', 'Canceled');
            $biayaQuery = Biaya::where('status', '!=', 'Canceled');

            $data['total_produk'] = Produk::count();
            $data['total_user'] = User::count();
            $data['total_gudang'] = Gudang::count();
        } elseif (in_array($role, ['admin', 'spectator'])) {
            $currentGudang = $user->getCurrentGudang();
            $gudangId = $currentGudang ? $currentGudang->id : 0;

            $penjualanQuery = Penjualan::where('status', '!=', 'Canceled')->where('gudang_id', $gudangId);
            $pembelianQuery = Pembelian::where('status', '!=', 'Canceled')->where('gudang_id', $gudangId);
            $biayaQuery = Biaya::where('status', '!=', 'Canceled');

            $data['current_gudang'] = $currentGudang ? $currentGudang->nama_gudang : null;
            $data['total_produk'] = GudangProduk::where('gudang_id', $gudangId)->count();
        } else {
            $penjualanQuery = Penjualan::where('status', '!=', 'Canceled')->where('user_id', $user->id);
            $pembelianQuery = Pembelian::where('status', '!=', 'Canceled')->where('user_id', $user->id);
            $biayaQuery = Biaya::where('status', '!=', 'Canceled')->where('user_id', $user->id);
        }

        // Summary counts
        $data['penjualan_bulan_ini'] = (clone $penjualanQuery)
            ->whereMonth('tgl_transaksi', $now->month)
            ->whereYear('tgl_transaksi', $now->year)
            ->count();

        $data['total_penjualan_bulan_ini'] = (clone $penjualanQuery)
            ->whereMonth('tgl_transaksi', $now->month)
            ->whereYear('tgl_transaksi', $now->year)
            ->sum('grand_total');

        $data['pembelian_bulan_ini'] = (clone $pembelianQuery)
            ->whereMonth('tgl_transaksi', $now->month)
            ->whereYear('tgl_transaksi', $now->year)
            ->count();

        $data['total_pembelian_bulan_ini'] = (clone $pembelianQuery)
            ->whereMonth('tgl_transaksi', $now->month)
            ->whereYear('tgl_transaksi', $now->year)
            ->sum('grand_total');

        $data['biaya_bulan_ini'] = (clone $biayaQuery)
            ->whereMonth('tgl_transaksi', $now->month)
            ->whereYear('tgl_transaksi', $now->year)
            ->sum('grand_total');

        $data['pending_approval'] = (clone $penjualanQuery)->where('status', 'Pending')->count()
            + Pembelian::where('status', 'Pending')->when($role != 'super_admin', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();

        // Recent transactions
        $data['recent_penjualan'] = (clone $penjualanQuery)
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get(['id', 'nomor', 'pelanggan', 'grand_total', 'status', 'tgl_transaksi', 'user_id']);

        return response()->json($data);
    }

    /**
     * Laporan Harian - semua aktivitas user hari ini (JSON)
     */
    public function dailyReport(Request $request)
    {
        $user = auth()->user();
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $penjualans = Penjualan::where('user_id', $user->id)
            ->whereDate('tgl_transaksi', $date)
            ->with('gudang:id,nama_gudang')
            ->orderBy('created_at', 'asc')
            ->get();

        $pembelians = Pembelian::where('user_id', $user->id)
            ->whereDate('tgl_transaksi', $date)
            ->with('gudang:id,nama_gudang')
            ->orderBy('created_at', 'asc')
            ->get();

        $biayas = Biaya::where('user_id', $user->id)
            ->whereDate('tgl_transaksi', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        $kunjungans = Kunjungan::where('user_id', $user->id)
            ->whereDate('tgl_kunjungan', $date)
            ->with('kontak:id,nama')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'sales_name' => $user->name,
            'summary' => [
                'total_penjualan' => $penjualans->count(),
                'nilai_penjualan' => $penjualans->sum('grand_total'),
                'total_pembelian' => $pembelians->count(),
                'nilai_pembelian' => $pembelians->sum('grand_total'),
                'total_biaya' => $biayas->count(),
                'nilai_biaya' => $biayas->sum('grand_total'),
                'total_kunjungan' => $kunjungans->count(),
                'total_aktivitas' => $penjualans->count() + $pembelians->count() + $biayas->count() + $kunjungans->count(),
            ],
            'penjualans' => $penjualans,
            'pembelians' => $pembelians,
            'biayas' => $biayas,
            'kunjungans' => $kunjungans,
        ]);
    }

    /**
     * Laporan Harian PDF - download file PDF
     */
    public function dailyReportPdf(Request $request)
    {
        $user = auth()->user();
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $penjualans = Penjualan::where('user_id', $user->id)
            ->whereDate('tgl_transaksi', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        $pembelians = Pembelian::where('user_id', $user->id)
            ->whereDate('tgl_transaksi', $date)
            ->with('gudang')
            ->orderBy('created_at', 'asc')
            ->get();

        $biayas = Biaya::where('user_id', $user->id)
            ->whereDate('tgl_transaksi', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        $kunjungans = Kunjungan::where('user_id', $user->id)
            ->whereDate('tgl_kunjungan', $date)
            ->with('kontak')
            ->orderBy('created_at', 'asc')
            ->get();

        $pdf = Pdf::loadView('reports.daily-report', [
            'penjualans' => $penjualans,
            'pembelians' => $pembelians,
            'biayas' => $biayas,
            'kunjungans' => $kunjungans,
            'salesName' => $user->name,
            'date' => $date->format('Y-m-d'),
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ]);
        $pdf->setPaper('a4', 'landscape');

        $fileName = 'Laporan-Harian-' . $user->name . '-' . $date->format('Ymd') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export Report PDF/Excel - sama persis dengan website
     */
    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_type' => 'required|in:all,penjualan,pembelian,biaya,kunjungan',
            'status_filter' => 'nullable|in:all,Pending,Approved,Rejected,Canceled,Lunas',
            'gudang_id' => 'nullable|exists:gudangs,id',
            'biaya_jenis' => 'nullable|in:masuk,keluar',
            'tujuan_filter' => 'nullable|string',
            'export_format' => 'nullable|in:excel,pdf',
            'sales_id' => 'nullable|exists:users,id',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $transactionType = $request->transaction_type;
        $statusFilter = $request->status_filter ?: 'all';
        $gudangId = $request->gudang_id;
        $biayaJenis = $request->biaya_jenis;
        $tujuanFilter = $request->tujuan_filter;
        $exportFormat = $request->export_format ?: 'excel';
        $salesId = $request->sales_id;
        $user = auth()->user();

        $penjualans = collect();
        $pembelians = collect();
        $biayas = collect();
        $kunjungans = collect();

        // Map kontak nama -> no_telp
        $kontakPhoneMap = Kontak::whereNotNull('no_telp')
            ->where('no_telp', '!=', '')
            ->pluck('no_telp', 'nama')
            ->toArray();

        // Helper generate nomor
        $generateNumber = function ($item, $prefix) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            return "{$prefix}-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
        };

        // PENJUALAN
        if (in_array($transactionType, ['all', 'penjualan'])) {
            $query = Penjualan::with('user', 'gudang', 'approver', 'items.produk')
                ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo]);

            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->whereIn('gudang_id', $accessibleGudangIds);
            }
            if ($gudangId) {
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
                }
                $query->where('gudang_id', $gudangId);
            }
            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }
            if ($salesId) {
                $query->where('user_id', $salesId);
            }

            $penjualans = $query->get();
            $penjualans->each(function ($item) use ($generateNumber, $kontakPhoneMap) {
                $item->type = 'Penjualan';
                $item->number = $generateNumber($item, 'INV');
                $item->display_contact_name = $item->pelanggan ?: '-';
                $item->no_telp_kontak = $kontakPhoneMap[$item->pelanggan] ?? '-';
            });
        }

        // PEMBELIAN
        if (in_array($transactionType, ['all', 'pembelian'])) {
            $query = Pembelian::with('user', 'gudang', 'approver', 'items.produk')
                ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo]);

            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->whereIn('gudang_id', $accessibleGudangIds);
            }
            if ($gudangId) {
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
                }
                $query->where('gudang_id', $gudangId);
            }
            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }
            if ($salesId) {
                $query->where('user_id', $salesId);
            }

            $pembelians = $query->get();
            $pembelians->each(function ($item) use ($generateNumber) {
                $item->type = 'Pembelian';
                $item->number = $generateNumber($item, 'PR');
                $item->display_contact_name = '-';
                $item->no_telp_kontak = '-';
            });
        }

        // BIAYA
        if (in_array($transactionType, ['all', 'biaya'])) {
            $query = Biaya::with('user', 'approver', 'items', 'gudang')
                ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo]);

            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->where(function ($q) use ($accessibleGudangIds, $user) {
                    $q->whereIn('gudang_id', $accessibleGudangIds)
                        ->orWhere('user_id', $user->id)
                        ->orWhere('approver_id', $user->id);
                });
            }
            if ($gudangId) {
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
                }
                $query->where('gudang_id', $gudangId);
            }
            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }
            if ($transactionType === 'biaya' && $biayaJenis) {
                $query->where('jenis_biaya', $biayaJenis);
            }
            if ($salesId) {
                $query->where('user_id', $salesId);
            }

            $biayas = $query->get();
            $biayas->each(function ($item) use ($generateNumber, $kontakPhoneMap) {
                $item->type = 'Biaya';
                $item->number = $generateNumber($item, 'EXP');
                $item->display_contact_name = $item->penerima ?: '-';
                $item->no_telp_kontak = $kontakPhoneMap[$item->penerima] ?? '-';
            });
        }

        // KUNJUNGAN
        if (in_array($transactionType, ['all', 'kunjungan'])) {
            $query = Kunjungan::with('user', 'gudang', 'approver', 'items.produk', 'kontak')
                ->whereBetween('tgl_kunjungan', [$dateFrom, $dateTo]);

            if ($user->role == 'admin') {
                $accessibleGudangIds = $user->gudangs()->pluck('gudangs.id');
                $query->whereIn('gudang_id', $accessibleGudangIds);
            }
            if ($gudangId) {
                if ($user->role == 'admin' && !$user->canAccessGudang($gudangId)) {
                    return response()->json(['message' => 'Tidak memiliki akses ke gudang ini.'], 403);
                }
                $query->where('gudang_id', $gudangId);
            }
            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }
            if ($tujuanFilter && $tujuanFilter !== 'all') {
                $query->where('tujuan', $tujuanFilter);
            }
            if ($salesId) {
                $query->where('user_id', $salesId);
            }

            $kunjungans = $query->get();
            $kunjungans->each(function ($item) use ($generateNumber) {
                $item->type = 'Kunjungan';
                $item->number = $generateNumber($item, 'VST');
                $item->display_contact_name = optional($item->kontak)->nama ?: '-';
                $item->no_telp_kontak = optional($item->kontak)->no_telp ?: '-';
            });
        }

        // Build file name
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

        $salesLabel = '';
        if ($salesId) {
            $salesUser = User::find($salesId);
            $salesLabel = '_Sales_' . str_replace(' ', '_', $salesUser->name);
        }

        $fileBaseName = 'Laporan_' . $typeLabel[$transactionType] . $gudangLabel . $salesLabel . '_' . $dateFrom . '_sd_' . $dateTo;

        // Prepare export data
        if ($transactionType == 'all') {
            $exportData = $penjualans->concat($pembelians)->concat($biayas)->concat($kunjungans)->sortBy('created_at');
        } elseif ($transactionType == 'penjualan') {
            $exportData = $penjualans;
        } elseif ($transactionType == 'pembelian') {
            $exportData = $pembelians;
        } elseif ($transactionType == 'biaya') {
            $exportData = $biayas;
        } else {
            $exportData = $kunjungans;
        }

        // Export based on format
        if ($exportFormat === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', [
                'transactions' => $exportData,
                'exportType' => $transactionType,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'generatedBy' => $user->name,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
            ]);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download($fileBaseName . '.pdf');
        }

        $fileName = $fileBaseName . '.xlsx';
        return Excel::download(new TransactionsExport($exportData, $transactionType, $user->name), $fileName);
    }

    /**
     * Download lampiran file
     */
    public function downloadLampiran(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->path;

        // Security: only allow access to lampiran folders
        $allowedPrefixes = [
            'lampiran_penjualan/',
            'lampiran_pembelian/',
            'lampiran_biaya/',
            'lampiran_kunjungan/',
            'lampiran_pembayaran/',
            'lampiran_penerimaan/',
        ];
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed || strpos($path, '..') !== false) {
            return response()->json(['message' => 'Path tidak valid.'], 403);
        }

        $fullPath = public_path('storage/' . $path);

        if (!File::exists($fullPath)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->download($fullPath);
    }
}
