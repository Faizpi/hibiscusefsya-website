<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_produk
 * @property string|null $item_code
 * @property float|null $harga
 * @property string|null $deskripsi
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GudangProduk[] $stokDiGudang
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GudangProduk[] $gudangProduks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\KunjunganItem[] $kunjunganItems
 */
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