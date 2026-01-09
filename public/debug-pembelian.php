<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

$gudangId = $_GET['gudang_id'] ?? 2;

// Get pembelians
$pembelians = App\Pembelian::with('items.produk')
    ->where('gudang_id', $gudangId)
    ->whereIn('status', ['Approved', 'Pending'])
    ->get();

$result = [];
foreach ($pembelians as $p) {
    $items = [];
    foreach ($p->items as $item) {
        // Check penerimaan items
        $qtyDiterima = App\PenerimaanBarangItem::whereHas('penerimaanBarang', function($q) use ($p) {
            $q->where('pembelian_id', $p->id)->where('status', 'Approved');
        })->where('produk_id', $item->produk_id)->sum('qty_diterima');
        
        $items[] = [
            'produk_id' => $item->produk_id,
            'produk_nama' => $item->produk ? $item->produk->nama_produk : 'N/A',
            'kuantitas' => $item->kuantitas,
            'jumlah' => $item->jumlah ?? null,
            'qty_diterima' => $qtyDiterima,
            'qty_sisa' => ($item->kuantitas ?? 0) - $qtyDiterima,
        ];
    }
    
    $result[] = [
        'id' => $p->id,
        'nomor' => $p->nomor,
        'status' => $p->status,
        'items_count' => count($items),
        'items' => $items,
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
