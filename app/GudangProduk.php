<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class GudangProduk extends Model
{
    protected $table = 'gudang_produk'; // Nama tabel pivot
    public $timestamps = false; // Tabel ini tidak perlu timestamps
    protected $fillable = ['gudang_id', 'produk_id', 'stok', 'stok_penjualan', 'stok_gratis', 'stok_sample'];

    // Relasi ke model Gudang
    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    // Relasi ke model Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}