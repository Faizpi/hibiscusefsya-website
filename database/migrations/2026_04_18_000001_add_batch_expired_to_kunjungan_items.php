<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchExpiredToKunjunganItems extends Migration
{
    public function up()
    {
        Schema::table('kunjungan_items', function (Blueprint $table) {
            $table->string('batch_number', 100)->nullable()->after('jumlah');
            $table->date('expired_date')->nullable()->after('batch_number');
        });
    }

    public function down()
    {
        Schema::table('kunjungan_items', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'expired_date']);
        });
    }
}
