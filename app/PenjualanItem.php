<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

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