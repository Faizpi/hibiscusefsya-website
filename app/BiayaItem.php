<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

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