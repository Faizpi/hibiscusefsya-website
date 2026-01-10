<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanBarangsTable extends Migration
{
    public function up()
    {
        Schema::create('penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('approver_id')->nullable();
            $table->foreign('approver_id')->references('id')->on('users');
            
            $table->unsignedBigInteger('gudang_id');
            $table->foreign('gudang_id')->references('id')->on('gudangs');
            
            // Referensi ke pembelian
            $table->unsignedBigInteger('pembelian_id');
            $table->foreign('pembelian_id')->references('id')->on('pembelians');

            $table->integer('no_urut_harian')->default(1);
            $table->string('nomor')->nullable();
            
            $table->date('tgl_penerimaan');
            $table->string('no_surat_jalan')->nullable();
            $table->text('lampiran_paths')->nullable();
            $table->text('keterangan')->nullable();
            
            $table->string('status')->default('Pending'); // Pending, Approved, Canceled
            $table->timestamps();
            
            // Indexes
            $table->index('gudang_id');
            $table->index('pembelian_id');
            $table->index('status');
            $table->index('tgl_penerimaan');
        });

        // Table untuk item penerimaan barang (detail qty per produk)
        Schema::create('penerimaan_barang_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_barang_id');
            $table->foreign('penerimaan_barang_id')->references('id')->on('penerimaan_barangs')->onDelete('cascade');
            
            $table->unsignedBigInteger('produk_id');
            $table->foreign('produk_id')->references('id')->on('produks');
            
            $table->integer('qty_diterima');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penerimaan_barang_items');
        Schema::dropIfExists('penerimaan_barangs');
    }
}
