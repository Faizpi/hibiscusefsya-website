<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $fillable = ['nama_produk', 'item_code', 'harga', 'deskripsi'];

    // Definisikan relasi ke tabel stok (gudang_produk)
    public function stokDiGudang()
    {
        return $this->hasMany(GudangProduk::class);
    }

    // Alias for gudangProduks
    public function gudangProduks()
    {
        return $this->hasMany(GudangProduk::class);
    }

    // Relasi ke kunjungan items
    public function kunjunganItems()
    {
        return $this->hasMany(KunjunganItem::class);
    }
}