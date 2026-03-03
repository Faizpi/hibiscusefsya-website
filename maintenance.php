<?php
/**
 * Toggle Maintenance Mode - Hibiscus Efsya
 * 
 * Cara pakai via SSH:
 *   php maintenance.php on     → Aktifkan maintenance mode
 *   php maintenance.php off    → Nonaktifkan maintenance mode
 *   php maintenance.php status → Cek status saat ini
 * 
 * Atau bisa juga pakai artisan langsung:
 *   php artisan down   → Aktifkan maintenance
 *   php artisan up     → Nonaktifkan maintenance
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = $argv[1] ?? 'status';

switch ($action) {
    case 'on':
    case 'down':
        Artisan::call('down', [
            '--message' => 'Sedang dalam pemeliharaan sistem. Silakan kembali beberapa saat lagi.',
            '--retry' => 60,
        ]);
        echo "✅ Maintenance mode AKTIF\n";
        echo "   Website sekarang menampilkan halaman maintenance.\n";
        echo "   Untuk menonaktifkan: php maintenance.php off\n";
        break;

    case 'off':
    case 'up':
        Artisan::call('up');
        echo "✅ Maintenance mode NONAKTIF\n";
        echo "   Website kembali normal.\n";
        break;

    case 'status':
        $isDown = $app->isDownForMaintenance();
        if ($isDown) {
            echo "🔧 Status: MAINTENANCE MODE (aktif)\n";
            echo "   Website sedang menampilkan halaman maintenance.\n";
            echo "   Untuk menonaktifkan: php maintenance.php off\n";
        } else {
            echo "🟢 Status: ONLINE (normal)\n";
            echo "   Website berjalan normal.\n";
            echo "   Untuk mengaktifkan maintenance: php maintenance.php on\n";
        }
        break;

    default:
        echo "Penggunaan:\n";
        echo "  php maintenance.php on      → Aktifkan maintenance mode\n";
        echo "  php maintenance.php off     → Nonaktifkan maintenance mode\n";
        echo "  php maintenance.php status  → Cek status\n";
        break;
}
