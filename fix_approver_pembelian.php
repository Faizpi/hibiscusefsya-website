<?php

/**
 * Script untuk fix approver_id pada transaksi pembelian yang masih NULL
 * Jalankan: php fix_approver_pembelian.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Pembelian;
use App\User;
use Illuminate\Support\Facades\DB;

echo "=== FIX APPROVER PEMBELIAN ===\n\n";

// Ambil semua pembelian dengan approver_id NULL dan status Pending
$pembelians = Pembelian::whereNull('approver_id')
    ->where('status', 'Pending')
    ->with(['user', 'gudang'])
    ->get();

echo "Ditemukan " . $pembelians->count() . " transaksi pembelian pending tanpa approver.\n\n";

if ($pembelians->isEmpty()) {
    echo "Tidak ada data yang perlu diperbaiki.\n";
    exit(0);
}

$updated = 0;
$failed = 0;

foreach ($pembelians as $pembelian) {
    echo "Processing Pembelian #{$pembelian->id} - {$pembelian->nomor}\n";
    echo "  Pembuat: {$pembelian->user->name} ({$pembelian->user->role})\n";
    echo "  Gudang: {$pembelian->gudang->nama_gudang}\n";
    
    $approverId = null;
    
    // Tentukan approver berdasarkan role pembuat dan gudang
    if ($pembelian->user->role == 'user') {
        // Sales: cari admin yang handle gudang ini
        $adminGudang = User::where('role', 'admin')
            ->where('current_gudang_id', $pembelian->gudang_id)
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
    } elseif ($pembelian->user->role == 'admin') {
        // Admin: approver ke super admin
        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $approverId = $superAdmin->id;
            echo "  → Approver: {$superAdmin->name} (Super Admin)\n";
        }
    }
    
    if ($approverId) {
        $pembelian->approver_id = $approverId;
        $pembelian->save();
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
