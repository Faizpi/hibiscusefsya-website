<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    // ========================================================================
    // KONFIGURASI GLOBAL (32 KARAKTER)
    // ========================================================================
    const W = 32;

    // Command Printer
    const ESC_RESET = "\x1B\x40";
    const ESC_BOLD_ON = "\x1B\x45\x01";
    const ESC_BOLD_OFF = "\x1B\x45\x00";
    const ESC_ALIGN_LEFT = "\x1B\x61\x00";
    const ESC_ALIGN_CENTER = "\x1B\x61\x01";

    const DASH = "--------------------------------\r\n";
    const DOUBLE = "================================\r\n";

    private function rp($val)
    {
        return "Rp " . number_format($val, 0, ',', '.');
    }

    // ========================================================================
    // HELPER FORMATTING (LOGIKA MATEMATIKA STRUK)
    // ========================================================================

    /**
     * Format Baris Info (Label : Value)
     * Contoh: "Nomor     : INV-001"
     */
    private function fmtRow($label, $value)
    {
        $val = trim((string) ($value ?? '-'));
        if ($val === '')
            $val = '-';

        $colLabel = 12;
        $colVal = 18; // 32 - 12 - 2 (": ")

        // Wordwrap manual agar value panjang turun baris rapi
        $lines = explode("\n", wordwrap($val, $colVal, "\n", true));
        $out = "";

        foreach ($lines as $i => $line) {
            if ($i === 0) {
                // Baris 1: Label lengkap
                $out .= str_pad(substr($label, 0, $colLabel), $colLabel, " ") . ": " . $line . "\r\n";
            } else {
                // Baris 2+: Indentasi
                $out .= str_repeat(" ", $colLabel + 2) . $line . "\r\n";
            }
        }
        return $out;
    }

    /**
     * Format Kiri ... Kanan (Align Justify)
     * PERBAIKAN: Tidak agresif memotong label kiri ("GRAND")
     */
    private function fmtJustify($left, $right)
    {
        $lenLeft = strlen($left);
        $lenRight = strlen($right);

        // Hitung Sisa Spasi
        $spaces = self::W - $lenLeft - $lenRight;

        // Jika teks tabrakan (negatif), kita potong label kiri,
        // TAPI kita sisakan minimal 1 spasi agar tidak nempel.
        if ($spaces < 1) {
            $spaces = 1;
            $maxLeft = self::W - $lenRight - 1;
            // Potong label kiri hanya jika terpaksa
            if ($maxLeft > 0) {
                $left = substr($left, 0, $maxLeft);
            }
        }

        return $left . str_repeat(" ", $spaces) . $right . "\r\n";
    }

    // ========================================================================
    // BLOK DIV (MODULAR SYSTEM)
    // ========================================================================

    // DIV 1: HEADER
    private function divHeader($title)
    {
        $out = self::ESC_RESET; // Reset Total
        $out .= self::ESC_ALIGN_CENTER;
        $out .= self::ESC_BOLD_ON . "HIBISCUS EFSYA" . self::ESC_BOLD_OFF . "\r\n";
        $out .= strtoupper($title) . "\r\n";
        $out .= self::ESC_ALIGN_LEFT; // Kembalikan ke kiri
        $out .= "\r\n";
        return $out;
    }

    // DIV 2: INFO
    private function divInfo($dataMap)
    {
        $out = "";
        foreach ($dataMap as $k => $v)
            $out .= $this->fmtRow($k, $v);
        return $out;
    }

    // DIV 3: ITEMS (STERILISASI TOTAL)
    private function divItems($items)
    {
        $out = self::DASH;

        foreach ($items as $item) {
            // -----------------------------------------------------------
            // LANGKAH 1: CETAK NAMA PRODUK
            // -----------------------------------------------------------
            // Pastikan Bold Mati dulu (Safety)
            $out .= self::ESC_BOLD_OFF;

            $nama = $item->produk->nama_produk . ($item->produk->item_code ? " (" . $item->produk->item_code . ")" : "");

            // Nyalakan Bold -> Cetak Nama -> Matikan Bold -> Enter
            $out .= self::ESC_BOLD_ON . $nama . self::ESC_BOLD_OFF . "\r\n";

            // -----------------------------------------------------------
            // LANGKAH 2: CETAK RINCIAN (STERIL)
            // -----------------------------------------------------------
            // Kita cetak baris per baris dengan format yang PASTI.
            // Gunakan Text Biasa untuk Qty agar tidak kena efek justify aneh-aneh.

            // Baris Qty
            $out .= self::ESC_BOLD_OFF; // Safety lagi
            $qtyStr = "Qty " . $item->kuantitas . " " . ($item->unit ?? 'Pcs');
            // Trik: Gunakan fmtJustify dengan kanan kosong agar Qty tetap di kiri
            $out .= $this->fmtJustify($qtyStr, "");

            // Baris Harga
            $out .= $this->fmtJustify("Harga", $this->rp($item->harga_satuan));

            // Baris Diskon (Jika ada)
            if ($item->diskon > 0) {
                $out .= $this->fmtJustify("Disc", ($item->diskon + 0) . "%");
            }

            // Baris Jumlah
            $out .= $this->fmtJustify("Jumlah", $this->rp($item->jumlah_baris));

            // Opsional: Jarak antar produk (uncomment jika perlu)
            // $out .= "\r\n"; 
        }
        return $out;
    }

    // DIV 4: SUBTOTAL
    private function divSubtotal($subtotal, $disc, $taxPct)
    {
        $out = self::DASH;
        $out .= $this->fmtJustify("Subtotal", $this->rp($subtotal));

        if ($disc > 0) {
            $out .= $this->fmtJustify("Diskon Akhir", "- " . $this->rp($disc));
        }

        if ($taxPct > 0) {
            $tax = max(0, $subtotal - $disc) * ($taxPct / 100);
            $out .= $this->fmtJustify("Pajak (" . ($taxPct + 0) . "%)", $this->rp($tax));
        }
        return $out;
    }

    // DIV 5: GRAND TOTAL
    private function divGrandTotal($val)
    {
        $out = self::DASH;
        // Kita beri Bold pada Label dan Value secara terpisah untuk keamanan
        $out .= self::ESC_BOLD_ON;
        $out .= $this->fmtJustify("GRAND TOTAL", $this->rp($val));
        $out .= self::ESC_BOLD_OFF;
        return $out;
    }

    // DIV 6: SEPARATOR (ANTI-NYATU)
    private function divSeparator()
    {
        // Enter -> Garis Double -> Enter
        return "\r\n" . self::DOUBLE . "\r\n";
    }

    // DIV 7: FOOTER
    private function divFooter()
    {
        $out = self::ESC_ALIGN_CENTER;
        $out .= "marketing@hibiscusefsya.com\r\n";
        $out .= "-- Terima Kasih --\r\n";
        $out .= "\r\n\r\n\r\n\r\n\r\n"; // Feed Extra
        return $out;
    }

    // ========================================================================
    // CONTROLLER LOGIC
    // ========================================================================
    public function penjualanRichText($id)
    {
        $data = Penjualan::with(['items.produk', 'user', 'gudang'])->findOrFail($id);
        $dateCode = $data->created_at->format('Ymd');
        $noUrut = str_pad($data->no_urut_harian, 3, '0', STR_PAD_LEFT);

        // Status
        $st = $data->status;
        if ($data->status == 'Lunas')
            $st = 'Lunas';
        elseif ($data->status == 'Approved')
            $st = 'Belum Lunas';

        // --- RAKIT DIV ---
        $p = "";

        // 1. Header
        $p .= $this->divHeader("INVOICE PENJUALAN");

        // 2. Info
        $p .= $this->divInfo([
            "Nomor" => "INV-1-{$dateCode}-{$noUrut}",
            "Tanggal" => $data->tgl_transaksi->format('d/m/Y') . " | " . $data->created_at->format('H:i'),
            "Jatuh Tempo" => $data->tgl_jatuh_tempo ? $data->tgl_jatuh_tempo->format('d/m/Y') : '-',
            "Pembayaran" => $data->syarat_pembayaran,
            "Pelanggan" => $data->pelanggan,
            "Ref" => $data->no_referensi,
            "Sales" => optional($data->user)->name,
            "Disetujui" => ($data->status != 'Pending' && $data->approver) ? $data->approver->name : '-',
            "Gudang" => optional($data->gudang)->nama_gudang,
            "Status" => $st ?: '-'
        ]);

        // 3. Items
        $p .= $this->divItems($data->items);

        // 4. Subtotal
        $p .= $this->divSubtotal($data->items->sum('jumlah_baris'), $data->diskon_akhir, $data->tax_percentage);

        // 5. Grand Total
        $p .= $this->divGrandTotal($data->grand_total);

        // 6. Separator
        $p .= $this->divSeparator();

        // 7. Footer
        $p .= $this->divFooter();

        return response($p)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    public function pembelianRichText($id)
    {
        $data = Pembelian::with(['items.produk', 'user', 'gudang'])->findOrFail($id);
        $dateCode = $data->created_at->format('Ymd');
        $noUrut = str_pad($data->no_urut_harian, 3, '0', STR_PAD_LEFT);

        $p = $this->divHeader("PERMINTAAN PEMBELIAN");
        $p .= $this->divInfo([
            "Nomor" => "PR-1-{$dateCode}-{$noUrut}",
            "Tanggal" => $data->tgl_transaksi->format('d/m/Y'),
            "Vendor" => $data->staf_penyetuju,
            "Sales" => optional($data->user)->name,
            "Gudang" => optional($data->gudang)->nama_gudang,
            "Status" => $data->status
        ]);

        $p .= $this->divItems($data->items);
        $p .= self::DASH;
        $p .= $this->divGrandTotal($data->grand_total);
        $p .= $this->divSeparator();
        $p .= $this->divFooter();

        return response($p)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    public function biayaRichText($id)
    {
        $data = Biaya::with(['items', 'user'])->findOrFail($id);
        $dateCode = $data->created_at->format('Ymd');
        $noUrut = str_pad($data->no_urut_harian, 3, '0', STR_PAD_LEFT);

        $p = $this->divHeader("BUKTI PENGELUARAN");
        $p .= $this->divInfo([
            "Nomor" => "EXP-1-{$dateCode}-{$noUrut}",
            "Tanggal" => $data->tgl_transaksi->format('d/m/Y'),
            "Penerima" => $data->penerima,
            "Sales" => optional($data->user)->name,
            "Status" => $data->status
        ]);

        $p .= self::DASH;
        foreach ($data->items as $item) {
            $p .= self::ESC_BOLD_ON . $item->kategori . self::ESC_BOLD_OFF . "\r\n";
            if ($item->deskripsi)
                $p .= "Ket: " . $item->deskripsi . "\r\n";
            $p .= $this->fmtJustify("Jumlah", $this->rp($item->jumlah));
        }

        $p .= self::DASH;
        $p .= $this->divGrandTotal($data->grand_total);
        $p .= $this->divSeparator();
        $p .= $this->divFooter();

        return response($p)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}