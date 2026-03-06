<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ========================================================================
// MOBILE API v1 - Token-based Authentication
// ========================================================================

// Public (no auth)
Route::prefix('v1')->namespace('Api')->group(function () {
    Route::post('login', 'AuthController@login');
});

// Protected (require token)
Route::prefix('v1')->namespace('Api')->middleware('api.token')->group(function () {

    // Auth
    Route::post('logout', 'AuthController@logout');
    Route::get('profile', 'AuthController@profile');
    Route::put('profile', 'AuthController@updateProfile');
    Route::post('change-password', 'AuthController@changePassword');

    // Dashboard
    Route::get('dashboard', 'DashboardController@index');

    // Gudang
    Route::get('gudang', 'GudangController@index');
    Route::post('gudang/switch', 'GudangController@switchGudang');
    Route::get('gudang/stok', 'GudangController@stok');
    Route::get('gudang/stok-log', 'GudangController@stokLog');

    // Produk
    Route::get('produk', 'ProdukController@index');
    Route::get('produk/{id}', 'ProdukController@show');
    Route::post('produk', 'ProdukController@store');
    Route::put('produk/{id}', 'ProdukController@update');
    Route::delete('produk/{id}', 'ProdukController@destroy');
    Route::get('produk/stok/{gudangId}', 'ProdukController@stokByGudang');

    // Kontak
    Route::get('kontak', 'KontakController@index');
    Route::get('kontak/{id}', 'KontakController@show');
    Route::post('kontak', 'KontakController@store');
    Route::put('kontak/{id}', 'KontakController@update');
    Route::delete('kontak/{id}', 'KontakController@destroy');

    // Penjualan
    Route::get('penjualan', 'PenjualanController@index');
    Route::get('penjualan/{id}', 'PenjualanController@show');
    Route::post('penjualan', 'PenjualanController@store');
    Route::post('penjualan/{id}/approve', 'PenjualanController@approve');
    Route::post('penjualan/{id}/cancel', 'PenjualanController@cancel');

    // Pembelian
    Route::get('pembelian', 'PembelianController@index');
    Route::get('pembelian/{id}', 'PembelianController@show');
    Route::post('pembelian', 'PembelianController@store');
    Route::post('pembelian/{id}/approve', 'PembelianController@approve');
    Route::post('pembelian/{id}/cancel', 'PembelianController@cancel');

    // Biaya
    Route::get('biaya', 'BiayaController@index');
    Route::get('biaya/{id}', 'BiayaController@show');
    Route::post('biaya', 'BiayaController@store');
    Route::post('biaya/{id}/approve', 'BiayaController@approve');
    Route::post('biaya/{id}/cancel', 'BiayaController@cancel');

    // Kunjungan
    Route::get('kunjungan', 'KunjunganController@index');
    Route::get('kunjungan/{id}', 'KunjunganController@show');
    Route::post('kunjungan', 'KunjunganController@store');
    Route::post('kunjungan/{id}/approve', 'KunjunganController@approve');
    Route::post('kunjungan/{id}/cancel', 'KunjunganController@cancel');

    // Pembayaran
    Route::get('pembayaran', 'PembayaranController@index');
    Route::get('pembayaran/{id}', 'PembayaranController@show');
    Route::post('pembayaran', 'PembayaranController@store');
    Route::post('pembayaran/{id}/approve', 'PembayaranController@approve');
    Route::post('pembayaran/{id}/cancel', 'PembayaranController@cancel');

    // Penerimaan Barang
    Route::get('penerimaan-barang', 'PenerimaanBarangController@index');
    Route::get('penerimaan-barang/{id}', 'PenerimaanBarangController@show');
    Route::post('penerimaan-barang', 'PenerimaanBarangController@store');
    Route::post('penerimaan-barang/{id}/approve', 'PenerimaanBarangController@approve');
    Route::post('penerimaan-barang/{id}/cancel', 'PenerimaanBarangController@cancel');
    Route::get('penerimaan-barang/pembelian-by-gudang/{gudangId}', 'PenerimaanBarangController@getPembelianByGudang');
    Route::get('penerimaan-barang/pembelian-detail/{id}', 'PenerimaanBarangController@getPembelianDetail');

    // Stok (Admin/Super Admin)
    Route::get('stok', 'StokController@index');
    Route::post('stok', 'StokController@store');
    Route::get('stok/log', 'StokController@log');

    // User Management (Super Admin)
    Route::get('users', 'UserController@index');
    Route::get('users/{id}', 'UserController@show');
    Route::post('users', 'UserController@store');
    Route::put('users/{id}', 'UserController@update');
    Route::delete('users/{id}', 'UserController@destroy');
});
