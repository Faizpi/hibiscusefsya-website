<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kontak extends Model
{
    protected $table = 'kontaks';

    protected $fillable = [
        'kode_kontak',
        'nama',
        'email',
        'no_telp',
        'alamat',
        'diskon_persen',
    ];

    /**
     * Boot method - auto-generate kode_kontak
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->kode_kontak)) {
                $lastKontak = static::orderBy('id', 'desc')->first();
                $nextId = $lastKontak ? $lastKontak->id + 1 : 1;
                $model->kode_kontak = 'KT' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relasi ke kunjungan
     */
    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class);
    }
}