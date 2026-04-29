<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEmailToNoTeleponInTransactions extends Migration
{
    public function up()
    {
        // Rename email -> no_telepon di tabel penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            $table->renameColumn('email', 'no_telepon');
        });

        // Rename sales_email -> sales_no_telepon di tabel kunjungans
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->renameColumn('sales_email', 'sales_no_telepon');
        });
    }

    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->renameColumn('no_telepon', 'email');
        });

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->renameColumn('sales_no_telepon', 'sales_email');
        });
    }
}
