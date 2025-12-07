<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;

echo "\n=== Checking for Invalid Users ===\n";

// Check users with null or empty name
$invalidUsers = User::whereNull('name')->orWhere('name', '')->orWhere('name', ' ')->get();
echo "\nUsers with null/empty/space name: " . $invalidUsers->count() . "\n";
foreach($invalidUsers as $user) {
    echo "  - ID: {$user->id}, Name: '[{$user->name}]', Email: {$user->email}, Role: {$user->role}\n";
}

// Delete invalid users
if($invalidUsers->count() > 0) {
    echo "\n⚠ Deleting " . $invalidUsers->count() . " invalid users...\n";
    foreach($invalidUsers as $user) {
        $user->delete();
        echo "  ✓ Deleted ID: {$user->id}\n";
    }
}

// Show final state
echo "\n=== All Users After Cleanup ===\n";
$users = User::select('id', 'name', 'email', 'role')->orderBy('id')->get();
echo str_pad('ID', 5) . ' | ' . str_pad('Name', 20) . ' | ' . str_pad('Email', 30) . ' | ' . 'Role' . "\n";
echo str_repeat('-', 80) . "\n";

foreach($users as $user) {
    echo str_pad($user->id, 5) . ' | ' 
         . str_pad($user->name, 20) . ' | ' 
         . str_pad($user->email, 30) . ' | ' 
         . $user->role . "\n";
}

echo "\n✓ Cleanup complete!\n";
