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
    Route::get('penjualan/{penjualan}/print-json', 'PenjualanController@printJson')->name('penjualan.printJson');
    Route::get('penjualan/{penjualan}/struk-image', 'PrintImageController@penjualan')->name('penjualan.strukImage');
    Route::post('penjualan/{penjualan}/approve', 'PenjualanController@approve')->name('penjualan.approve');
    Route::post('penjualan/{penjualan}/cancel', 'PenjualanController@cancel')->name('penjualan.cancel');
    Route::post('penjualan/{penjualan}/mark-paid', 'PenjualanController@markAsPaid')->name('penjualan.markAsPaid');
    Route::resource('penjualan', 'PenjualanController');

    // 2. Modul Pembelian
    Route::get('pembelian/{pembelian}/print', 'PembelianController@print')->name('pembelian.print');
    Route::get('pembelian/{pembelian}/print-json', 'PembelianController@printJson')->name('pembelian.printJson');
    Route::get('pembelian/{pembelian}/struk-image', 'PrintImageController@pembelian')->name('pembelian.strukImage');
    Route::post('pembelian/{pembelian}/approve', 'PembelianController@approve')->name('pembelian.approve');
    Route::post('pembelian/{pembelian}/cancel', 'PembelianController@cancel')->name('pembelian.cancel');
    Route::resource('pembelian', 'PembelianController');

    // 3. Modul Biaya
    Route::get('biaya/{biaya}/print', 'BiayaController@print')->name('biaya.print');
    Route::get('biaya/{biaya}/print-json', 'BiayaController@printJson')->name('biaya.printJson');
    Route::get('biaya/{biaya}/struk-image', 'PrintImageController@biaya')->name('biaya.strukImage');
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
        Route::post('stok/export', 'StokController@exportStok')->name('stok.export');

        // Export Excel
        Route::get('/report/export', 'DashboardController@export')->name('report.export');

        // Switch gudang (admin dapat akses multiple gudang)
        Route::post('admin/switch-gudang', 'AdminGudangController@switchGudang')->name('admin.switchGudang');
    });


    // ====================================================================
    // GRUP 3: Area Khusus Super Admin
    // ====================================================================
    Route::middleware(['role:super_admin'])->group(function () {

        // Manajemen User & Role
        Route::resource('users', 'UserController');

        // Manage gudang per admin
        Route::resource('admin-gudang', 'AdminGudangController');

        // Master Data Inti
        Route::resource('gudang', 'GudangController');
        Route::resource('produk', 'ProdukController');

        // Edit Stok Manual
        Route::post('stok', 'StokController@store')->name('stok.store');
    });

});