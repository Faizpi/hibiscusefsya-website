<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $penjualan_id
 * @property int $produk_id
 * @property string|null $deskripsi
 * @property int $kuantitas
 * @property string|null $unit
 * @property float $harga_satuan
 * @property float|null $diskon
 * @property float $jumlah_baris
 * @property-read \App\Penjualan $penjualan
 * @property-read \App\Produk $produk
 */
class PenjualanItem extends Model
{
    public $timestamps = false; // Rincian tidak perlu created_at/updated_at

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'deskripsi',
        'kuantitas',
        'unit',
        'harga_satuan',
        'diskon',
        'jumlah_baris',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}