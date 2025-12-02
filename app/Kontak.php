<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Kontak extends Model
{
    // Nama tabel (opsional, tapi baik untuk kejelasan)
    protected $table = 'kontaks';

    protected $fillable = [
        'nama',
        'email',
        'no_telp',
        'alamat',
        'diskon_persen',
    ];
}