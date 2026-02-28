<?php

namespace App\Http\Controllers;

use App\Kontak;
use App\Penjualan;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    /**
     * Tampilkan halaman login customer
     */
    public function loginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (session('customer_id')) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.login');
    }

    /**
     * Proses login customer via no_telp + pin
     */
    public function login(Request $request)
    {
        $request->validate([
            'no_telp' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        $kontak = Kontak::where('no_telp', $request->no_telp)
            ->where('pin', $request->pin)
            ->first();

        if (!$kontak) {
            return back()->with('error', 'No. Telepon atau PIN salah.')->withInput(['no_telp' => $request->no_telp]);
        }

        if (empty($kontak->pin)) {
            return back()->with('error', 'Akun belum diaktifkan. Hubungi admin untuk mengatur PIN.')->withInput(['no_telp' => $request->no_telp]);
        }

        // Set session
        session([
            'customer_id' => $kontak->id,
            'customer_no_telp' => $kontak->no_telp,
            'customer_nama' => $kontak->nama,
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Selamat datang, ' . $kontak->nama . '!');
    }

    /**
     * Customer dashboard - data kontak + diskon
     */
    public function dashboard()
    {
        $kontak = Kontak::findOrFail(session('customer_id'));

        // Hitung total transaksi
        $totalTransaksi = Penjualan::where('pelanggan', $kontak->nama)
            ->whereIn('status', ['Approved', 'Lunas'])
            ->count();

        $totalNilai = Penjualan::where('pelanggan', $kontak->nama)
            ->whereIn('status', ['Approved', 'Lunas'])
            ->sum('grand_total');

        return view('customer.dashboard', compact('kontak', 'totalTransaksi', 'totalNilai'));
    }

    /**
     * History pembelian customer
     */
    public function history(Request $request)
    {
        $kontak = Kontak::findOrFail(session('customer_id'));

        $query = Penjualan::where('pelanggan', $kontak->nama)
            ->with(['items.produk', 'gudang'])
            ->whereIn('status', ['Approved', 'Lunas', 'Pending']);

        // Filter tanggal
        if ($request->filled('dari')) {
            $query->whereDate('tgl_transaksi', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tgl_transaksi', '<=', $request->sampai);
        }

        $penjualans = $query->orderBy('tgl_transaksi', 'desc')->paginate(15);

        return view('customer.history', compact('kontak', 'penjualans'));
    }

    /**
     * Detail satu transaksi
     */
    public function historyDetail($id)
    {
        $kontak = Kontak::findOrFail(session('customer_id'));

        $penjualan = Penjualan::where('pelanggan', $kontak->nama)
            ->with(['items.produk', 'gudang', 'user'])
            ->findOrFail($id);

        return view('customer.history-detail', compact('kontak', 'penjualan'));
    }

    /**
     * Logout customer
     */
    public function logout()
    {
        session()->forget(['customer_id', 'customer_no_telp', 'customer_nama']);
        return redirect()->route('customer.login')->with('success', 'Berhasil logout.');
    }
}
