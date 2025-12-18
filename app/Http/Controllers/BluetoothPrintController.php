<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Illuminate\Http\Request;

/**
 * Controller untuk API Print Bluetooth
 * 
 * Mengembalikan data JSON yang akan dibangun menjadi struk
 * di client-side menggunakan JavaScript. Solusi ini mengatasi
 * masalah data hilang/tidak lengkap saat print via Bluetooth.
 */
class BluetoothPrintController extends Controller
{
    /**
     * Get Penjualan data as JSON for Bluetooth printing
     */
    public function penjualanJson($id)
    {
        $data = Penjualan::with(['items.produk', 'user', 'gudang', 'approver'])->findOrFail($id);

        $dateCode = $data->created_at->format('Ymd');
        $noUrut = str_pad($data->no_urut_harian, 3, '0', STR_PAD_LEFT);

        // Calculate status display - Cash selalu Lunas (langsung bayar)
        $status = $data->status;
        if ($data->syarat_pembayaran == 'Cash') {
            $status = 'Lunas';
        } elseif ($data->status == 'Lunas') {
            $status = 'Lunas';
        } elseif ($data->status == 'Approved') {
            $status = 'Belum Lunas';
        }

        // Calculate tax
        $subtotal = $data->items->sum('jumlah_baris');
        $kenaPajak = max(0, $subtotal - $data->diskon_akhir);
        $pajak = $kenaPajak * ($data->tax_percentage / 100);

        $items = $data->items->map(function ($item) {
            return [
                'nama' => $item->produk->nama_produk . ($item->produk->item_code ? ' (' . $item->produk->item_code . ')' : ''),
                'qty' => $item->kuantitas,
                'unit' => $item->unit ?? 'Pcs',
                'harga' => $item->harga_satuan,
                'diskon' => $item->diskon ?? 0,
                'jumlah' => $item->jumlah_baris
            ];
        });

        return response()->json([
            'nomor' => "INV-{$data->user_id}-{$dateCode}-{$noUrut}",
            'tanggal' => $data->tgl_transaksi->format('d/m/Y') . ' | ' . $data->created_at->format('H:i'),
            'jatuh_tempo' => $data->tgl_jatuh_tempo ? $data->tgl_jatuh_tempo->format('d/m/Y') : '-',
            'pembayaran' => $data->syarat_pembayaran ?? '-',
            'pelanggan' => $data->pelanggan,
            'sales' => optional($data->user)->name ?? '-',
            'approver' => ($data->status != 'Pending' && $data->approver) ? $data->approver->name : '-',
            'gudang' => optional($data->gudang)->nama_gudang ?? '-',
            'status' => $status,
            'items' => $items,
            'subtotal' => $subtotal,
            'diskon_akhir' => $data->diskon_akhir ?? 0,
            'tax_percentage' => $data->tax_percentage ?? 0,
            'pajak' => $pajak,
            'grand_total' => $data->grand_total,
            'invoice_url' => url('invoice/penjualan/' . $data->uuid)
        ]);
    }

    /**
     * Get Pembelian data as JSON for Bluetooth printing
     */
    public function pembelianJson($id)
    {
        $data = Pembelian::with(['items.produk', 'user', 'gudang', 'approver'])->findOrFail($id);

        $dateCode = $data->created_at->format('Ymd');
        $noUrut = str_pad($data->no_urut_harian, 3, '0', STR_PAD_LEFT);

        // Calculate tax
        $subtotal = $data->items->sum('jumlah_baris');
        $kenaPajak = max(0, $subtotal - ($data->diskon_akhir ?? 0));
        $pajak = $kenaPajak * (($data->tax_percentage ?? 0) / 100);

        $items = $data->items->map(function ($item) {
            return [
                'nama' => $item->produk->nama_produk . ($item->produk->item_code ? ' (' . $item->produk->item_code . ')' : ''),
                'qty' => $item->kuantitas,
                'unit' => $item->unit ?? 'Pcs',
                'harga' => $item->harga_satuan,
                'diskon' => $item->diskon ?? 0,
                'jumlah' => $item->jumlah_baris
            ];
        });

        return response()->json([
            'nomor' => "PR-{$data->user_id}-{$dateCode}-{$noUrut}",
            'tanggal' => $data->tgl_transaksi->format('d/m/Y') . ' | ' . $data->created_at->format('H:i'),
            'jatuh_tempo' => $data->tgl_jatuh_tempo ? $data->tgl_jatuh_tempo->format('d/m/Y') : '-',
            'pembayaran' => $data->syarat_pembayaran ?? '-',
            'vendor' => $data->staf_penyetuju ?? '-',
            'sales' => optional($data->user)->name ?? '-',
            'approver' => ($data->status != 'Pending' && $data->approver) ? $data->approver->name : '-',
            'gudang' => optional($data->gudang)->nama_gudang ?? '-',
            'status' => $data->status_display ?? $data->status,
            'items' => $items,
            'subtotal' => $subtotal,
            'diskon_akhir' => $data->diskon_akhir ?? 0,
            'tax_percentage' => $data->tax_percentage ?? 0,
            'pajak' => $pajak,
            'grand_total' => $data->grand_total,
            'invoice_url' => url('invoice/pembelian/' . $data->uuid)
        ]);
    }

    /**
     * Get Biaya data as JSON for Bluetooth printing
     */
    public function biayaJson($id)
    {
        $data = Biaya::with(['items', 'user', 'approver'])->findOrFail($id);

        $dateCode = $data->created_at->format('Ymd');
        $noUrut = str_pad($data->no_urut_harian, 3, '0', STR_PAD_LEFT);

        // Calculate tax
        $subtotal = $data->items->sum('jumlah');
        $pajak = $subtotal * (($data->tax_percentage ?? 0) / 100);

        $items = $data->items->map(function ($item) {
            return [
                'kategori' => $item->kategori,
                'deskripsi' => $item->deskripsi ?? '',
                'jumlah' => $item->jumlah
            ];
        });

        return response()->json([
            'nomor' => "EXP-{$data->user_id}-{$dateCode}-{$noUrut}",
            'tanggal' => $data->tgl_transaksi->format('d/m/Y') . ' | ' . $data->created_at->format('H:i'),
            'jenis_biaya' => $data->jenis_biaya ?? 'keluar',
            'cara_pembayaran' => $data->cara_pembayaran ?? '-',
            'bayar_dari' => $data->bayar_dari ?? '-',
            'penerima' => $data->penerima ?? '-',
            'sales' => optional($data->user)->name ?? '-',
            'approver' => ($data->status != 'Pending' && $data->approver) ? $data->approver->name : '-',
            'status' => $data->status,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_percentage' => $data->tax_percentage ?? 0,
            'pajak' => $pajak,
            'grand_total' => $data->grand_total,
            'invoice_url' => url('invoice/biaya/' . $data->uuid)
        ]);
    }
}
