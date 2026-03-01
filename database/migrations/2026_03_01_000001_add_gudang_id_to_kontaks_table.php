<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGudangIdToKontaksTable extends Migration
{
    public function up()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->unsignedBigInteger('gudang_id')->nullable()->after('diskon_persen');
            $table->foreign('gudang_id')->references('id')->on('gudangs')->onDelete('set null');
            $table->index('gudang_id');
        });
    }

    public function down()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->dropForeign(['gudang_id']);
            $table->dropColumn('gudang_id');
        });
    }
}
