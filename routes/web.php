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
// PUBLIC ROUTES (Tanpa Login) - Untuk QR Code Invoice
// Security: Menggunakan UUID bukan ID untuk prevent enumeration attack
// ========================================================================
Route::prefix('invoice')->name('public.invoice.')->group(function () {
    // Penjualan
    Route::get('penjualan/{uuid}', 'PublicInvoiceController@showPenjualan')->name('penjualan');
    Route::get('penjualan/{uuid}/download', 'PublicInvoiceController@downloadPenjualan')->name('penjualan.download');

    // Pembelian
    Route::get('pembelian/{uuid}', 'PublicInvoiceController@showPembelian')->name('pembelian');
    Route::get('pembelian/{uuid}/download', 'PublicInvoiceController@downloadPembelian')->name('pembelian.download');

    // Biaya
    Route::get('biaya/{uuid}', 'PublicInvoiceController@showBiaya')->name('biaya');
    Route::get('biaya/{uuid}/download', 'PublicInvoiceController@downloadBiaya')->name('biaya.download');

    // Kunjungan
    Route::get('kunjungan/{uuid}', 'PublicInvoiceController@showKunjungan')->name('kunjungan');
    Route::get('kunjungan/{uuid}/download', 'PublicInvoiceController@downloadKunjungan')->name('kunjungan.download');
});

// ========================================================================
// GRUP 1: User yang Sudah Login (Semua Role)
// ========================================================================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('/home', 'DashboardController@index')->name('home');

    // --- BLUETOOTH PRINT JSON API ---
    Route::get('bluetooth/penjualan/{id}', 'BluetoothPrintController@penjualanJson')->name('bluetooth.penjualan');
    Route::get('bluetooth/pembelian/{id}', 'BluetoothPrintController@pembelianJson')->name('bluetooth.pembelian');
    Route::get('bluetooth/biaya/{id}', 'BluetoothPrintController@biayaJson')->name('bluetooth.biaya');
    Route::get('bluetooth/kunjungan/{id}', 'BluetoothPrintController@kunjunganJson')->name('bluetooth.kunjungan');

    // --- TRANSAKSI (CRUD & PRINT) ---

    // 1. Modul Penjualan
    Route::get('penjualan/{penjualan}/print', 'PenjualanController@print')->name('penjualan.print');
    Route::get('penjualan/{penjualan}/print-json', 'PenjualanController@printJson')->name('penjualan.printJson');
    Route::get('penjualan/{penjualan}/print-rich', 'PrintController@penjualanRichText')->name('penjualan.printRich');
    Route::get('penjualan/{penjualan}/struk-image', 'PrintImageController@penjualan')->name('penjualan.strukImage');
    Route::post('penjualan/{penjualan}/approve', 'PenjualanController@approve')->name('penjualan.approve');
    Route::post('penjualan/{penjualan}/cancel', 'PenjualanController@cancel')->name('penjualan.cancel');
    Route::post('penjualan/{penjualan}/mark-paid', 'PenjualanController@markAsPaid')->name('penjualan.markAsPaid');
    Route::resource('penjualan', 'PenjualanController');

    // 2. Modul Pembelian
    Route::get('pembelian/{pembelian}/print', 'PembelianController@print')->name('pembelian.print');
    Route::get('pembelian/{pembelian}/print-json', 'PembelianController@printJson')->name('pembelian.printJson');
    Route::get('pembelian/{pembelian}/print-rich', 'PrintController@pembelianRichText')->name('pembelian.printRich');
    Route::get('pembelian/{pembelian}/struk-image', 'PrintImageController@pembelian')->name('pembelian.strukImage');
    Route::post('pembelian/{pembelian}/approve', 'PembelianController@approve')->name('pembelian.approve');
    Route::post('pembelian/{pembelian}/cancel', 'PembelianController@cancel')->name('pembelian.cancel');
    Route::resource('pembelian', 'PembelianController');

    // 3. Modul Biaya
    Route::get('biaya/{biaya}/print', 'BiayaController@print')->name('biaya.print');
    Route::get('biaya/{biaya}/print-json', 'BiayaController@printJson')->name('biaya.printJson');
    Route::get('biaya/{biaya}/print-rich', 'PrintController@biayaRichText')->name('biaya.printRich');
    Route::get('biaya/{biaya}/struk-image', 'PrintImageController@biaya')->name('biaya.strukImage');
    Route::post('biaya/{biaya}/approve', 'BiayaController@approve')->name('biaya.approve');
    Route::post('biaya/{biaya}/cancel', 'BiayaController@cancel')->name('biaya.cancel');
    Route::resource('biaya', 'BiayaController');

    // 4. Modul Kunjungan
    Route::get('kunjungan/{kunjungan}/print', 'KunjunganController@print')->name('kunjungan.print');
    Route::get('kunjungan/{kunjungan}/print-json', 'KunjunganController@printJson')->name('kunjungan.printJson');
    Route::post('kunjungan/{kunjungan}/approve', 'KunjunganController@approve')->name('kunjungan.approve');
    Route::post('kunjungan/{kunjungan}/cancel', 'KunjunganController@cancel')->name('kunjungan.cancel');
    Route::resource('kunjungan', 'KunjunganController');


    // ====================================================================
    // GRUP 2: Area Admin & Super Admin
    // ====================================================================
    Route::middleware(['role:admin'])->group(function () {

        // Master Kontak
        Route::get('kontak/{kontak}/print', 'KontakController@print')->name('kontak.print');
        Route::get('kontak/{kontak}/download', 'KontakController@downloadPdf')->name('kontak.download');
        Route::resource('kontak', 'KontakController');

        // Cek Stok
        Route::get('stok', 'StokController@index')->name('stok.index');
        Route::post('stok/export', 'StokController@exportStok')->name('stok.export');
        Route::get('stok/log', 'StokController@log')->name('stok.log');

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

        // Manage gudang per spectator
        Route::resource('spectator-gudang', 'SpectatorGudangController');

        // Master Data Inti
        Route::resource('gudang', 'GudangController');
        Route::get('produk/{produk}/print', 'ProdukController@print')->name('produk.print');
        Route::get('produk/{produk}/download', 'ProdukController@downloadPdf')->name('produk.download');
        Route::resource('produk', 'ProdukController');

        // Edit Stok Manual
        Route::post('stok', 'StokController@store')->name('stok.store');
    });

});