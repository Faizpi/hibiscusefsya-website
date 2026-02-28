<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerSubdomainRedirect
{
    /**
     * Jika request datang dari customer.hibiscusefsya.com dan path bukan /customer/*,
     * redirect ke /customer supaya pakai prefix routes yang sudah ada.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Cek apakah request dari subdomain customer
        if (strpos($host, 'customer') !== false && !$request->is('customer*')) {
            $path = $request->getPathInfo();

            // Root atau admin login → redirect ke customer portal
            if ($path === '/' || $path === '/login' || $path === '/register') {
                return redirect('/customer');
            }

            // Path lain (misal /home) → redirect ke customer dashboard
            return redirect('/customer');
        }

        return $next($request);
    }
}
