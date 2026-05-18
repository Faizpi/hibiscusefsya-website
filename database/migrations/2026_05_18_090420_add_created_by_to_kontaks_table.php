<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCreatedByToKontaksTable extends Migration
{
    public function up()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('gudang_id');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Backfill: untuk kontak yang belum punya created_by,
        // cari user_id dari penjualan pertama yang pelanggannya cocok dengan nama kontak.
        DB::statement("
            UPDATE kontaks k
            SET k.created_by = (
                SELECT p.user_id
                FROM penjualans p
                WHERE p.pelanggan = k.nama
                ORDER BY p.created_at ASC
                LIMIT 1
            )
            WHERE k.created_by IS NULL
        ");
    }

    public function down()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
}
