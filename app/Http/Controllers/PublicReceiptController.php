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
                'with' => ['gudang', 'user', 'approver', 'items.produk'],
            ],
            'pembelian' => [
                'model' => Pembelian::class,
                'view' => 'pembelian.print',
                'var' => 'pembelian',
                'with' => ['gudang', 'user', 'approver', 'items.produk'],
            ],
            'biaya' => [
                'model' => Biaya::class,
                'view' => 'biaya.print',
                'var' => 'biaya',
                'with' => ['gudang', 'user', 'approver', 'items.produk'],
            ],
            'kunjungan' => [
                'model' => Kunjungan::class,
                'view' => 'kunjungan.print',
                'var' => 'kunjungan',
                'with' => ['kontak', 'gudang', 'user', 'approver', 'items.produk'],
            ],
            'pembayaran' => [
                'model' => Pembayaran::class,
                'view' => 'pembayaran.print',
                'var' => 'pembayaran',
                'with' => ['penjualan', 'gudang', 'user', 'approver'],
            ],
            'penerimaan-barang' => [
                'model' => PenerimaanBarang::class,
                'view' => 'penerimaan-barang.print',
                'var' => 'penerimaan',
                'with' => ['pembelian', 'gudang', 'user', 'approver', 'items.produk'],
            ],
        ];

        return $map[$type] ?? null;
    }
}
