<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
