<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;
use Illuminate\Support\Facades\DB;

echo "\n=== Admin Users & Their Assigned Gudangs ===\n";

$admins = User::where('role', 'admin')->with('gudangs')->get();

foreach($admins as $admin) {
    echo "\nAdmin: {$admin->name} (ID: {$admin->id})\n";
    echo "  - Email: {$admin->email}\n";
    echo "  - current_gudang_id: " . ($admin->current_gudang_id ?? 'NULL') . "\n";
    echo "  - Assigned Gudangs: " . $admin->gudangs->count() . "\n";
    
    if($admin->gudangs->count() > 0) {
        foreach($admin->gudangs as $gudang) {
            echo "    • {$gudang->nama_gudang}\n";
        }
    }
}

// Also check the pivot table directly
echo "\n=== Pivot Table (admin_gudang) ===\n";
$pivot = DB::table('admin_gudang')->get();
echo "Total records: " . $pivot->count() . "\n";
foreach($pivot as $record) {
    echo "  - user_id: {$record->user_id}, gudang_id: {$record->gudang_id}\n";
}

echo "\n✓ Check complete!\n";
