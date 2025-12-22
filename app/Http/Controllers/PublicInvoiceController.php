<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Illuminate\Http\Request;
use PDF;

/**
 * Controller untuk Public Invoice
 * 
 * Menampilkan invoice yang bisa diakses tanpa login.
 * Digunakan untuk QR code pada struk yang bisa di-scan
 * oleh pelanggan untuk melihat/download invoice.
 * 
 * Security: Menggunakan UUID instead of ID untuk prevent enumeration attack
 */
class PublicInvoiceController extends Controller
{
    /**
     * Show public invoice for Penjualan
     */
    public function showPenjualan($uuid)
    {
        $penjualan = Penjualan::where('uuid', $uuid)->with(['items.produk', 'user', 'gudang', 'approver'])->firstOrFail();

        return view('public.invoice-penjualan', compact('penjualan'));
    }

    /**
     * Download PDF for Penjualan
     */
    public function downloadPenjualan($uuid)
    {
        $penjualan = Penjualan::where('uuid', $uuid)->with(['items.produk', 'user', 'gudang', 'approver'])->firstOrFail();

        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $filename = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-penjualan-pdf', compact('penjualan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Show public invoice for Pembelian
     */
    public function showPembelian($uuid)
    {
        $pembelian = Pembelian::where('uuid', $uuid)->with(['items.produk', 'user', 'gudang', 'approver'])->firstOrFail();

        return view('public.invoice-pembelian', compact('pembelian'));
    }

    /**
     * Download PDF for Pembelian
     */
    public function downloadPembelian($uuid)
    {
        $pembelian = Pembelian::where('uuid', $uuid)->with(['items.produk', 'user', 'gudang', 'approver'])->firstOrFail();

        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrut = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $filename = "PR-{$pembelian->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-pembelian-pdf', compact('pembelian'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Show public invoice for Biaya
     */
    public function showBiaya($uuid)
    {
        $biaya = Biaya::where('uuid', $uuid)->with(['items', 'user', 'approver'])->firstOrFail();

        return view('public.invoice-biaya', compact('biaya'));
    }

    /**
     * Download PDF for Biaya
     */
    public function downloadBiaya($uuid)
    {
        $biaya = Biaya::where('uuid', $uuid)->with(['items', 'user', 'approver'])->firstOrFail();

        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $filename = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-biaya-pdf', compact('biaya'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Show public invoice for Kunjungan
     */
    public function showKunjungan($uuid)
    {
        $kunjungan = \App\Kunjungan::where('uuid', $uuid)->with(['user', 'gudang', 'approver'])->firstOrFail();

        return view('public.invoice-kunjungan', compact('kunjungan'));
    }

    /**
     * Download PDF for Kunjungan
     */
    public function downloadKunjungan($uuid)
    {
        $kunjungan = \App\Kunjungan::where('uuid', $uuid)->with(['user', 'gudang', 'approver'])->firstOrFail();

        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $filename = "VST-{$kunjungan->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-kunjungan-pdf', compact('kunjungan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
