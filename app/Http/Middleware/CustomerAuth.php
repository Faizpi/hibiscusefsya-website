<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     * Cek apakah customer sudah login via session.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session('customer_id') || !session('customer_no_telp')) {
            return redirect()->route('customer.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek apakah kontak masih ada di database
        $kontak = \App\Kontak::find(session('customer_id'));
        if (!$kontak) {
            session()->forget(['customer_id', 'customer_no_telp', 'customer_nama']);
            return redirect()->route('customer.login')->with('error', 'Akun tidak ditemukan.');
        }

        // Share kontak ke semua view
        view()->share('customerKontak', $kontak);

        return $next($request);
    }
}
