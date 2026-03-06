<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use App\Kunjungan;
use App\User;
use App\Produk;
use App\GudangProduk;
use App\Gudang;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
