<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PenerimaanBarang extends Model
{
    protected $table = 'penerimaan_barangs';
    
    protected $fillable = [
        'uuid',
        'user_id',
        'approver_id',
        'gudang_id',
        'pembelian_id',
        'no_urut_harian',
        'nomor',
        'tgl_penerimaan',
        'no_surat_jalan',
        'lampiran_paths',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tgl_penerimaan' => 'date',
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

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function items()
    {
        return $this->hasMany(PenerimaanBarangItem::class);
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
        return "RCV-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }

    /**
     * Generate nomor transaksi
     */
    public static function generateNomor($userId, $noUrut, $createdAt)
    {
        $dateCode = $createdAt->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        return "RCV-{$dateCode}-{$userId}-{$noUrutPadded}";
    }
}
