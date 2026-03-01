<?php

/**
 * Backfill gudang_id for existing biayas records
 * Based on the creator (user_id) gudang assignment
 *
 * Usage: php backfill_gudang_biaya.php
 * Run from project root after migration
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Backfill gudang_id untuk Biayas ===\n\n";

// Get all biayas without gudang_id
$biayas = DB::table('biayas')->whereNull('gudang_id')->get();
echo "Total biaya tanpa gudang_id: {$biayas->count()}\n";

$updated = 0;
$skipped = 0;

foreach ($biayas as $biaya) {
    $user = DB::table('users')->where('id', $biaya->user_id)->first();

    if (!$user) {
        echo "  SKIP biaya #{$biaya->id}: user_id={$biaya->user_id} tidak ditemukan\n";
        $skipped++;
        continue;
    }

    // Prioritas: current_gudang_id > gudang_id > first admin_gudang
    $gudangId = $user->current_gudang_id ?? $user->gudang_id;

    if (!$gudangId && $user->role === 'admin') {
        // Try admin_gudang pivot
        $adminGudang = DB::table('admin_gudang')->where('user_id', $user->id)->first();
        if ($adminGudang) {
            $gudangId = $adminGudang->gudang_id;
        }
    }

    if ($gudangId) {
        DB::table('biayas')->where('id', $biaya->id)->update(['gudang_id' => $gudangId]);
        $updated++;
    } else {
        echo "  SKIP biaya #{$biaya->id}: user '{$user->name}' (role={$user->role}) tidak punya gudang\n";
        $skipped++;
    }
}

echo "\nHasil:\n";
echo "  Updated: {$updated}\n";
echo "  Skipped: {$skipped}\n";
echo "\nSelesai!\n";
