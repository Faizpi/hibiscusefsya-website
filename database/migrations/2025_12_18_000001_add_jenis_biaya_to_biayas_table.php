<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisBiayaToBiayasTable extends Migration
{
    public function up()
    {
        Schema::table('biayas', function (Blueprint $table) {
            $table->enum('jenis_biaya', ['masuk', 'keluar'])->default('keluar')->after('no_urut_harian');
        });
    }

    public function down()
    {
        Schema::table('biayas', function (Blueprint $table) {
            $table->dropColumn('jenis_biaya');
        });
    }
}
