<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiskonNominalToPenjualanItemsTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan_items', function (Blueprint $table) {
            $table->decimal('diskon_nominal', 15, 2)->default(0)->after('diskon');
        });
    }

    public function down()
    {
        Schema::table('penjualan_items', function (Blueprint $table) {
            $table->dropColumn('diskon_nominal');
        });
    }
}
