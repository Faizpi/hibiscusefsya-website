<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddKodeKontakToKontaksTable extends Migration
{
    public function up()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->string('kode_kontak', 20)->nullable()->unique()->after('id');
        });

        // Generate kode_kontak untuk kontak yang sudah ada
        $kontaks = DB::table('kontaks')->get();
        foreach ($kontaks as $kontak) {
            $kode = 'KT' . str_pad($kontak->id, 5, '0', STR_PAD_LEFT);
            DB::table('kontaks')->where('id', $kontak->id)->update(['kode_kontak' => $kode]);
        }
    }

    public function down()
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->dropColumn('kode_kontak');
        });
    }
}
