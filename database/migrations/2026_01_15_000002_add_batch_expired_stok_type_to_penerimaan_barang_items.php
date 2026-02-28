<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchExpiredStokTypeToPenerimaanBarangItems extends Migration
{
    public function up()
    {
        Schema::table('penerimaan_barang_items', function (Blueprint $table) {
            $table->string('tipe_stok', 20)->default('penjualan')->after('qty_reject');
            $table->string('batch_number', 100)->nullable()->after('tipe_stok');
            $table->date('expired_date')->nullable()->after('batch_number');
        });
    }

    public function down()
    {
        Schema::table('penerimaan_barang_items', function (Blueprint $table) {
            $table->dropColumn(['tipe_stok', 'batch_number', 'expired_date']);
        });
    }
}
