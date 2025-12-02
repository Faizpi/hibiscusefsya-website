<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penjualan_id');
            $table->foreign('penjualan_id')->references('id')->on('penjualans')->onDelete('cascade');

            // --- PERUBAHAN DI SINI ---
            $table->unsignedBigInteger('produk_id'); // Menggantikan $table->string('produk')
            $table->foreign('produk_id')->references('id')->on('produks');
            // -------------------------

            $table->text('deskripsi')->nullable();
            $table->integer('kuantitas');
            $table->string('unit')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('diskon', 5, 2)->default(0);
            $table->decimal('jumlah_baris', 15, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penjualan_items');
    }
}
