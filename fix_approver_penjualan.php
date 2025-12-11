<?php

/**
 * Script untuk fix approver_id pada transaksi penjualan yang masih NULL
 * Jalankan: php fix_approver_penjualan.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Penjualan;
use App\User;
use Illuminate\Support\Facades\DB;

echo "=== FIX APPROVER PENJUALAN ===\n\n";

// Ambil semua penjualan dengan approver_id NULL dan status Pending
$penjualans = Penjualan::whereNull('approver_id')
    ->where('status', 'Pending')
    ->with(['user', 'gudang'])
    ->get();

echo "Ditemukan " . $penjualans->count() . " transaksi penjualan pending tanpa approver.\n\n";

if ($penjualans->isEmpty()) {
    echo "Tidak ada data yang perlu diperbaiki.\n";
    exit(0);
}

$updated = 0;
$failed = 0;

foreach ($penjualans as $penjualan) {
    echo "Processing Penjualan #{$penjualan->id} - {$penjualan->nomor}\n";
    echo "  Pembuat: {$penjualan->user->name} ({$penjualan->user->role})\n";
    echo "  Gudang: {$penjualan->gudang->nama_gudang}\n";

    $approverId = null;

    // Tentukan approver berdasarkan role pembuat dan gudang
    if ($penjualan->user->role == 'user') {
        // Sales: cari admin yang handle gudang ini
        $adminGudang = User::where('role', 'admin')
            ->where('current_gudang_id', $penjualan->gudang_id)
            ->first();

        if ($adminGudang) {
            $approverId = $adminGudang->id;
            echo "  → Approver: {$adminGudang->name} (Admin Gudang)\n";
        } else {
            // Tidak ada admin gudang, ke super admin
            $superAdmin = User::where('role', 'super_admin')->first();
            if ($superAdmin) {
                $approverId = $superAdmin->id;
                echo "  → Approver: {$superAdmin->name} (Super Admin - fallback)\n";
            }
        }
    } elseif ($penjualan->user->role == 'admin') {
        // Admin: approver ke super admin
        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $approverId = $superAdmin->id;
            echo "  → Approver: {$superAdmin->name} (Super Admin)\n";
        }
    }

    if ($approverId) {
        $penjualan->approver_id = $approverId;
        $penjualan->save();
        echo "  ✓ Updated!\n";
        $updated++;
    } else {
        echo "  ✗ Failed: Tidak bisa menentukan approver\n";
        $failed++;
    }

    echo "\n";
}

echo "=== SELESAI ===\n";
echo "Berhasil update: {$updated} transaksi\n";
echo "Gagal: {$failed} transaksi\n";
