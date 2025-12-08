<?php
// HAPUS FILE INI SETELAH DIGUNAKAN!
// File untuk cek routes yang terdaftar

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "<pre>";
echo "=== CHECKING ROUTES ===\n\n";

// Get all routes
$routes = Route::getRoutes();

echo "PEMBELIAN ROUTES:\n";
foreach ($routes as $route) {
    if (strpos($route->uri(), 'pembelian') !== false) {
        echo sprintf("%-10s %-40s %-30s %s\n", 
            implode('|', $route->methods()), 
            $route->uri(), 
            $route->getName() ?? '(no name)',
            $route->getActionName()
        );
    }
}

echo "\n\nPENJUALAN ROUTES:\n";
foreach ($routes as $route) {
    if (strpos($route->uri(), 'penjualan') !== false) {
        echo sprintf("%-10s %-40s %-30s %s\n", 
            implode('|', $route->methods()), 
            $route->uri(), 
            $route->getName() ?? '(no name)',
            $route->getActionName()
        );
    }
}

echo "\n\n=== CHECKING URL GENERATION ===\n";
try {
    echo "route('pembelian.index') = " . route('pembelian.index') . "\n";
    echo "route('penjualan.index') = " . route('penjualan.index') . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n\n=== ENVIRONMENT INFO ===\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";

echo "\nSEGERA HAPUS FILE INI UNTUK KEAMANAN!";
echo "</pre>";
