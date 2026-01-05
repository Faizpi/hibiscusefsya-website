<?php
// Script to create super admin user
// Run: php create_super_admin.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;
use Illuminate\Support\Facades\Hash;

$user = User::updateOrCreate(
    ['email' => 'super@gmail.com'],
    [
        'name' => 'Super Admin',
        'password' => Hash::make('password'),
        'role' => 'super_admin',
    ]
);

echo "Super Admin created/updated successfully!\n";
echo "Email: super@gmail.com\n";
echo "Password: password\n";
echo "Role: super_admin\n";
