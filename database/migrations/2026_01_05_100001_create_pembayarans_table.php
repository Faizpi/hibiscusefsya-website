<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaransTable extends Migration
{
    public function up()
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('approver_id')->nullable();
            $table->foreign('approver_id')->references('id')->on('users');
            
            $table->unsignedBigInteger('gudang_id');
            $table->foreign('gudang_id')->references('id')->on('gudangs');
            
            // Referensi ke penjualan yang dibayar
            $table->unsignedBigInteger('penjualan_id');
            $table->foreign('penjualan_id')->references('id')->on('penjualans');

            $table->integer('no_urut_harian')->default(1);
            $table->string('nomor')->nullable();
            
            $table->date('tgl_pembayaran');
            $table->string('metode_pembayaran'); // Cash, Transfer, Giro, dll
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('bukti_bayar')->nullable(); // Path file bukti
            $table->text('lampiran_paths')->nullable();
            $table->text('keterangan')->nullable();
            
            $table->string('status')->default('Pending'); // Pending, Approved, Canceled
            $table->timestamps();
            
            // Indexes
            $table->index('gudang_id');
            $table->index('penjualan_id');
            $table->index('status');
            $table->index('tgl_pembayaran');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayarans');
    }
}
