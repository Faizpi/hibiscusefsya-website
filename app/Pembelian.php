<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $approver_id
 * @property int|null $no_urut_harian
 * @property string|null $nomor
 * @property string $uuid
 * @property int|null $gudang_id
 * @property string|null $staf_penyetuju
 * @property string|null $email_penyetuju
 * @property \Carbon\Carbon|null $tgl_transaksi
 * @property \Carbon\Carbon|null $tgl_jatuh_tempo
 * @property string|null $syarat_pembayaran
 * @property string|null $urgensi
 * @property string|null $tahun_anggaran
 * @property string|null $tag
 * @property string|null $koordinat
 * @property string|null $memo
 * @property string|null $lampiran_path
 * @property string $status
 * @property float|null $diskon_akhir
 * @property float|null $tax_percentage
 * @property float|null $grand_total
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User|null $user
 * @property-read \App\User|null $approver
 * @property-read \App\Gudang|null $gudang
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PembelianItem[] $items
 */
class Pembelian extends Model
{
    // Pastikan semua kolom ini ada di fillable
    protected $fillable = [
        'user_id',
        'approver_id',
        'no_urut_harian',
        'nomor',
        'uuid',
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
        'lampiran_paths',
        'status',

        // Keuangan
        'diskon_akhir',
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
        'tgl_jatuh_tempo' => 'date',
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

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function items()
    {
        return $this->hasMany(PembelianItem::class);
    }

    /**
     * Accessor untuk status display di invoice
     * - Jika pembayaran Cash → selalu tampil "Lunas" (langsung bayar)
     * - Jika status Lunas → tampil "Lunas"
     * - Jika bukan Cash dan Approved → tampil "Belum Lunas"
     * - Selain itu tampil status asli (Pending/Canceled)
     */
    public function getStatusDisplayAttribute()
    {
        // Cash selalu Lunas karena langsung dibayar
        if ($this->syarat_pembayaran === 'Cash') {
            return 'Lunas';
        }
        if ($this->status === 'Lunas') {
            return 'Lunas';
        }
        if ($this->status === 'Approved') {
            return 'Belum Lunas';
        }
        return $this->status;
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
        return "PR-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }

    /**
     * Generate nomor transaksi
     */
    public static function generateNomor($userId, $noUrut, $createdAt)
    {
        $dateCode = $createdAt->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        return "PR-{$dateCode}-{$userId}-{$noUrutPadded}";
    }
}