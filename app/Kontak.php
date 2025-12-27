<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $kode_kontak
 * @property string $nama
 * @property string|null $email
 * @property string|null $no_telp
 * @property string|null $alamat
 * @property float|null $diskon_persen
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Kunjungan[] $kunjungans
 */
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