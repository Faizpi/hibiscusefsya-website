<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $userRole = Auth::user()->role;

        // 1. Jika user adalah SUPER ADMIN, izinkan akses ke SEMUA rute
        if ($userRole == 'super_admin') {
            return $next($request);
        }

        // 2. Jika user adalah ADMIN, izinkan akses ke rute 'admin'
        if ($userRole == 'admin' && $role == 'admin') {
            return $next($request);
        }

        // 3. Jika user adalah SPECTATOR, izinkan akses ke rute 'admin' (read-only)
        if ($userRole == 'spectator' && $role == 'admin') {
            return $next($request);
        }

        // 4. Jika role cocok persis
        if ($userRole == $role) {
            return $next($request);
        }

        // Jika tidak cocok, kembalikan ke dashboard dengan pesan error
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}