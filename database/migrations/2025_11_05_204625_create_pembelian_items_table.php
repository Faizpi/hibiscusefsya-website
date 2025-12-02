<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembelianItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
    {
        Schema::create('pembelian_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembelian_id');
            $table->foreign('pembelian_id')->references('id')->on('pembelians')->onDelete('cascade');

            $table->unsignedBigInteger('produk_id');
            $table->foreign('produk_id')->references('id')->on('produks');
            
            $table->text('deskripsi')->nullable();
            $table->integer('kuantitas');
            $table->string('unit')->nullable();

            // --- TAMBAHKAN FIELD KEUANGAN INI ---
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('diskon', 5, 2)->default(0);
            $table->decimal('jumlah_baris', 15, 2);
            // ------------------------------------
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembelian_items');
    }
}
