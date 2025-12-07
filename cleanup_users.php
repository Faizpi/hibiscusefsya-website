<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;
use Illuminate\Support\Facades\DB;

echo "\n=== Cleaning Users Table ===\n";

// 1. Check users with null or empty role
$invalidUsers = User::whereNull('role')->orWhere('role', '')->get();
echo "\nUsers with null/empty role: " . $invalidUsers->count() . "\n";
foreach ($invalidUsers as $user) {
    echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}

// 2. Fix invalid users - set default role to 'user'
if ($invalidUsers->count() > 0) {
    User::whereNull('role')->orWhere('role', '')->update(['role' => 'user']);
    echo "\n✓ Fixed {$invalidUsers->count()} users - set role to 'user'\n";
}

// 3. Show all users after cleanup
echo "\n=== All Users After Cleanup ===\n";
$users = User::select('id', 'name', 'email', 'role')->get();
echo str_pad('ID', 5) . ' | ' . str_pad('Name', 20) . ' | ' . str_pad('Email', 30) . ' | ' . 'Role' . "\n";
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    echo str_pad($user->id, 5) . ' | '
        . str_pad($user->name, 20) . ' | '
        . str_pad($user->email, 30) . ' | '
        . $user->role . "\n";
}

echo "\n✓ Cleanup complete!\n";
