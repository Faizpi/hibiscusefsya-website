<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pembelian;
use App\Pembayaran;
use App\PenerimaanBarang;
use App\Biaya;
use App\Kunjungan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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
        $kunjungan = Kunjungan::where('uuid', $uuid)->with(['user', 'gudang', 'approver'])->firstOrFail();

        return view('public.invoice-kunjungan', compact('kunjungan'));
    }

    /**
     * Download PDF for Kunjungan
     */
    public function downloadKunjungan($uuid)
    {
        $kunjungan = Kunjungan::where('uuid', $uuid)->with(['user', 'gudang', 'approver'])->firstOrFail();

        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $filename = "VST-{$kunjungan->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-kunjungan-pdf', compact('kunjungan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Show public invoice for Pembayaran
     */
    public function showPembayaran($uuid)
    {
        $pembayaran = Pembayaran::where('uuid', $uuid)->with(['penjualan', 'user', 'gudang', 'approver'])->firstOrFail();

        return view('public.invoice-pembayaran', compact('pembayaran'));
    }

    /**
     * Download PDF for Pembayaran
     */
    public function downloadPembayaran($uuid)
    {
        $pembayaran = Pembayaran::where('uuid', $uuid)->with(['penjualan', 'user', 'gudang', 'approver'])->firstOrFail();

        $dateCode = $pembayaran->created_at->format('Ymd');
        $noUrut = str_pad($pembayaran->no_urut_harian ?? 1, 3, '0', STR_PAD_LEFT);
        $filename = "PAY-{$pembayaran->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-pembayaran-pdf', compact('pembayaran'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Show public invoice for Penerimaan Barang
     */
    public function showPenerimaanBarang($uuid)
    {
        $penerimaan = PenerimaanBarang::where('uuid', $uuid)->with(['items.produk', 'items.pembelianItem.pembelian', 'user', 'gudang', 'approver', 'supplier'])->firstOrFail();

        return view('public.invoice-penerimaan', compact('penerimaan'));
    }

    /**
     * Download PDF for Penerimaan Barang
     */
    public function downloadPenerimaanBarang($uuid)
    {
        $penerimaan = PenerimaanBarang::where('uuid', $uuid)->with(['items.produk', 'items.pembelianItem.pembelian', 'user', 'gudang', 'approver', 'supplier'])->firstOrFail();

        $dateCode = $penerimaan->created_at->format('Ymd');
        $noUrut = str_pad($penerimaan->no_urut_harian ?? 1, 3, '0', STR_PAD_LEFT);
        $filename = "GRN-{$penerimaan->user_id}-{$dateCode}-{$noUrut}.pdf";

        $pdf = PDF::loadView('public.invoice-penerimaan-pdf', compact('penerimaan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
