<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiayaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biaya_items', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel 'biayas' (induk)
            $table->unsignedBigInteger('biaya_id');
            $table->foreign('biaya_id')->references('id')->on('biayas')->onDelete('cascade');

            // Kolom rincian
            $table->string('kategori');
            $table->string('deskripsi')->nullable();
            $table->decimal('jumlah', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('biaya_items');
    }
}
