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
        $out .= "     HIBISCUS EFSYA\n";
        $out .= "  INVOICE PENJUALAN\n";
        $out .= "----------------------------\n";

        // Info
        $out .= "Nomor  : " . $nomorInvoice . "\n";
        $out .= "Tgl    : " . $penjualan->tgl_transaksi->format('d/m/Y') . "\n";
        $out .= "Jam    : " . $penjualan->created_at->format('H:i') . "\n";
        $out .= "Tempo  : " . ($penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-') . "\n";
        $out .= "Bayar  : " . ($penjualan->syarat_pembayaran ?? '-') . "\n";
        $out .= "Cust   : " . ($penjualan->pelanggan ?? '-') . "\n";
        $out .= "Sales  : " . ($penjualan->user->name ?? '-') . "\n";
        $out .= "Approve: " . ($penjualan->status == 'Pending' ? '-' : ($penjualan->approver->name ?? '-')) . "\n";
        $out .= "Gudang : " . ($penjualan->gudang->nama_gudang ?? '-') . "\n";
        $out .= "Status : " . ($penjualan->status_display ?? $penjualan->status) . "\n";
        $out .= "----------------------------\n";

        // Items
        foreach ($penjualan->items as $item) {
            $out .= $item->produk->nama_produk . "\n";
            $out .= "(" . ($item->produk->item_code ?? '-') . ")\n";
            $out .= "  Qty  : " . $item->kuantitas . " " . ($item->unit ?? 'Pcs') . "\n";
            $out .= "  @Rp  : " . number_format($item->harga_satuan, 0, ',', '.') . "\n";
            
            if ($item->diskon > 0) {
                $out .= "  Disc : " . $item->diskon . "%\n";
            }
            
            $out .= "  Jml  : Rp " . number_format($item->jumlah_baris, 0, ',', '.') . "\n";
            $out .= "\n";
        }

        $out .= "----------------------------\n";

        // Total
        $out .= "Subtotal\n";
        $out .= "         Rp " . number_format($penjualan->items->sum('jumlah_baris'), 0, ',', '.') . "\n";

        if ($penjualan->diskon_akhir > 0) {
            $out .= "Diskon Akhir\n";
            $out .= "      -Rp " . number_format($penjualan->diskon_akhir, 0, ',', '.') . "\n";
        }

        if ($penjualan->tax_percentage > 0) {
            $subtotal = $penjualan->items->sum('jumlah_baris');
            $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
            $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
            $out .= "Pajak " . $penjualan->tax_percentage . "%\n";
            $out .= "         Rp " . number_format($pajakNominal, 0, ',', '.') . "\n";
        }

        $out .= "============================\n";
        $out .= "GRAND TOTAL\n";
        $out .= "         Rp " . number_format($penjualan->grand_total, 0, ',', '.') . "\n";
        $out .= "============================\n";

        // Footer
        $out .= "\n";
        $out .= "marketing@hibiscusefsya.com\n";
        $out .= "    -- Terima Kasih --\n";

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
        $out .= "     HIBISCUS EFSYA\n";
        $out .= " PERMINTAAN PEMBELIAN\n";
        $out .= "----------------------------\n";

        // Info
        $out .= "Nomor  : " . $nomorInvoice . "\n";
        $out .= "Tgl    : " . $pembelian->tgl_transaksi->format('d/m/Y') . "\n";
        $out .= "Jam    : " . $pembelian->created_at->format('H:i') . "\n";
        $out .= "Tempo  : " . ($pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d/m/Y') : '-') . "\n";
        $out .= "Bayar  : " . ($pembelian->syarat_pembayaran ?? '-') . "\n";
        $out .= "Vendor : " . ($pembelian->staf_penyetuju ?? '-') . "\n";
        $out .= "Sales  : " . ($pembelian->user->name ?? '-') . "\n";
        $out .= "Approve: " . ($pembelian->status == 'Pending' ? '-' : ($pembelian->approver->name ?? '-')) . "\n";
        $out .= "Gudang : " . ($pembelian->gudang->nama_gudang ?? '-') . "\n";
        $out .= "Status : " . ($pembelian->status_display ?? $pembelian->status) . "\n";
        $out .= "----------------------------\n";

        $subtotal = $pembelian->items->sum('jumlah_baris');
        
        // Items
        foreach ($pembelian->items as $item) {
            $out .= $item->produk->nama_produk . "\n";
            $out .= "(" . ($item->produk->item_code ?? '-') . ")\n";
            $out .= "  Qty  : " . $item->kuantitas . " " . ($item->unit ?? 'Pcs') . "\n";
            $out .= "  @Rp  : " . number_format($item->harga_satuan, 0, ',', '.') . "\n";
            
            if ($item->diskon > 0) {
                $out .= "  Disc : " . $item->diskon . "%\n";
            }
            
            $out .= "  Jml  : Rp " . number_format($item->jumlah_baris, 0, ',', '.') . "\n";
            $out .= "\n";
        }

        $out .= "----------------------------\n";

        // Total
        $out .= "Subtotal\n";
        $out .= "         Rp " . number_format($subtotal, 0, ',', '.') . "\n";

        if ($pembelian->diskon_akhir > 0) {
            $out .= "Diskon Akhir\n";
            $out .= "      -Rp " . number_format($pembelian->diskon_akhir, 0, ',', '.') . "\n";
        }

        if ($pembelian->tax_percentage > 0) {
            $kenaPajak = max(0, $subtotal - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
            $out .= "Pajak " . $pembelian->tax_percentage . "%\n";
            $out .= "         Rp " . number_format($pajakNominal, 0, ',', '.') . "\n";
        }

        $out .= "============================\n";
        $out .= "GRAND TOTAL\n";
        $out .= "         Rp " . number_format($pembelian->grand_total, 0, ',', '.') . "\n";
        $out .= "============================\n";

        // Footer
        $out .= "\n";
        $out .= "marketing@hibiscusefsya.com\n";
        $out .= "  -- Dokumen Internal --\n";

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
        $out .= "     HIBISCUS EFSYA\n";
        $out .= "   BUKTI PENGELUARAN\n";
        $out .= "----------------------------\n";

        // Info
        $out .= "Nomor  : " . $nomorInvoice . "\n";
        $out .= "Tgl    : " . $biaya->tgl_transaksi->format('d/m/Y') . "\n";
        $out .= "Jam    : " . $biaya->created_at->format('H:i') . "\n";
        $out .= "Bayar  : " . ($biaya->cara_pembayaran ?? '-') . "\n";
        $out .= "Dari   : " . ($biaya->bayar_dari ?? '-') . "\n";
        $out .= "Kepada : " . ($biaya->penerima ?? '-') . "\n";
        $out .= "Sales  : " . ($biaya->user->name ?? '-') . "\n";
        $out .= "Approve: " . ($biaya->status == 'Pending' ? '-' : ($biaya->approver->name ?? '-')) . "\n";
        $out .= "Status : " . $biaya->status . "\n";
        $out .= "----------------------------\n";

        // Items
        foreach ($biaya->items as $item) {
            $out .= $item->kategori ?? 'Kategori' . "\n";
            
            if ($item->deskripsi) {
                $out .= "  Ket  : " . $item->deskripsi . "\n";
            }
            
            $out .= "  Jml  : Rp " . number_format($item->jumlah, 0, ',', '.') . "\n";
            $out .= "\n";
        }

        $out .= "----------------------------\n";

        // Total
        $out .= "Subtotal\n";
        $out .= "         Rp " . number_format($biaya->items->sum('jumlah'), 0, ',', '.') . "\n";

        if ($biaya->tax_percentage > 0) {
            $subtotal = $biaya->items->sum('jumlah');
            $pajakNominal = $subtotal * ($biaya->tax_percentage / 100);
            $out .= "Pajak " . $biaya->tax_percentage . "%\n";
            $out .= "         Rp " . number_format($pajakNominal, 0, ',', '.') . "\n";
        }

        $out .= "============================\n";
        $out .= "GRAND TOTAL\n";
        $out .= "         Rp " . number_format($biaya->grand_total, 0, ',', '.') . "\n";
        $out .= "============================\n";

        // Footer
        $out .= "\n";
        $out .= "marketing@hibiscusefsya.com\n";
        $out .= "    -- Terima Kasih --\n";

        return response($out)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
