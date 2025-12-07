<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;

$users = User::select('id', 'name', 'email', 'role')->get();

echo "\n=== User Roles ===\n";
echo str_pad('ID', 5) . ' | ' . str_pad('Name', 20) . ' | ' . str_pad('Email', 30) . ' | ' . 'Role' . "\n";
echo str_repeat('-', 80) . "\n";

foreach($users as $user) {
    echo str_pad($user->id, 5) . ' | ' 
         . str_pad($user->name, 20) . ' | ' 
         . str_pad($user->email, 30) . ' | ' 
         . $user->role . "\n";
}

echo "\n=== Users with role = 'admin' ===\n";
$admins = User::where('role', 'admin')->count();
echo "Total: " . $admins . "\n";
