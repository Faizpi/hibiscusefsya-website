<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiayasTable extends Migration
{
    public function up()
    {
        Schema::create('biayas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('approver_id')->nullable();
            $table->foreign('approver_id')->references('id')->on('users');
            $table->integer('no_urut_harian')->default(1);

            $table->string('bayar_dari');
            $table->string('penerima')->nullable();
            $table->text('alamat_penagihan')->nullable();
            $table->date('tgl_transaksi');
            $table->string('cara_pembayaran')->nullable();
            $table->string('tag')->nullable();
            $table->text('memo')->nullable();
            $table->string('lampiran_path')->nullable();
            
            $table->string('status')->default('Pending');
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('grand_total', 15, 2);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('biayas');
    }
}