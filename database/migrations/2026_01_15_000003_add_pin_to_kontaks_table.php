<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPinToKontaksTable extends Migration
{
    public function up()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->string('pin', 6)->nullable()->after('no_telp');
        });
    }

    public function down()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
}
