<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $biaya_id
 * @property string|null $kategori
 * @property string|null $deskripsi
 * @property float $jumlah
 * @property-read \App\Biaya $biaya
 */
class BiayaItem extends Model
{
    // Matikan timestamps (created_at, updated_at) jika tidak perlu
    public $timestamps = false;

    protected $fillable = [
        'biaya_id',
        'kategori',
        'deskripsi',
        'jumlah',
    ];

    /**
     * Relasi ke induk (Biaya)
     */
    public function biaya()
    {
        return $this->belongsTo(Biaya::class);
    }
}