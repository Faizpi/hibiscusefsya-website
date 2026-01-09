<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

$gudangId = $_GET['gudang_id'] ?? 2;
$pembelianId = $_GET['pembelian_id'] ?? null;

if ($pembelianId) {
    // Simulate getPembelianDetail
    $pembelian = App\Pembelian::with('items.produk')->findOrFail($pembelianId);

    $qtyDiterima = [];
    $penerimaanItems = App\PenerimaanBarangItem::whereHas('penerimaanBarang', function ($q) use ($pembelianId) {
        $q->where('pembelian_id', $pembelianId)->where('status', 'Approved');
    })->get();

    foreach ($penerimaanItems as $item) {
        if (!isset($qtyDiterima[$item->produk_id])) {
            $qtyDiterima[$item->produk_id] = 0;
        }
        $qtyDiterima[$item->produk_id] += $item->qty_diterima;
    }

    $items = [];
    foreach ($pembelian->items as $item) {
        $sudahDiterima = $qtyDiterima[$item->produk_id] ?? 0;
        $qtyPesan = $item->kuantitas ?? $item->jumlah ?? 0;
        $items[] = [
            'produk_id' => $item->produk_id,
            'produk_nama' => $item->produk ? ($item->produk->nama_produk ?? $item->produk->item_nama) : ($item->nama_produk ?? '-'),
            'produk_kode' => $item->produk ? ($item->produk->kode_produk ?? $item->produk->item_kode ?? '-') : '-',
            'qty_pesan' => $qtyPesan,
            'qty_diterima' => $sudahDiterima,
            'qty_sisa' => max(0, $qtyPesan - $sudahDiterima),
            'satuan' => $item->satuan ?? ($item->produk ? $item->produk->satuan : 'Pcs'),
        ];
    }

    echo json_encode([
        'id' => $pembelian->id,
        'nomor' => $pembelian->nomor ?? $pembelian->custom_number ?? 'PO-' . $pembelian->id,
        'supplier' => $pembelian->nama_supplier ?? '-',
        'tgl_transaksi' => $pembelian->tgl_transaksi ? $pembelian->tgl_transaksi->format('d/m/Y') : '-',
        'items' => $items,
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get pembelians list
$pembelians = App\Pembelian::with('items.produk')
    ->where('gudang_id', $gudangId)
    ->whereIn('status', ['Approved', 'Pending'])
    ->get();

$result = [];
foreach ($pembelians as $p) {
    $items = [];
    foreach ($p->items as $item) {
        $qtyDiterima = App\PenerimaanBarangItem::whereHas('penerimaanBarang', function ($q) use ($p) {
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
