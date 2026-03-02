<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHargaGrosirAndTipeHarga extends Migration
{
    public function up()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->decimal('harga_grosir', 15, 2)->default(0)->after('harga');
        });

        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('tipe_harga', 10)->default('retail')->after('gudang_id');
        });
    }

    public function down()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('harga_grosir');
        });

        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn('tipe_harga');
        });
    }
}
