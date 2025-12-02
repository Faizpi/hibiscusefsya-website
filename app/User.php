<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'gudang_id', // Pastikan 'role' ada di sini
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
                'user' => 'User',
                'admin' => 'Admin',
                'super_admin' => 'Super Admin',
            ];
        }
        return [
            'user' => 'User',
            'admin' => 'Admin',
        ];
    }
}