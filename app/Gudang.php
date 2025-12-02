<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    protected $fillable = ['nama_gudang', 'alamat_gudang'];

    // Definisikan relasi ke user yang memegang gudang ini
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Definisikan relasi ke tabel stok (gudang_produk)
    public function produkStok()
    {
        return $this->hasMany(GudangProduk::class);
    }
}