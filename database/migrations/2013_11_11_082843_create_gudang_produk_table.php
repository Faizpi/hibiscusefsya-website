<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGudangProdukTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gudang_produk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gudang_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('stok')->default(0);

            $table->foreign('gudang_id')->references('id')->on('gudangs')->onDelete('cascade');
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
            // Pastikan tidak ada duplikat (hanya ada 1 data stok untuk 1 produk di 1 gudang)
            $table->unique(['gudang_id', 'produk_id']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gudang_produk');
    }
}
