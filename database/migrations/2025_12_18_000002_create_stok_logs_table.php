<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStokLogsTable extends Migration
{
    public function up()
    {
        Schema::create('stok_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gudang_produk_id');
            $table->unsignedBigInteger('produk_id');
            $table->unsignedBigInteger('gudang_id');
            $table->unsignedBigInteger('user_id'); // siapa yang edit
            
            $table->string('produk_nama'); // snapshot nama produk
            $table->string('gudang_nama'); // snapshot nama gudang
            $table->string('user_nama');   // snapshot nama user
            
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->integer('selisih'); // bisa + atau -
            
            $table->string('keterangan')->nullable(); // alasan edit
            
            $table->timestamps();
            
            // Indexes
            $table->index('gudang_produk_id');
            $table->index('produk_id');
            $table->index('gudang_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stok_logs');
    }
}
