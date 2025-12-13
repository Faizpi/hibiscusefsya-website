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
        $out .= str_pad("marketing@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("INVOICE PENJUALAN", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_repeat('-', $w) . "\n";

        // Info
        $out .= $line("Nomor", $nomorInvoice);
        $out .= $line("Tanggal", $penjualan->tgl_transaksi->format('d/m/Y') . " | " . $penjualan->created_at->format('H:i'));
        $out .= $line("Jatuh Tempo", $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-');
        $out .= $line("Pembayaran", $penjualan->metode_pembayaran ?? 'Net 7');
        $out .= $line("Pelanggan", $penjualan->pelanggan ?? '-');
        $out .= $line("Sales", $penjualan->user->name ?? '-');
        $out .= $line("Gudang", $penjualan->gudang->nama_gudang ?? '-');
        $out .= $line("Status", $penjualan->status);
        
        if ($penjualan->approver_id && $penjualan->approver) {
            $out .= $line("Disetujui", $penjualan->approver->name);
        }
        
        $out .= str_repeat('-', $w) . "\n";

        // Items
        foreach ($penjualan->items as $item) {
            $produkName = $item->produk->nama_produk;
            $itemCode = $item->produk->item_code ?? '-';
            
            // Nama produk (bisa multi line jika panjang)
            $out .= wordwrap($produkName . " (" . $itemCode . ")", $w, "\n", false) . "\n";
            
            $out .= $line("Qty", $item->kuantitas . " Pcs");
            $out .= $line("Harga", "Rp " . number_format($item->harga_satuan, 0, ',', '.'));
            
            if ($item->diskon_per_item > 0) {
                $out .= $line("Diskon", "- Rp " . number_format($item->diskon_per_item, 0, ',', '.'));
            }
            
            $out .= $line("Jumlah", "Rp " . number_format($item->jumlah_baris, 0, ',', '.'));
            $out .= "\n";
        }

        $out .= str_repeat('-', $w) . "\n";

        // Total
        $out .= $line("Subtotal", "Rp " . number_format($penjualan->items->sum('jumlah_baris'), 0, ',', '.'));

        if ($penjualan->diskon_akhir > 0) {
            $out .= $line("Diskon", "- Rp " . number_format($penjualan->diskon_akhir, 0, ',', '.'));
        }

        if ($penjualan->tax_percentage > 0) {
            $kenaPajak = max(0, $penjualan->items->sum('jumlah_baris') - $penjualan->diskon_akhir);
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
        $nomorPembelian = "REQ-{$pembelian->user_id}-{$dateCode}-{$noUrut}";

        $w = 32;

        $line = function($left, $right = '') use ($w) {
            $spaces = max(1, $w - mb_strlen($left) - mb_strlen($right));
            return $left . str_repeat(' ', $spaces) . $right . "\n";
        };

        $out = "";
        
        $out .= str_pad("HIBISCUS EFSYA", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("marketing@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("PERMINTAAN PEMBELIAN", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_repeat('-', $w) . "\n";

        $out .= $line("Nomor", $nomorPembelian);
        $out .= $line("Tanggal", $pembelian->tgl_transaksi->format('d/m/Y') . " | " . $pembelian->created_at->format('H:i'));
        $out .= $line("Jatuh Tempo", $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d/m/Y') : '-');
        $out .= $line("Pembayaran", $pembelian->metode_pembayaran ?? 'Net 30');
        $out .= $line("Pemasok", $pembelian->supplier ?? '-');
        $out .= $line("Diminta", $pembelian->user->name ?? '-');
        $out .= $line("Gudang", $pembelian->gudang->nama_gudang ?? '-');
        $out .= $line("Status", $pembelian->status);
        
        if ($pembelian->approver_id && $pembelian->approver) {
            $out .= $line("Disetujui", $pembelian->approver->name);
        }
        
        $out .= str_repeat('-', $w) . "\n";

        foreach ($pembelian->items as $item) {
            $produkName = $item->produk->nama_produk;
            $itemCode = $item->produk->item_code ?? '-';
            
            $out .= wordwrap($produkName . " (" . $itemCode . ")", $w, "\n", false) . "\n";
            $out .= $line("Qty", $item->kuantitas . " Pcs");
            $out .= $line("Harga", "Rp " . number_format($item->harga_satuan, 0, ',', '.'));
            $out .= $line("Jumlah", "Rp " . number_format($item->jumlah_baris, 0, ',', '.'));
            $out .= "\n";
        }

        $out .= str_repeat('-', $w) . "\n";
        $out .= $line("Subtotal", "Rp " . number_format($pembelian->items->sum('jumlah_baris'), 0, ',', '.'));

        if ($pembelian->diskon_akhir > 0) {
            $out .= $line("Diskon", "- Rp " . number_format($pembelian->diskon_akhir, 0, ',', '.'));
        }

        if ($pembelian->tax_percentage > 0) {
            $kenaPajak = max(0, $pembelian->items->sum('jumlah_baris') - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
            $out .= $line("Pajak ({$pembelian->tax_percentage}%)", "Rp " . number_format($pajakNominal, 0, ',', '.'));
        }

        $out .= str_repeat('=', $w) . "\n";
        $out .= $line("GRAND TOTAL", "Rp " . number_format($pembelian->grand_total, 0, ',', '.'));
        $out .= str_repeat('=', $w) . "\n";

        $out .= "\n";
        $out .= str_pad("procurement@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
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
        $nomorBiaya = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";

        $w = 32;

        $line = function($left, $right = '') use ($w) {
            $spaces = max(1, $w - mb_strlen($left) - mb_strlen($right));
            return $left . str_repeat(' ', $spaces) . $right . "\n";
        };

        $out = "";
        
        $out .= str_pad("HIBISCUS EFSYA", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("marketing@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("BUKTI PENGELUARAN", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_repeat('-', $w) . "\n";

        $out .= $line("Nomor", $nomorBiaya);
        $out .= $line("Tanggal", $biaya->tgl_transaksi->format('d/m/Y') . " | " . $biaya->created_at->format('H:i'));
        $out .= $line("Jatuh Tempo", $biaya->tgl_jatuh_tempo ? $biaya->tgl_jatuh_tempo->format('d/m/Y') : '-');
        $out .= $line("Pembayaran", $biaya->metode_pembayaran ?? 'Cash');
        $out .= $line("Kontak", $biaya->nama_pemasok ?? '-');
        $out .= $line("Diinput", $biaya->user->name ?? '-');
        $out .= $line("Status", $biaya->status);
        
        if ($biaya->approver_id && $biaya->approver) {
            $out .= $line("Disetujui", $biaya->approver->name);
        }
        
        $out .= str_repeat('-', $w) . "\n";

        foreach ($biaya->items as $item) {
            $kategori = $item->kategori ?? 'Kategori';
            
            $out .= wordwrap($kategori, $w, "\n", false) . "\n";
            $out .= $line("Deskripsi", $item->deskripsi ?? '-');
            $out .= $line("Jumlah", "Rp " . number_format($item->jumlah, 0, ',', '.'));
            $out .= "\n";
        }

        $out .= str_repeat('-', $w) . "\n";
        $out .= str_repeat('=', $w) . "\n";
        $out .= $line("TOTAL BIAYA", "Rp " . number_format($biaya->items->sum('jumlah'), 0, ',', '.'));
        $out .= str_repeat('=', $w) . "\n";

        $out .= "\n";
        $out .= str_pad("accounting@hibiscusefsya.com", $w, " ", STR_PAD_BOTH) . "\n";
        $out .= str_pad("-- Dokumen Internal --", $w, " ", STR_PAD_BOTH) . "\n";

        return response($out)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
