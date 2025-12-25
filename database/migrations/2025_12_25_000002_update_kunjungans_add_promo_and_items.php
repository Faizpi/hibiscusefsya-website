<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateKunjungansAddPromoAndItems extends Migration
{
    public function up()
    {
        // 1. Ubah enum tujuan untuk menambahkan Promo
        // Karena MySQL tidak bisa langsung alter enum, kita ubah ke string dulu
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->string('tujuan_new', 50)->nullable()->after('tujuan');
        });

        // Copy data lama
        DB::statement("UPDATE kunjungans SET tujuan_new = tujuan");

        // Drop kolom lama dan rename
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('tujuan');
        });

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->renameColumn('tujuan_new', 'tujuan');
        });

        // 2. Buat tabel kunjungan_items untuk produk
        Schema::create('kunjungan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->foreign('kunjungan_id')->references('id')->on('kunjungans')->onDelete('cascade');
            
            $table->unsignedBigInteger('produk_id');
            $table->foreign('produk_id')->references('id')->on('produks');
            
            $table->integer('jumlah')->default(1);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 3. Tambah kontak_id ke kunjungans untuk link ke kontak
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->unsignedBigInteger('kontak_id')->nullable()->after('gudang_id');
            $table->foreign('kontak_id')->references('id')->on('kontaks');
        });
    }

    public function down()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropForeign(['kontak_id']);
            $table->dropColumn('kontak_id');
        });

        Schema::dropIfExists('kunjungan_items');

        // Revert tujuan back to enum (tanpa Promo)
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->string('tujuan_old', 50)->nullable()->after('tujuan');
        });

        DB::statement("UPDATE kunjungans SET tujuan_old = tujuan WHERE tujuan IN ('Pemeriksaan Stock', 'Penagihan', 'Penawaran')");
        DB::statement("UPDATE kunjungans SET tujuan_old = 'Penawaran' WHERE tujuan = 'Promo'");

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('tujuan');
        });

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->renameColumn('tujuan_old', 'tujuan');
        });
    }
}
