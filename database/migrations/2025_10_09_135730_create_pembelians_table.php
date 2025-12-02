<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembeliansTable extends Migration
{
    public function up()
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            
            // Relasi & ID
            $table->unsignedBigInteger('user_id'); // Pembuat
            $table->unsignedBigInteger('gudang_id'); // Gudang Tujuan
            $table->unsignedBigInteger('approver_id'); // Admin Penyetuju (ID)
            
            // Penomoran
            $table->integer('no_urut_harian')->default(1);

            // Data String (Snapshot data saat transaksi dibuat)
            $table->string('staf_penyetuju'); // Nama Admin (Snapshot)
            $table->string('email_penyetuju')->nullable(); // Email Admin (Snapshot)
            
            // Tanggal & Syarat
            $table->date('tgl_transaksi');
            $table->string('syarat_pembayaran'); // Cash, Net 30, dll
            $table->date('tgl_jatuh_tempo')->nullable();
            
            // Meta Data
            $table->string('urgensi');
            $table->string('tahun_anggaran')->nullable();
            $table->string('tag')->nullable();
            $table->text('memo')->nullable();
            $table->string('lampiran_path')->nullable();
            
            // Status
            $table->string('status')->default('Pending');
            
            // Keuangan
            $table->decimal('diskon_akhir', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('grand_total', 15, 2);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('gudang_id')->references('id')->on('gudangs');
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelians');
    }
}