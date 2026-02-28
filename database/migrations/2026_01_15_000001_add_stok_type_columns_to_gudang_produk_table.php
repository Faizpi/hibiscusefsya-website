<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStokTypeColumnsToGudangProdukTable extends Migration
{
    public function up()
    {
        Schema::table('gudang_produk', function (Blueprint $table) {
            $table->integer('stok_penjualan')->default(0)->after('stok');
            $table->integer('stok_gratis')->default(0)->after('stok_penjualan');
            $table->integer('stok_sample')->default(0)->after('stok_gratis');
        });

        // Migrate existing stok to stok_penjualan
        \DB::statement('UPDATE gudang_produk SET stok_penjualan = stok WHERE stok > 0');
    }

    public function down()
    {
        Schema::table('gudang_produk', function (Blueprint $table) {
            $table->dropColumn(['stok_penjualan', 'stok_gratis', 'stok_sample']);
        });
    }
}
