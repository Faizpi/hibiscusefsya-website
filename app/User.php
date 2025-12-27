<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $alamat
 * @property string|null $no_telp
 * @property int|null $gudang_id
 * @property int|null $current_gudang_id
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Gudang|null $gudang
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Gudang[] $gudangs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Gudang[] $spectatorGudangs
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'alamat',
        'no_telp',
        'gudang_id',
        'current_gudang_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    /**
     * Relationship: admin dapat handle multiple gudang
     * Hanya untuk admin
     */
    public function gudangs()
    {
        return $this->belongsToMany(Gudang::class, 'admin_gudang')
            ->withTimestamps();
    }

    /**
     * Relationship: spectator dapat handle multiple gudang
     * Hanya untuk spectator
     */
    public function spectatorGudangs()
    {
        return $this->belongsToMany(Gudang::class, 'spectator_gudang')
            ->withTimestamps();
    }

    /**
     * Get current active gudang
     * For admin: return current_gudang_id jika ada, else first assigned gudang
     * For spectator: return current_gudang_id jika ada, else first assigned gudang
     * For user: return single gudang
     */
    public function getCurrentGudang()
    {
        if ($this->role === 'user') {
            return $this->gudang;
        }

        if ($this->current_gudang_id) {
            return Gudang::find($this->current_gudang_id);
        }

        // Fallback: return first assigned gudang
        if ($this->role === 'admin') {
            return $this->gudangs()->first();
        } elseif ($this->role === 'spectator') {
            return $this->spectatorGudangs()->first();
        }

        return null;
    }

    /**
     * Check if admin/spectator can access specific gudang
     */
    public function canAccessGudang($gudangId)
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if ($this->role === 'admin') {
            return $this->gudangs()->where('gudangs.id', $gudangId)->exists();
        }

        if ($this->role === 'spectator') {
            return $this->spectatorGudangs()->where('gudangs.id', $gudangId)->exists();
        }

        return $this->gudang_id == $gudangId;
    }

    /**
     * Check if user is spectator (read-only role)
     */
    public function isSpectator()
    {
        return $this->role === 'spectator';
    }

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
    }

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class);
    }

    public function biayas()
    {
        return $this->hasMany(Biaya::class);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin or super admin
     */
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Get available roles based on current user's role
     */
    public static function getAvailableRoles()
    {
        if (auth()->user()->isSuperAdmin()) {
            return [
                'user' => 'User (Sales)',
                'admin' => 'Admin',
                'spectator' => 'Spectator (Read-Only)',
                'super_admin' => 'Super Admin',
            ];
        }
        return [
            'user' => 'User (Sales)',
            'admin' => 'Admin',
        ];
    }
}