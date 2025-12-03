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
}