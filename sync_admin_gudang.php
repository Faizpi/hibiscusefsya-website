<?php

/**
 * Script to sync gudang_id to admin_gudang pivot table for existing admin users
 * Run once after deploying the fix
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;

$admins = User::where('role', 'admin')->whereNotNull('gudang_id')->get();

echo "Found " . $admins->count() . " admin(s) with gudang_id\n\n";

foreach ($admins as $admin) {
    $gudangId = $admin->gudang_id;
    $currentGudangs = $admin->gudangs()->pluck('gudang_id')->toArray();

    echo "Admin: {$admin->name} (ID: {$admin->id})\n";
    echo "  - gudang_id: {$gudangId}\n";
    echo "  - Current pivot gudangs: " . implode(', ', $currentGudangs) . "\n";

    if (!in_array($gudangId, $currentGudangs)) {
        $admin->gudangs()->attach($gudangId);
        echo "  - SYNCED gudang_id {$gudangId} to pivot table\n";
    } else {
        echo "  - Already in sync\n";
    }
    echo "\n";
}

echo "Done!\n";
