<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int|null $approver_id
 * @property int|null $no_urut_harian
 * @property string $jenis_biaya
 * @property string|null $nomor
 * @property string|null $bayar_dari
 * @property string|null $penerima
 * @property string|null $alamat_penagihan
 * @property \Carbon\Carbon|null $tgl_transaksi
 * @property string|null $cara_pembayaran
 * @property string|null $tag
 * @property string|null $koordinat
 * @property string|null $memo
 * @property string|null $lampiran_path
 * @property string $status
 * @property float|null $tax_percentage
 * @property float|null $grand_total
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User|null $user
 * @property-read \App\User|null $approver
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BiayaItem[] $items
 */
class Biaya extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'approver_id',
        'no_urut_harian',
        'jenis_biaya',
        'nomor',
        'bayar_dari',
        'penerima',
        'alamat_penagihan',
        'tgl_transaksi',
        'cara_pembayaran',
        'tag',
        'koordinat',
        'memo',
        'lampiran_path',
        'lampiran_paths',
        'status',
        'tax_percentage',
        'grand_total'
    ];

    /**
     * Boot method - auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'tgl_transaksi' => 'date',
        'lampiran_paths' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function items()
    {
        return $this->hasMany(BiayaItem::class);
    }

    /**
     * Accessor untuk custom_number
     * Gunakan nomor dari DB jika ada, atau generate
     */
    public function getCustomNumberAttribute()
    {
        if ($this->nomor) {
            return $this->nomor;
        }
        $dateCode = $this->created_at->format('Ymd');
        $noUrutPadded = str_pad($this->no_urut_harian, 3, '0', STR_PAD_LEFT);
        return "EXP-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }

    /**
     * Generate nomor transaksi
     */
    public static function generateNomor($userId, $noUrut, $createdAt)
    {
        $dateCode = $createdAt->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        return "EXP-{$dateCode}-{$userId}-{$noUrutPadded}";
    }
}