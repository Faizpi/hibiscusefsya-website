<?php
// HAPUS FILE INI SETELAH DIGUNAKAN!
// File untuk cek routes yang terdaftar

echo "<pre>";
echo "=== CHECKING ROUTES ===\n\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    // Boot the application
    $kernel->bootstrap();

    // Get all routes
    $routes = \Illuminate\Support\Facades\Route::getRoutes();

    echo "PEMBELIAN ROUTES:\n";
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'pembelian') !== false) {
            echo sprintf(
                "%-10s %-40s %-30s %s\n",
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
            echo sprintf(
                "%-10s %-40s %-30s %s\n",
                implode('|', $route->methods()),
                $route->uri(),
                $route->getName() ?? '(no name)',
                $route->getActionName()
            );
        }
    }

    echo "\n\n=== CHECKING URL GENERATION ===\n";
    echo "route('pembelian.index') = " . route('pembelian.index') . "\n";
    echo "route('penjualan.index') = " . route('penjualan.index') . "\n";

    echo "\n\n=== ENVIRONMENT INFO ===\n";
    echo "APP_ENV: " . config('app.env') . "\n";
    echo "APP_URL: " . config('app.url') . "\n";
    echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";

} catch (\Exception $e) {
    echo "ERROR OCCURRED:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
}

echo "\n\nSEGERA HAPUS FILE INI UNTUK KEAMANAN!";
echo "</pre>";
