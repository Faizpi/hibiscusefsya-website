<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use App\User;
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

        if ($role == 'super_admin') {
            // SUPER ADMIN: Lihat SEMUA transaksi
            $penjualanQuery = Penjualan::query();
            $pembelianQuery = Pembelian::query();
            $biayaQuery = Biaya::query();

            $data['card_4_title'] = 'Jumlah User Terdaftar';
            $data['card_4_value'] = User::count();
            $data['card_4_icon'] = 'fa-users';

            // Ambil semua transaksi
            $penjualans = Penjualan::with('user')->get();
            $pembelians = Pembelian::with('user')->get();
            $biayas = Biaya::with('user')->get();

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

            $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->sortByDesc('created_at')->values();
            
            // Manual Pagination
            $currentPage = $request->get('page', 1);
            $currentItems = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $data['allTransactions'] = new LengthAwarePaginator($currentItems, $allTransactions->count(), $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        } elseif ($role == 'admin') {
            // ADMIN: Hanya lihat transaksi yang DIA sebagai approver
            $userId = Auth::id();

            $penjualanQuery = Penjualan::where('approver_id', $userId);
            $pembelianQuery = Pembelian::where('approver_id', $userId);
            $biayaQuery = Biaya::where('approver_id', $userId);

            $pendingCount = Penjualan::where('approver_id', $userId)->where('status', 'Pending')->count()
                + Pembelian::where('approver_id', $userId)->where('status', 'Pending')->count()
                + Biaya::where('approver_id', $userId)->where('status', 'Pending')->count();

            $data['card_4_title'] = 'Menunggu Approval Anda';
            $data['card_4_value'] = $pendingCount;
            $data['card_4_icon'] = 'fa-clock';

            // Ambil transaksi yang dia sebagai approver
            $penjualans = Penjualan::with('user')->where('approver_id', $userId)->get();
            $pembelians = Pembelian::with('user')->where('approver_id', $userId)->get();
            $biayas = Biaya::with('user')->where('approver_id', $userId)->get();

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

            $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->sortByDesc('created_at')->values();
            
            // Manual Pagination
            $currentPage = $request->get('page', 1);
            $currentItems = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $data['allTransactions'] = new LengthAwarePaginator($currentItems, $allTransactions->count(), $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        } else {
            // USER: Hanya lihat transaksi milik sendiri
            $userId = Auth::id();
            $penjualanQuery = Penjualan::where('user_id', $userId);
            $pembelianQuery = Pembelian::where('user_id', $userId);
            $biayaQuery = Biaya::where('user_id', $userId);

            $pendingCount = (clone $penjualanQuery)->where('status', 'Pending')->count()
                + (clone $pembelianQuery)->where('status', 'Pending')->count()
                + (clone $biayaQuery)->where('status', 'Pending')->count();

            $data['card_4_title'] = 'Data Menunggu Persetujuan';
            $data['card_4_value'] = $pendingCount;
            $data['card_4_icon'] = 'fa-clock';
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

        return view('dashboard', $data);
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_type' => 'required|in:all,penjualan,pembelian,biaya',
            'status_filter' => 'nullable|in:all,Pending,Approved,Rejected,Canceled',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $transactionType = $request->transaction_type;
        $statusFilter = $request->status_filter ?? 'all';
        $user = Auth::user();

        $penjualans = collect();
        $pembelians = collect();
        $biayas = collect();

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

            // Role-based filtering: Admin hanya bisa export yang dia sebagai approver
            if ($user->role == 'admin') {
                $query->where('approver_id', $user->id);
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
                $query->where('approver_id', $user->id);
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

            if ($user->role == 'admin') {
                $query->where('approver_id', $user->id);
            }

            if ($statusFilter != 'all') {
                $query->where('status', $statusFilter);
            }

            $biayas = $query->get();
            $biayas->each(function ($item) use ($generateNumber) {
                $item->type = 'Biaya';
                $item->number = $generateNumber($item, 'EXP');
            });
        }

        // Determine export type and file name
        $typeLabel = [
            'all' => 'Semua_Transaksi',
            'penjualan' => 'Penjualan',
            'pembelian' => 'Pembelian',
            'biaya' => 'Biaya'
        ];

        $fileName = 'Laporan_' . $typeLabel[$transactionType] . '_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        // Export based on type
        if ($transactionType == 'all') {
            $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->sortBy('tgl_transaksi');
            return Excel::download(new TransactionsExport($allTransactions, 'all'), $fileName);
        } else {
            $data = ${$transactionType . 's'}; // $penjualans, $pembelians, $biayas
            return Excel::download(new TransactionsExport($data, $transactionType), $fileName);
        }
    }
}