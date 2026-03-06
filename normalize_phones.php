<?php

/**
 * One-time script to normalize all phone numbers in kontaks table to 628xxx format.
 * Run: php normalize_phones.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Kontak;

function normalizePhone($phone)
{
    if (empty($phone))
        return $phone;

    // Hapus spasi, strip, titik, kurung
    $phone = preg_replace('/[\s\-\.\(\)]+/', '', $phone);

    // Hapus prefix +
    if (substr($phone, 0, 1) === '+') {
        $phone = substr($phone, 1);
    }

    // 08xxx → 628xxx
    if (substr($phone, 0, 2) === '08') {
        $phone = '62' . substr($phone, 1);
    }

    // 8xxx (tanpa prefix) → 628xxx
    if (substr($phone, 0, 1) === '8' && strlen($phone) >= 9 && strlen($phone) <= 13) {
        $phone = '62' . $phone;
    }

    return $phone;
}

echo "=== Normalisasi Nomor Telepon Kontak ===\n\n";

$kontaks = Kontak::whereNotNull('no_telp')->where('no_telp', '!=', '')->get();
echo "Total kontak dengan no_telp: " . $kontaks->count() . "\n\n";

$updated = 0;
$skipped = 0;

foreach ($kontaks as $kontak) {
    $old = $kontak->no_telp;
    $new = normalizePhone($old);

    if ($old !== $new) {
        // Cek duplikat
        $existing = Kontak::where('no_telp', $new)->where('id', '!=', $kontak->id)->first();
        if ($existing) {
            echo "[SKIP] {$kontak->nama}: {$old} → {$new} (DUPLIKAT dengan {$existing->nama})\n";
            $skipped++;
            continue;
        }

        $kontak->no_telp = $new;
        $kontak->save();
        echo "[OK]   {$kontak->nama}: {$old} → {$new}\n";
        $updated++;
    } else {
        echo "[--]   {$kontak->nama}: {$old} (sudah benar)\n";
    }
}

echo "\nSelesai! Updated: {$updated}, Skipped: {$skipped}, Total: " . $kontaks->count() . "\n";
