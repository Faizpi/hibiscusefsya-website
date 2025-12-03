<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $fillable = [
        'user_id',
        'approver_id',
        'no_urut_harian',
        'gudang_id',
        'pelanggan',
        'email',
        'alamat_penagihan',
        'koordinat',
        'tgl_transaksi',
        'tgl_jatuh_tempo',
        'syarat_pembayaran',
        'no_referensi',
        'tag',
        'memo',
        'lampiran_path',
        'status',
        'diskon_akhir',
        'tax_percentage',
        'grand_total'
    ];

    protected $casts = [
        'tgl_transaksi' => 'date',
        'tgl_jatuh_tempo' => 'date',
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

    public function items()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}