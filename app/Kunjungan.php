<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int|null $approver_id
 * @property int|null $gudang_id
 * @property int|null $kontak_id
 * @property int|null $no_urut_harian
 * @property string|null $nomor
 * @property string|null $sales_nama
 * @property string|null $sales_email
 * @property string|null $sales_alamat
 * @property \Carbon\Carbon|null $tgl_kunjungan
 * @property string|null $tujuan
 * @property string|null $koordinat
 * @property string|null $memo
 * @property string|null $lampiran_path
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User|null $user
 * @property-read \App\User|null $approver
 * @property-read \App\Gudang|null $gudang
 * @property-read \App\Kontak|null $kontak
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\KunjunganItem[] $items
 */
class Kunjungan extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'approver_id',
        'gudang_id',
        'kontak_id',
        'no_urut_harian',
        'nomor',
        'sales_nama',
        'sales_email',
        'sales_alamat',
        'tgl_kunjungan',
        'tujuan',
        'koordinat',
        'memo',
        'lampiran_path',
        'status',
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
        'tgl_kunjungan' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function kontak()
    {
        return $this->belongsTo(Kontak::class);
    }

    public function items()
    {
        return $this->hasMany(KunjunganItem::class);
    }

    /**
     * Accessor untuk custom_number
     * Format: VST-YYYYMMDD-USERID-NOURUT
     */
    public function getCustomNumberAttribute()
    {
        if ($this->nomor) {
            return $this->nomor;
        }
        $dateCode = $this->created_at->format('Ymd');
        $noUrutPadded = str_pad($this->no_urut_harian, 3, '0', STR_PAD_LEFT);
        return "VST-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }

    /**
     * Generate nomor kunjungan
     */
    public static function generateNomor($userId, $noUrut, $createdAt)
    {
        $dateCode = $createdAt->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        return "VST-{$dateCode}-{$userId}-{$noUrutPadded}";
    }

    /**
     * Get tujuan label with icon
     */
    public function getTujuanBadgeAttribute()
    {
        $badges = [
            'Pemeriksaan Stock' => '<span class="badge badge-info">Pemeriksaan Stock</span>',
            'Penagihan' => '<span class="badge badge-warning">Penagihan</span>',
            'Penawaran' => '<span class="badge badge-success">Penawaran</span>',
            'Promo' => '<span class="badge badge-primary">Promo</span>',
        ];
        return $badges[$this->tujuan] ?? '<span class="badge badge-secondary">' . $this->tujuan . '</span>';
    }

    /**
     * Constant untuk jenis tujuan kunjungan
     */
    const TUJUAN_OPTIONS = [
        'Pemeriksaan Stock',
        'Penagihan',
        'Penawaran',
        'Promo',
    ];
}
