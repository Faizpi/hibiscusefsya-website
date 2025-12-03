<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Biaya extends Model
{
    protected $fillable = [
        'user_id',
        'approver_id',
        'no_urut_harian',
        'bayar_dari',
        'penerima',
        'alamat_penagihan',
        'tgl_transaksi',
        'cara_pembayaran',
        'tag',
        'koordinat',
        'memo',
        'lampiran_path',
        'status',
        'tax_percentage',
        'grand_total'
    ];

    protected $casts = [
        'tgl_transaksi' => 'date',
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
     * Format: EXP-YYYYMMDD-USER_ID-NO_URUT
     */
    public function getCustomNumberAttribute()
    {
        $dateCode = $this->created_at->format('Ymd');
        $noUrutPadded = str_pad($this->no_urut_harian, 3, '0', STR_PAD_LEFT);
        return "EXP-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }
}