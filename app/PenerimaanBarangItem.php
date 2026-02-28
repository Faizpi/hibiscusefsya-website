<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PenerimaanBarangItem extends Model
{
    protected $table = 'penerimaan_barang_items';
    
    protected $fillable = [
        'penerimaan_barang_id',
        'produk_id',
        'qty_diterima',
        'qty_reject',
        'tipe_stok',
        'batch_number',
        'expired_date',
        'keterangan',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
