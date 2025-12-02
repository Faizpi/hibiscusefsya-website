<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualansTable extends Migration
{
    public function up()
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            
            // Relasi User Pembuat (Inputter)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Relasi Gudang (Stok diambil dari sini)
            $table->unsignedBigInteger('gudang_id');
            $table->foreign('gudang_id')->references('id')->on('gudangs');

            // Relasi Approver (Admin/Super Admin yang dipilih user)
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->foreign('approver_id')->references('id')->on('users');

            // Penomoran Reset Harian per User
            $table->integer('no_urut_harian')->default(1);

            // Data Transaksi
            $table->string('pelanggan'); // Nama Pelanggan (Dari Kontak)
            $table->string('email')->nullable();
            $table->text('alamat_penagihan')->nullable();
            
            $table->date('tgl_transaksi');
            $table->string('syarat_pembayaran'); // Cash, Net 30, dll (Menentukan Jatuh Tempo)
            $table->date('tgl_jatuh_tempo')->nullable(); 
            
            $table->string('no_referensi')->nullable();
            $table->string('tag')->nullable();
            $table->text('memo')->nullable();
            $table->string('lampiran_path')->nullable();
            
            // Status: Pending, Approved, Canceled, Lunas
            $table->string('status')->default('Pending');
            
            // Keuangan
            $table->decimal('diskon_akhir', 15, 2)->default(0); // Diskon global (Nominal)
            $table->decimal('tax_percentage', 5, 2)->default(0); // Persen Pajak
            $table->decimal('grand_total', 15, 2); // Total Akhir (Setelah Diskon & Pajak)

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penjualans');
    }
}