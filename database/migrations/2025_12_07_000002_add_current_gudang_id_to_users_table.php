<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentGudangIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Gudang yang sedang aktif (untuk admin yang handle multiple gudang)
            // Jika user hanya punya 1 gudang, ini auto-set ke gudang itu
            // Jika user punya multiple gudang, user bisa switch via dropdown
            $table->unsignedBigInteger('current_gudang_id')->nullable()->after('gudang_id');
            $table->foreign('current_gudang_id')->references('id')->on('gudangs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_gudang_id']);
            $table->dropColumn('current_gudang_id');
        });
    }
}
