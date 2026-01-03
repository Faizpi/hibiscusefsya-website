<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLampiranPathsToTransactionsTables extends Migration
{
    /**
     * Run the migrations.
     * Adds lampiran_paths (JSON array) column to support multiple attachments
     * 
     * @return void
     */
    public function up()
    {
        // Penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            $table->json('lampiran_paths')->nullable()->after('lampiran_path');
        });

        // Pembelians
        Schema::table('pembelians', function (Blueprint $table) {
            $table->json('lampiran_paths')->nullable()->after('lampiran_path');
        });

        // Biayas
        Schema::table('biayas', function (Blueprint $table) {
            $table->json('lampiran_paths')->nullable()->after('lampiran_path');
        });

        // Kunjungans
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->json('lampiran_paths')->nullable()->after('lampiran_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn('lampiran_paths');
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('lampiran_paths');
        });

        Schema::table('biayas', function (Blueprint $table) {
            $table->dropColumn('lampiran_paths');
        });

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('lampiran_paths');
        });
    }
}
