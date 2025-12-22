<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kunjungan extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'approver_id',
        'gudang_id',
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
        ];
        return $badges[$this->tujuan] ?? '<span class="badge badge-secondary">' . $this->tujuan . '</span>';
    }
}
