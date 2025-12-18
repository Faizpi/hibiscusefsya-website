<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUuidToTransactionsTables extends Migration
{
    /**
     * Run the migrations.
     * Security: Add UUID untuk public invoice agar ID tidak bisa ditebak
     *
     * @return void
     */
    public function up()
    {
        // Add UUID column to penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // Add UUID column to pembelians
        Schema::table('pembelians', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // Add UUID column to biayas
        Schema::table('biayas', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // Generate UUID untuk data yang sudah ada
        DB::table('penjualans')->whereNull('uuid')->get()->each(function ($item) {
            DB::table('penjualans')->where('id', $item->id)->update(['uuid' => (string) Str::uuid()]);
        });

        DB::table('pembelians')->whereNull('uuid')->get()->each(function ($item) {
            DB::table('pembelians')->where('id', $item->id)->update(['uuid' => (string) Str::uuid()]);
        });

        DB::table('biayas')->whereNull('uuid')->get()->each(function ($item) {
            DB::table('biayas')->where('id', $item->id)->update(['uuid' => (string) Str::uuid()]);
        });

        // Make UUID NOT NULL after generating for existing records
        Schema::table('penjualans', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });

        Schema::table('biayas', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
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
            $table->dropColumn('uuid');
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('biayas', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
