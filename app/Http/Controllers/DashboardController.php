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

class DashboardController extends Controller
{
    public function index()
    {
        $data = [];
        $now = Carbon::now();
        $role = Auth::user()->role;

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
                $item->type = 'Penjualan';
                $item->route = route('penjualan.show', $item->id);
                $item->number = $item->custom_number;
            });
            $pembelians->each(function ($item) {
                $item->type = 'Pembelian';
                $item->route = route('pembelian.show', $item->id);
                $item->number = $item->custom_number;
            });
            $biayas->each(function ($item) {
                $item->type = 'Biaya';
                $item->route = route('biaya.show', $item->id);
                $item->number = $item->custom_number;
            });

            $allTransactions = $penjualans->concat($pembelians)->concat($biayas);
            $data['allTransactions'] = $allTransactions->sortByDesc('created_at');

        } elseif ($role == 'admin') {
            // ADMIN: Hanya lihat transaksi yang perlu approval (status Pending)
            $penjualanQuery = Penjualan::query();
            $pembelianQuery = Pembelian::query();
            $biayaQuery = Biaya::query();

            $pendingCount = Penjualan::where('status', 'Pending')->count()
                + Pembelian::where('status', 'Pending')->count()
                + Biaya::where('status', 'Pending')->count();

            $data['card_4_title'] = 'Menunggu Approval';
            $data['card_4_value'] = $pendingCount;
            $data['card_4_icon'] = 'fa-clock';

            // Ambil hanya transaksi dengan status Pending untuk di-approve
            $penjualans = Penjualan::with('user')->where('status', 'Pending')->get();
            $pembelians = Pembelian::with('user')->where('status', 'Pending')->get();
            $biayas = Biaya::with('user')->where('status', 'Pending')->get();

            $penjualans->each(function ($item) {
                $item->type = 'Penjualan';
                $item->route = route('penjualan.show', $item->id);
                $item->number = $item->custom_number;
            });
            $pembelians->each(function ($item) {
                $item->type = 'Pembelian';
                $item->route = route('pembelian.show', $item->id);
                $item->number = $item->custom_number;
            });
            $biayas->each(function ($item) {
                $item->type = 'Biaya';
                $item->route = route('biaya.show', $item->id);
                $item->number = $item->custom_number;
            });

            $allTransactions = $penjualans->concat($pembelians)->concat($biayas);
            $data['allTransactions'] = $allTransactions->sortByDesc('created_at');

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
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $penjualans = Penjualan::with('user', 'gudang')
            ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo])
            ->get();
        $pembelians = Pembelian::with('user', 'gudang')
            ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo])
            ->get();
        $biayas = Biaya::with('user')
            ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo])
            ->get();

        $penjualans->each(function ($item) {
            $item->type = 'Penjualan';
            $item->route = route('penjualan.show', $item->id);
            $item->number = $item->custom_number;
        });
        $pembelians->each(function ($item) {
            $item->type = 'Pembelian';
            $item->route = route('pembelian.show', $item->id);
            $item->number = $item->custom_number;
        });
        $biayas->each(function ($item) {
            $item->type = 'Biaya';
            $item->route = route('biaya.show', $item->id);
            $item->number = $item->custom_number;
        });

        $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->sortBy('tgl_transaksi');

        $fileName = 'Laporan_Transaksi_' . $dateFrom . '_sampai_' . $dateTo . '.xlsx';

        return Excel::download(new TransactionsExport($allTransactions), $fileName);
    }
}