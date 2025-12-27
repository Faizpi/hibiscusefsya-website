<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email dari Hibiscus Efsya - ' . date('Y-m-d H:i:s'), function($m) {
        $m->to('admin@hibiscusefsya.com')->subject('Test Email Config');
    });
    echo "Email berhasil dikirim!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
