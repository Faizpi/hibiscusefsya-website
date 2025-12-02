<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKoordinatToPenjualansTable extends Migration
{
    /**
     * Menambahkan koordinat GPS untuk audit lokasi saat transaksi dibuat
     */
    public function up()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('koordinat')->nullable()->after('tag');
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('koordinat')->nullable()->after('tag');
        });

        Schema::table('biayas', function (Blueprint $table) {
            $table->string('koordinat')->nullable()->after('tag');
        });
    }

    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });

        Schema::table('biayas', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
}
