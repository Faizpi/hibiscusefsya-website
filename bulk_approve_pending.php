<?php

/**
 * Bulk Approve Pending Data (sebelum Maret 2026)
 * 
 * Syarat:
 * 1. Status = Pending (bukan Cancelled)
 * 2. Tanggal transaksi sebelum 1 Maret 2026
 * 3. Syarat pembayaran BUKAN tempo (Net 7, Net 14, Net 30) — hanya Cash
 * 4. TIDAK mengurangi/menambah stok
 * 
 * Jalankan: php bulk_approve_pending.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cutoffDate = '2026-03-01';
$tempoValues = ['Net 7', 'Net 14', 'Net 30', 'Net 60', 'Net 90'];

echo "=== BULK APPROVE PENDING DATA ===\n";
echo "Cutoff date: sebelum {$cutoffDate}\n";
echo "Syarat: status=Pending, bukan tempo, TANPA perubahan stok\n";
echo str_repeat('=', 50) . "\n\n";

// -------------------------------------------------------
// 1. PENJUALAN
// -------------------------------------------------------
echo "--- PENJUALAN ---\n";
$penjualans = App\Penjualan::where('status', 'Pending')
    ->whereDate('tgl_transaksi', '<', $cutoffDate)
    ->whereNotIn('syarat_pembayaran', $tempoValues)
    ->get();

echo "Ditemukan: {$penjualans->count()} penjualan pending\n";

foreach ($penjualans as $p) {
    $p->status = 'Approved';
    $p->approver_id = 1; // super_admin
    $p->save();
    echo "  [OK] {$p->nomor} | {$p->tgl_transaksi->format('d/m/Y')} | {$p->pelanggan} | {$p->syarat_pembayaran} | Rp " . number_format($p->grand_total, 0, ',', '.') . "\n";
}

echo "Penjualan di-approve: {$penjualans->count()}\n\n";

// -------------------------------------------------------
// 2. PEMBELIAN
// -------------------------------------------------------
echo "--- PEMBELIAN ---\n";
$pembelians = App\Pembelian::where('status', 'Pending')
    ->whereDate('tgl_transaksi', '<', $cutoffDate)
    ->whereNotIn('syarat_pembayaran', $tempoValues)
    ->get();

echo "Ditemukan: {$pembelians->count()} pembelian pending\n";

foreach ($pembelians as $p) {
    $p->status = 'Approved';
    $p->approver_id = 1;
    $p->save();
    echo "  [OK] {$p->nomor} | {$p->tgl_transaksi->format('d/m/Y')} | {$p->syarat_pembayaran} | Rp " . number_format($p->grand_total, 0, ',', '.') . "\n";
}

echo "Pembelian di-approve: {$pembelians->count()}\n\n";

// -------------------------------------------------------
// 3. BIAYA (tidak punya syarat_pembayaran/jatuh tempo)
// -------------------------------------------------------
echo "--- BIAYA ---\n";
$biayas = App\Biaya::where('status', 'Pending')
    ->whereDate('tgl_transaksi', '<', $cutoffDate)
    ->get();

echo "Ditemukan: {$biayas->count()} biaya pending\n";

foreach ($biayas as $b) {
    $b->status = 'Approved';
    $b->approver_id = 1;
    $b->save();
    echo "  [OK] {$b->nomor} | {$b->tgl_transaksi->format('d/m/Y')} | Rp " . number_format($b->grand_total, 0, ',', '.') . "\n";
}

echo "Biaya di-approve: {$biayas->count()}\n\n";

// -------------------------------------------------------
// 4. KUNJUNGAN (tidak punya syarat_pembayaran/jatuh tempo)
// -------------------------------------------------------
echo "--- KUNJUNGAN ---\n";
$kunjungans = App\Kunjungan::where('status', 'Pending')
    ->whereDate('tgl_kunjungan', '<', $cutoffDate)
    ->get();

echo "Ditemukan: {$kunjungans->count()} kunjungan pending\n";

foreach ($kunjungans as $k) {
    $k->status = 'Approved';
    $k->approver_id = 1;
    $k->save();
    echo "  [OK] {$k->nomor} | {$k->tgl_kunjungan->format('d/m/Y')} | {$k->tujuan} | {$k->sales_nama}\n";
}

echo "Kunjungan di-approve: {$kunjungans->count()}\n\n";

// -------------------------------------------------------
// SUMMARY
// -------------------------------------------------------
echo str_repeat('=', 50) . "\n";
echo "SUMMARY:\n";
echo "  Penjualan: {$penjualans->count()}\n";
echo "  Pembelian: {$pembelians->count()}\n";
echo "  Biaya:     {$biayas->count()}\n";
echo "  Kunjungan: {$kunjungans->count()}\n";
$total = $penjualans->count() + $pembelians->count() + $biayas->count() + $kunjungans->count();
echo "  TOTAL:     {$total} data di-approve\n";
echo str_repeat('=', 50) . "\n";
echo "Selesai! Stok TIDAK diubah.\n";
