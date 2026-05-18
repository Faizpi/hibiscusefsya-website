<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string|null $kode_kontak
 * @property string $nama
 * @property string|null $email
 * @property string|null $no_telp
 * @property string|null $alamat
 * @property float|null $diskon_persen
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User|null $creator
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
        'pin',
        'alamat',
        'diskon_persen',
        'gudang_id',
        'created_by',
    ];

    /**
     * Boot method - auto-generate kode_kontak and set created_by
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

            if (empty($model->created_by) && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    /**
     * User yang membuat kontak ini.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class);
    }
}