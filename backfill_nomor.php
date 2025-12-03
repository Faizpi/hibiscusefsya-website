<?php
// Script untuk backfill nomor transaksi ke database
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Carbon\Carbon;

echo "Updating Penjualan...\n";
$countPenjualan = 0;
Penjualan::whereNull('nomor')->get()->each(function ($p) use (&$countPenjualan) {
    $dateCode = Carbon::parse($p->tgl_transaksi)->format('Ymd');
    $noUrutPadded = str_pad($p->no_urut_harian, 3, '0', STR_PAD_LEFT);
    $p->nomor = 'INV-' . $dateCode . '-' . $p->user_id . '-' . $noUrutPadded;
    $p->save();
    $countPenjualan++;
});
echo "Updated {$countPenjualan} Penjualan records\n";

echo "Updating Pembelian...\n";
$countPembelian = 0;
Pembelian::whereNull('nomor')->get()->each(function ($p) use (&$countPembelian) {
    $dateCode = Carbon::parse($p->tgl_transaksi)->format('Ymd');
    $noUrutPadded = str_pad($p->no_urut_harian, 3, '0', STR_PAD_LEFT);
    $p->nomor = 'PR-' . $dateCode . '-' . $p->user_id . '-' . $noUrutPadded;
    $p->save();
    $countPembelian++;
});
echo "Updated {$countPembelian} Pembelian records\n";

echo "Updating Biaya...\n";
$countBiaya = 0;
Biaya::whereNull('nomor')->get()->each(function ($b) use (&$countBiaya) {
    $dateCode = Carbon::parse($b->tgl_transaksi)->format('Ymd');
    $noUrutPadded = str_pad($b->no_urut_harian, 3, '0', STR_PAD_LEFT);
    $b->nomor = 'EXP-' . $dateCode . '-' . $b->user_id . '-' . $noUrutPadded;
    $b->save();
    $countBiaya++;
});
echo "Updated {$countBiaya} Biaya records\n";

echo "Done!\n";
