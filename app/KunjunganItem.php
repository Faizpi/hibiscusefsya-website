<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $kunjungan_id
 * @property int $produk_id
 * @property int|null $jumlah
 * @property string|null $keterangan
 * @property-read \App\Kunjungan $kunjungan
 * @property-read \App\Produk $produk
 */
class KunjunganItem extends Model
{
    protected $table = 'kunjungan_items';

    protected $fillable = [
        'kunjungan_id',
        'produk_id',
        'jumlah',
        'keterangan',
    ];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
