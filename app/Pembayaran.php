<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pembayaran extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'approver_id',
        'gudang_id',
        'penjualan_id',
        'no_urut_harian',
        'nomor',
        'tgl_pembayaran',
        'metode_pembayaran',
        'jumlah_bayar',
        'bukti_bayar',
        'lampiran_paths',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tgl_pembayaran' => 'date',
        'lampiran_paths' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

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

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    /**
     * Accessor untuk custom_number
     */
    public function getCustomNumberAttribute()
    {
        if ($this->nomor) {
            return $this->nomor;
        }
        $dateCode = $this->created_at->format('Ymd');
        $noUrutPadded = str_pad($this->no_urut_harian, 3, '0', STR_PAD_LEFT);
        return "PAY-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }

    /**
     * Generate nomor transaksi
     */
    public static function generateNomor($userId, $noUrut, $createdAt)
    {
        $dateCode = $createdAt->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        return "PAY-{$dateCode}-{$userId}-{$noUrutPadded}";
    }
}
