<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGudangIdToBiayasTable extends Migration
{
    public function up()
    {
        Schema::table('biayas', function (Blueprint $table) {
            $table->unsignedBigInteger('gudang_id')->nullable()->after('user_id');
            $table->foreign('gudang_id')->references('id')->on('gudangs')->onDelete('set null');
            $table->index('gudang_id');
        });
    }

    public function down()
    {
        Schema::table('biayas', function (Blueprint $table) {
            $table->dropForeign(['gudang_id']);
            $table->dropColumn('gudang_id');
        });
    }
}
