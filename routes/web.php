<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes (Laravel 7 Style)
|--------------------------------------------------------------------------
*/

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rute Autentikasi
Auth::routes();

// ========================================================================
// GRUP 1: User yang Sudah Login (Semua Role)
// ========================================================================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('/home', 'DashboardController@index')->name('home');

    // --- TRANSAKSI (CRUD & PRINT) ---

    // 1. Modul Penjualan
    Route::get('penjualan/{penjualan}/print', 'PenjualanController@print')->name('penjualan.print');
    Route::post('penjualan/{penjualan}/approve', 'PenjualanController@approve')->name('penjualan.approve');
    Route::post('penjualan/{penjualan}/cancel', 'PenjualanController@cancel')->name('penjualan.cancel');
    Route::post('penjualan/{penjualan}/mark-paid', 'PenjualanController@markAsPaid')->name('penjualan.markAsPaid');
    Route::resource('penjualan', 'PenjualanController');

    // 2. Modul Pembelian
    Route::get('pembelian/{pembelian}/print', 'PembelianController@print')->name('pembelian.print');
    Route::post('pembelian/{pembelian}/approve', 'PembelianController@approve')->name('pembelian.approve');
    Route::post('pembelian/{pembelian}/cancel', 'PembelianController@cancel')->name('pembelian.cancel');
    Route::resource('pembelian', 'PembelianController');

    // 3. Modul Biaya
    Route::get('biaya/{biaya}/print', 'BiayaController@print')->name('biaya.print');
    Route::post('biaya/{biaya}/approve', 'BiayaController@approve')->name('biaya.approve');
    Route::post('biaya/{biaya}/cancel', 'BiayaController@cancel')->name('biaya.cancel');
    Route::resource('biaya', 'BiayaController');


    // ====================================================================
    // GRUP 2: Area Admin & Super Admin
    // ====================================================================
    Route::middleware(['role:admin'])->group(function () {
        
        // Master Kontak
        Route::resource('kontak', 'KontakController');

        // Cek Stok
        Route::get('stok', 'StokController@index')->name('stok.index');

        // Export Excel
        Route::get('/report/export', 'DashboardController@export')->name('report.export');
    });


    // ====================================================================
    // GRUP 3: Area Khusus Super Admin
    // ====================================================================
    Route::middleware(['role:super_admin'])->group(function () {
        
        // Manajemen User & Role
        Route::resource('users', 'UserController');

        // Master Data Inti
        Route::resource('gudang', 'GudangController');
        Route::resource('produk', 'ProdukController');

        // Edit Stok Manual
        Route::post('stok', 'StokController@store')->name('stok.store');
    });

});