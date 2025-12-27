<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pembelian_id
 * @property int $produk_id
 * @property string|null $deskripsi
 * @property int $kuantitas
 * @property string|null $unit
 * @property float $harga_satuan
 * @property float|null $diskon
 * @property float $jumlah_baris
 * @property-read \App\Pembelian $pembelian
 * @property-read \App\Produk $produk
 */
class PembelianItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'deskripsi',
        'kuantitas',
        'unit',
        'harga_satuan', // <-- TAMBAHKAN INI
        'diskon',       // <-- TAMBAHKAN INI
        'jumlah_baris', // <-- TAMBAHKAN INI
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}