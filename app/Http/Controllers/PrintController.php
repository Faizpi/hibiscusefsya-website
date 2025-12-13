<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Print Penjualan as Rich Text for Thermer Bluetooth
     */
    public function penjualanRichText($id)
    {
        $penjualan = Penjualan::with([
            'items.produk',
            'user',
            'gudang',
            'approver'
        ])->findOrFail($id);

        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";

        $out = "";
        
        // Header
        $out .= "   HIBISCUS EFSYA\n";
        $out .= " INVOICE PENJUALAN\n";
        $out .= "--------------------------\n";

        // Info
        $out .= "No : " . $nomorInvoice . "\n";
        $out .= "Tgl: " . $penjualan->tgl_transaksi->format('d/m/Y') . " " . $penjualan->created_at->format('H:i') . "\n";
        $out .= "Cst: " . ($penjualan->pelanggan ?? '-') . "\n";
        $out .= "Sls: " . ($penjualan->user->name ?? '-') . "\n";
        $out .= "--------------------------\n";

        // Items
        foreach ($penjualan->items as $item) {
            $out .= $item->produk->nama_produk . "\n";
            $out .= $item->kuantitas . " " . ($item->unit ?? 'Pcs');
            $out .= " x " . number_format($item->harga_satuan, 0, ',', '.') . "\n";
            
            if ($item->diskon > 0) {
                $out .= " Disc " . $item->diskon . "%\n";
            }
            
            $out .= " = Rp " . number_format($item->jumlah_baris, 0, ',', '.') . "\n";
            $out .= "\n";
        }

        $out .= "--------------------------\n";

        // Total
        $subtotal = $penjualan->items->sum('jumlah_baris');
        $out .= "Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n";

        if ($penjualan->diskon_akhir > 0) {
            $out .= "Diskon  : Rp " . number_format($penjualan->diskon_akhir, 0, ',', '.') . "\n";
        }

        if ($penjualan->tax_percentage > 0) {
            $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
            $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
            $out .= "Pajak " . $penjualan->tax_percentage . "%: Rp " . number_format($pajakNominal, 0, ',', '.') . "\n";
        }

        $out .= "==========================\n";
        $out .= "TOTAL: Rp " . number_format($penjualan->grand_total, 0, ',', '.') . "\n";
        $out .= "==========================\n";

        // Footer
        $out .= "\n";
        $out .= "  -- Terima Kasih --\n";

        return response($out)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Print Pembelian as Rich Text for Thermer Bluetooth
     */
    public function pembelianRichText($id)
    {
        $pembelian = Pembelian::with([
            'items.produk',
            'user',
            'gudang',
            'approver'
        ])->findOrFail($id);

        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrut = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "PR-{$pembelian->user_id}-{$dateCode}-{$noUrut}";

        $out = "";
        
        // Header
        $out .= "   HIBISCUS EFSYA\n";
        $out .= "PERMINTAAN PEMBELIAN\n";
        $out .= "--------------------------\n";

        // Info
        $out .= "No : " . $nomorInvoice . "\n";
        $out .= "Tgl: " . $pembelian->tgl_transaksi->format('d/m/Y') . " " . $pembelian->created_at->format('H:i') . "\n";
        $out .= "Vnd: " . ($pembelian->staf_penyetuju ?? '-') . "\n";
        $out .= "Req: " . ($pembelian->user->name ?? '-') . "\n";
        $out .= "--------------------------\n";

        $subtotal = $pembelian->items->sum('jumlah_baris');
        
        // Items
        foreach ($pembelian->items as $item) {
            $out .= $item->produk->nama_produk . "\n";
            $out .= $item->kuantitas . " " . ($item->unit ?? 'Pcs');
            $out .= " x " . number_format($item->harga_satuan, 0, ',', '.') . "\n";
            
            if ($item->diskon > 0) {
                $out .= " Disc " . $item->diskon . "%\n";
            }
            
            $out .= " = Rp " . number_format($item->jumlah_baris, 0, ',', '.') . "\n";
            $out .= "\n";
        }

        $out .= "--------------------------\n";

        // Total
        $out .= "Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n";

        if ($pembelian->diskon_akhir > 0) {
            $out .= "Diskon  : Rp " . number_format($pembelian->diskon_akhir, 0, ',', '.') . "\n";
        }

        if ($pembelian->tax_percentage > 0) {
            $kenaPajak = max(0, $subtotal - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
            $out .= "Pajak " . $pembelian->tax_percentage . "%: Rp " . number_format($pajakNominal, 0, ',', '.') . "\n";
        }

        $out .= "==========================\n";
        $out .= "TOTAL: Rp " . number_format($pembelian->grand_total, 0, ',', '.') . "\n";
        $out .= "==========================\n";

        // Footer
        $out .= "\n";
        $out .= " -- Dokumen Internal --\n";

        return response($out)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Print Biaya as Rich Text for Thermer Bluetooth
     */
    public function biayaRichText($id)
    {
        $biaya = Biaya::with([
            'items',
            'user',
            'approver'
        ])->findOrFail($id);

        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";

        $out = "";
        
        // Header
        $out .= "   HIBISCUS EFSYA\n";
        $out .= " BUKTI PENGELUARAN\n";
        $out .= "--------------------------\n";

        // Info
        $out .= "No : " . $nomorInvoice . "\n";
        $out .= "Tgl: " . $biaya->tgl_transaksi->format('d/m/Y') . " " . $biaya->created_at->format('H:i') . "\n";
        $out .= "Usr: " . ($biaya->user->name ?? '-') . "\n";
        $out .= "--------------------------\n";

        // Items
        foreach ($biaya->items as $item) {
            $out .= $item->kategori ?? 'Kategori' . "\n";
            
            if ($item->deskripsi) {
                $out .= $item->deskripsi . "\n";
            }
            
            $out .= " = Rp " . number_format($item->jumlah, 0, ',', '.') . "\n";
            $out .= "\n";
        }

        $out .= "--------------------------\n";

        // Total
        $out .= "Subtotal: Rp " . number_format($biaya->items->sum('jumlah'), 0, ',', '.') . "\n";

        if ($biaya->tax_percentage > 0) {
            $subtotal = $biaya->items->sum('jumlah');
            $pajakNominal = $subtotal * ($biaya->tax_percentage / 100);
            $out .= "Pajak " . $biaya->tax_percentage . "%: Rp " . number_format($pajakNominal, 0, ',', '.') . "\n";
        }

        $out .= "==========================\n";
        $out .= "TOTAL: Rp " . number_format($biaya->grand_total, 0, ',', '.') . "\n";
        $out .= "==========================\n";

        // Footer
        $out .= "\n";
        $out .= "  -- Terima Kasih --\n";

        return response($out)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
