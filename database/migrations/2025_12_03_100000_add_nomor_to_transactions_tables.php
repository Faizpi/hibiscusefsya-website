<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomorToTransactionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add nomor to penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('nomor')->nullable()->after('no_urut_harian');
            $table->index('nomor');
        });

        // Add nomor to pembelians
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('nomor')->nullable()->after('no_urut_harian');
            $table->index('nomor');
        });

        // Add nomor to biayas
        Schema::table('biayas', function (Blueprint $table) {
            $table->string('nomor')->nullable()->after('no_urut_harian');
            $table->index('nomor');
        });

        // Update existing records with generated nomor
        $this->updateExistingRecords();
    }

    /**
     * Update existing records with generated nomor
     */
    private function updateExistingRecords()
    {
        // Update penjualans
        $penjualans = \App\Penjualan::whereNull('nomor')->get();
        foreach ($penjualans as $item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->nomor = "INV-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            $item->save();
        }

        // Update pembelians
        $pembelians = \App\Pembelian::whereNull('nomor')->get();
        foreach ($pembelians as $item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->nomor = "PR-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            $item->save();
        }

        // Update biayas
        $biayas = \App\Biaya::whereNull('nomor')->get();
        foreach ($biayas as $item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->nomor = "EXP-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            $item->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropIndex(['nomor']);
            $table->dropColumn('nomor');
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropIndex(['nomor']);
            $table->dropColumn('nomor');
        });

        Schema::table('biayas', function (Blueprint $table) {
            $table->dropIndex(['nomor']);
            $table->dropColumn('nomor');
        });
    }
}
