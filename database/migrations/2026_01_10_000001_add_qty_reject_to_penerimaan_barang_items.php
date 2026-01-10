<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtyRejectToPenerimaanBarangItems extends Migration
{
    public function up()
    {
        Schema::table('penerimaan_barang_items', function (Blueprint $table) {
            $table->integer('qty_reject')->default(0)->after('qty_diterima');
        });
    }

    public function down()
    {
        Schema::table('penerimaan_barang_items', function (Blueprint $table) {
            $table->dropColumn('qty_reject');
        });
    }
}
