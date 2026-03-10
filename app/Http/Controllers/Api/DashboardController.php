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
use Illuminate\Support\Facades\File;

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
     * Laporan Harian - semua aktivitas user hari ini
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
     * Download lampiran file
     */
    public function downloadLampiran(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->path;

        // Security: only allow access to lampiran folders
        $allowedPrefixes = ['lampiran_penjualan/', 'lampiran_pembelian/', 'lampiran_biaya/', 'lampiran_kunjungan/'];
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
