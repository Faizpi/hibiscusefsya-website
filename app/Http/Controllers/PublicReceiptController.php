<?php

namespace App\Http\Controllers;

use App\Biaya;
use App\Kunjungan;
use App\Pembayaran;
use App\Pembelian;
use App\PenerimaanBarang;
use App\Penjualan;

class PublicReceiptController extends Controller
{
    /**
     * Public struk endpoint for mobile/detail actions.
     * Uses UUID to avoid exposing sequential IDs.
     */
    public function show($type, $uuid)
    {
        $config = $this->receiptConfig($type);
        if (!$config) {
            abort(404);
        }

        $modelClass = $config['model'];
        $record = $modelClass::where('uuid', $uuid)->firstOrFail();

        if (!empty($config['with'])) {
            $record->load($config['with']);
        }

        return view($config['view'], [
            $config['var'] => $record,
        ]);
    }

    private function receiptConfig($type)
    {
        $map = [
            'penjualan' => [
                'model' => Penjualan::class,
                'view' => 'penjualan.print',
                'var' => 'penjualan',
                'with' => ['kontak', 'gudang', 'user', 'items.produk'],
            ],
            'pembelian' => [
                'model' => Pembelian::class,
                'view' => 'pembelian.print',
                'var' => 'pembelian',
                'with' => ['kontak', 'gudang', 'user', 'items.produk'],
            ],
            'biaya' => [
                'model' => Biaya::class,
                'view' => 'biaya.print',
                'var' => 'biaya',
                'with' => ['kontak', 'gudang', 'user', 'items.produk'],
            ],
            'kunjungan' => [
                'model' => Kunjungan::class,
                'view' => 'kunjungan.print',
                'var' => 'kunjungan',
                'with' => ['kontak', 'gudang', 'user', 'items.produk'],
            ],
            'pembayaran' => [
                'model' => Pembayaran::class,
                'view' => 'pembayaran.print',
                'var' => 'pembayaran',
                'with' => ['penjualan.kontak', 'gudang', 'user'],
            ],
            'penerimaan-barang' => [
                'model' => PenerimaanBarang::class,
                'view' => 'penerimaan-barang.print',
                'var' => 'penerimaan',
                'with' => ['pembelian.kontak', 'gudang', 'user', 'items.produk'],
            ],
        ];

        return $map[$type] ?? null;
    }
}
