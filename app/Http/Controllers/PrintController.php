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

        $w = 32; // lebar karakter untuk thermal 58mm

        // Helper function untuk alignment kiri-kanan
        $line = function($left, $right = '') use ($w) {
            $spaces = max(1, $w - mb_strlen($left) - mb_strlen($right));
            return $left . str_repeat(' ', $spaces) . $right . "\n";
        };

        $out = "";
        
        // Header
        $out .= str_pad("HIBISCUS EFSYA", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("INVOICE PENJUALAN", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_repeat('-', $w) . "\n";

        // Info
        $out .= $line("Nomor", $nomorInvoice);
        $out .= $line("Tanggal", $penjualan->tgl_transaksi->format('d/m/Y') . " | " . $penjualan->created_at->format('H:i'));
        $out .= $line("Jatuh Tempo", $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-');
        $out .= $line("Pembayaran", $penjualan->syarat_pembayaran ?? '-');
        $out .= $line("Pelanggan", $penjualan->pelanggan ?? '-');
        $out .= $line("Sales", $penjualan->user->name ?? '-');
        $out .= $line("Disetujui", $penjualan->status == 'Pending' ? '-' : ($penjualan->approver->name ?? '-'));
        $out .= $line("Gudang", $penjualan->gudang->nama_gudang ?? '-');
        $out .= $line("Status", $penjualan->status_display ?? $penjualan->status);
        
        $out .= str_repeat('-', $w) . "\n";

        // Items
        foreach ($penjualan->items as $item) {
            $produkName = $item->produk->nama_produk;
            $itemCode = $item->produk->item_code ?? '-';
            
            // Nama produk (bisa multi line jika panjang)
            $out .= wordwrap($produkName . " (" . $itemCode . ")", $w, "\n", false) . "\n";
            
            $out .= $line("Qty", $item->kuantitas . " " . ($item->unit ?? 'Pcs'));
            $out .= $line("Harga", "Rp " . number_format($item->harga_satuan, 0, ',', '.'));
            
            if ($item->diskon > 0) {
                $out .= $line("Disc", $item->diskon . "%");
            }
            
            $out .= $line("Jumlah", "Rp " . number_format($item->jumlah_baris, 0, ',', '.'));
            $out .= "\n";
        }

        $out .= str_repeat('-', $w) . "\n";

        // Total
        $out .= $line("Subtotal", "Rp " . number_format($penjualan->items->sum('jumlah_baris'), 0, ',', '.'));

        if ($penjualan->diskon_akhir > 0) {
            $out .= $line("Diskon Akhir", "- Rp " . number_format($penjualan->diskon_akhir, 0, ',', '.'));
        }

        if ($penjualan->tax_percentage > 0) {
            $subtotal = $penjualan->items->sum('jumlah_baris');
            $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
            $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
            $out .= $line("Pajak ({$penjualan->tax_percentage}%)", "Rp " . number_format($pajakNominal, 0, ',', '.'));
        }

        $out .= str_repeat('=', $w) . "\n";
        $out .= $line("GRAND TOTAL", "Rp " . number_format($penjualan->grand_total, 0, ',', '.'));
        $out .= str_repeat('=', $w) . "\n";

        // Footer
        $out .= "\n";
        $out .= str_pad("marketing@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("-- Terima Kasih --", $w, " ", STR_PAD_BOTH) . "\n";

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

        $w = 32;

        $line = function($left, $right = '') use ($w) {
            $spaces = max(1, $w - mb_strlen($left) - mb_strlen($right));
            return $left . str_repeat(' ', $spaces) . $right . "\n";
        };

        $out = "";
        
        $out .= str_pad("HIBISCUS EFSYA", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("PERMINTAAN PEMBELIAN", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_repeat('-', $w) . "\n";

        $out .= $line("Nomor", $nomorInvoice);
        $out .= $line("Tanggal", $pembelian->tgl_transaksi->format('d/m/Y') . " | " . $pembelian->created_at->format('H:i'));
        $out .= $line("Jatuh Tempo", $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d/m/Y') : '-');
        $out .= $line("Pembayaran", $pembelian->syarat_pembayaran ?? '-');
        $out .= $line("Vendor", $pembelian->staf_penyetuju ?? '-');
        $out .= $line("Sales", $pembelian->user->name ?? '-');
        $out .= $line("Disetujui", $pembelian->status == 'Pending' ? '-' : ($pembelian->approver->name ?? '-'));
        $out .= $line("Gudang", $pembelian->gudang->nama_gudang ?? '-');
        $out .= $line("Status", $pembelian->status_display ?? $pembelian->status);
        
        $out .= str_repeat('-', $w) . "\n";

        $subtotal = $pembelian->items->sum('jumlah_baris');
        
        foreach ($pembelian->items as $item) {
            $produkName = $item->produk->nama_produk;
            $itemCode = $item->produk->item_code ?? '-';
            
            $out .= wordwrap($produkName . " (" . $itemCode . ")", $w, "\n", false) . "\n";
            $out .= $line("Qty", $item->kuantitas . " " . ($item->unit ?? 'Pcs'));
            $out .= $line("Harga", "Rp " . number_format($item->harga_satuan, 0, ',', '.'));
            
            if ($item->diskon > 0) {
                $out .= $line("Disc", $item->diskon . "%");
            }
            
            $out .= $line("Jumlah", "Rp " . number_format($item->jumlah_baris, 0, ',', '.'));
            $out .= "\n";
        }

        $out .= str_repeat('-', $w) . "\n";
        $out .= $line("Subtotal", "Rp " . number_format($subtotal, 0, ',', '.'));

        if ($pembelian->diskon_akhir > 0) {
            $out .= $line("Diskon Akhir", "- Rp " . number_format($pembelian->diskon_akhir, 0, ',', '.'));
        }

        if ($pembelian->tax_percentage > 0) {
            $kenaPajak = max(0, $subtotal - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
            $out .= $line("Pajak ({$pembelian->tax_percentage}%)", "Rp " . number_format($pajakNominal, 0, ',', '.'));
        }

        $out .= str_repeat('=', $w) . "\n";
        $out .= $line("GRAND TOTAL", "Rp " . number_format($pembelian->grand_total, 0, ',', '.'));
        $out .= str_repeat('=', $w) . "\n";

        $out .= "\n";
        $out .= str_pad("marketing@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("-- Dokumen Internal --", $w, " ", STR_PAD_BOTH) . "\n";

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

        $w = 32;

        $line = function($left, $right = '') use ($w) {
            $spaces = max(1, $w - mb_strlen($left) - mb_strlen($right));
            return $left . str_repeat(' ', $spaces) . $right . "\n";
        };

        $out = "";
        
        $out .= str_pad("HIBISCUS EFSYA", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("BUKTI PENGELUARAN", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_repeat('-', $w) . "\n";

        $out .= $line("Nomor", $nomorInvoice);
        $out .= $line("Tanggal", $biaya->tgl_transaksi->format('d/m/Y') . " | " . $biaya->created_at->format('H:i'));
        $out .= $line("Pembayaran", $biaya->cara_pembayaran ?? '-');
        $out .= $line("Bayar Dari", $biaya->bayar_dari ?? '-');
        $out .= $line("Penerima", $biaya->penerima ?? '-');
        $out .= $line("Sales", $biaya->user->name ?? '-');
        $out .= $line("Disetujui", $biaya->status == 'Pending' ? '-' : ($biaya->approver->name ?? '-'));
        $out .= $line("Status", $biaya->status);
        
        $out .= str_repeat('-', $w) . "\n";

        foreach ($biaya->items as $item) {
            $kategori = $item->kategori ?? 'Kategori';
            
            $out .= wordwrap($kategori, $w, "\n", false) . "\n";
            
            if ($item->deskripsi) {
                $out .= $line("Ket", $item->deskripsi);
            }
            
            $out .= $line("Jumlah", "Rp " . number_format($item->jumlah, 0, ',', '.'));
            $out .= "\n";
        }

        $out .= str_repeat('-', $w) . "\n";
        
        $out .= $line("Subtotal", "Rp " . number_format($biaya->items->sum('jumlah'), 0, ',', '.'));

        if ($biaya->tax_percentage > 0) {
            $subtotal = $biaya->items->sum('jumlah');
            $pajakNominal = $subtotal * ($biaya->tax_percentage / 100);
            $out .= $line("Pajak ({$biaya->tax_percentage}%)", "Rp " . number_format($pajakNominal, 0, ',', '.'));
        }

        $out .= str_repeat('=', $w) . "\n";
        $out .= $line("GRAND TOTAL", "Rp " . number_format($biaya->grand_total, 0, ',', '.'));
        $out .= str_repeat('=', $w) . "\n";

        $out .= "\n";
        $out .= str_pad("marketing@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("-- Terima Kasih --", $w, " ", STR_PAD_BOTH) . "\n";

        return response($out)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
