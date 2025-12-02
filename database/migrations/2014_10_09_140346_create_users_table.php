<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');

            // --- KOLOM BARU ---
            $table->string('alamat')->nullable();
            $table->string('no_telp')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable(); // User bisa 'tidak memegang' gudang (misal admin utama)
            $table->foreign('gudang_id')->references('id')->on('gudangs');
            // --------------------

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}