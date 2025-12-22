<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKunjungansTable extends Migration
{
    public function up()
    {
        Schema::create('kunjungans', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('approver_id')->nullable();
            $table->foreign('approver_id')->references('id')->on('users');

            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->foreign('gudang_id')->references('id')->on('gudangs');

            $table->integer('no_urut_harian')->default(1);
            $table->string('nomor')->nullable();

            // Sales/kontak info
            $table->string('sales_nama');
            $table->string('sales_email')->nullable();
            $table->text('sales_alamat')->nullable();

            // Kunjungan details
            $table->date('tgl_kunjungan');
            $table->enum('tujuan', ['Pemeriksaan Stock', 'Penagihan', 'Penawaran']);

            // Location & memo
            $table->string('koordinat')->nullable();
            $table->text('memo')->nullable();
            $table->string('lampiran_path')->nullable();

            // Status (same as other modules)
            $table->string('status')->default('Pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kunjungans');
    }
}
