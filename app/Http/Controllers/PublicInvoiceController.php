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
 */
class PublicInvoiceController extends Controller
{
    /**
     * Show public invoice for Penjualan
     */
    public function showPenjualan($id)
    {
        $penjualan = Penjualan::with(['items.produk', 'user', 'gudang', 'approver'])->findOrFail($id);

        return view('public.invoice-penjualan', compact('penjualan'));
    }

    /**
     * Download PDF for Penjualan
     */
    public function downloadPenjualan($id)
    {
        $penjualan = Penjualan::with(['items.produk', 'user', 'gudang', 'approver'])->findOrFail($id);

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
    public function showPembelian($id)
    {
        $pembelian = Pembelian::with(['items.produk', 'user', 'gudang', 'approver'])->findOrFail($id);

        return view('public.invoice-pembelian', compact('pembelian'));
    }

    /**
     * Download PDF for Pembelian
     */
    public function downloadPembelian($id)
    {
        $pembelian = Pembelian::with(['items.produk', 'user', 'gudang', 'approver'])->findOrFail($id);

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
    public function showBiaya($id)
    {
        $biaya = Biaya::with(['items', 'user', 'approver'])->findOrFail($id);

        return view('public.invoice-biaya', compact('biaya'));
    }

    /**
     * Download PDF for Biaya
     */
    public function downloadBiaya($id)
    {
        $biaya = Biaya::with(['items', 'user', 'approver'])->findOrFail($id);

        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $filename = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-biaya-pdf', compact('biaya'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
