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
 * @property string|null $pelanggan
 * @property string|null $email
 * @property string|null $alamat_penagihan
 * @property string|null $koordinat
 * @property \Carbon\Carbon|null $tgl_transaksi
 * @property \Carbon\Carbon|null $tgl_jatuh_tempo
 * @property string|null $syarat_pembayaran
 * @property string|null $no_referensi
 * @property string|null $tag
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PenjualanItem[] $items
 */
class Penjualan extends Model
{
    protected $fillable = [
        'user_id',
        'approver_id',
        'no_urut_harian',
        'nomor',
        'uuid',
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
        return "INV-{$dateCode}-{$this->user_id}-{$noUrutPadded}";
    }

    /**
     * Generate nomor transaksi
     */
    public static function generateNomor($userId, $noUrut, $createdAt)
    {
        $dateCode = $createdAt->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        return "INV-{$dateCode}-{$userId}-{$noUrutPadded}";
    }
}