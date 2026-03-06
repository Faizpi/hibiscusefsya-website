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
    Route::get('produk/stok/{gudangId}', 'ProdukController@stokByGudang');

    // Kontak
    Route::get('kontak', 'KontakController@index');
    Route::get('kontak/{id}', 'KontakController@show');
    Route::post('kontak', 'KontakController@store');
    Route::put('kontak/{id}', 'KontakController@update');

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

    // Kunjungan
    Route::get('kunjungan', 'KunjunganController@index');
    Route::get('kunjungan/{id}', 'KunjunganController@show');
    Route::post('kunjungan', 'KunjunganController@store');

    // Pembayaran
    Route::get('pembayaran', 'PembayaranController@index');
    Route::get('pembayaran/{id}', 'PembayaranController@show');
    Route::post('pembayaran', 'PembayaranController@store');
});
