<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    // Pastikan semua kolom ini ada di fillable
    protected $fillable = [
        'user_id',
        'approver_id',
        'no_urut_harian',
        'gudang_id',

        // Field Transaksi
        'staf_penyetuju',    // <--- WAJIB ADA
        'email_penyetuju',   // <--- WAJIB ADA
        'tgl_transaksi',
        'tgl_jatuh_tempo',
        'syarat_pembayaran',
        'urgensi',
        'tahun_anggaran',
        'tag',
        'koordinat',
        'memo',
        'lampiran_path',
        'status',

        // Keuangan
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
        return $this->hasMany(PembelianItem::class);
    }

    /**
     * Accessor untuk custom_number
     * Format: PR-YYYYMMDD-USER_ID-NO_URUT
     */
    public function getCustomNumberAttribute()
    {
        $dateCode = $this->created_at->format('Ymd');
        $noUrutPadded = str_pad($this->no_urut_harian, 3, '0', STR_PAD_LEFT);
        return "PR-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }
}