<?php
// HAPUS FILE INI SETELAH DIGUNAKAN!
// File untuk clear cache darurat

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "Clearing route cache...\n";
$kernel->call('route:clear');

echo "Clearing config cache...\n";
$kernel->call('config:clear');

echo "Clearing view cache...\n";
$kernel->call('view:clear');

echo "Clearing all cache...\n";
$kernel->call('cache:clear');

echo "\nAll caches cleared!\n";
echo "SEGERA HAPUS FILE INI UNTUK KEAMANAN!";
echo "</pre>";
