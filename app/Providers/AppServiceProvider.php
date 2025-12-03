<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share pending notifications count to all views
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $pendingNotifications = collect();

                if ($user->role === 'super_admin') {
                    // Super admin sees all pending
                    $pendingPenjualan = Penjualan::where('status', 'pending')->latest()->take(5)->get();
                    $pendingPembelian = Pembelian::where('status', 'pending')->latest()->take(5)->get();
                    $pendingBiaya = Biaya::where('status', 'pending')->latest()->take(5)->get();
                } else {
                    // Admin sees only their pending (as approver)
                    $pendingPenjualan = Penjualan::where('status', 'pending')
                        ->where('approver_id', $user->id)
                        ->latest()->take(5)->get();
                    $pendingPembelian = Pembelian::where('status', 'pending')
                        ->where('approver_id', $user->id)
                        ->latest()->take(5)->get();
                    $pendingBiaya = Biaya::where('status', 'pending')
                        ->where('approver_id', $user->id)
                        ->latest()->take(5)->get();
                }

                // Map to notifications
                foreach ($pendingPenjualan as $item) {
                    $pendingNotifications->push([
                        'type' => 'penjualan',
                        'icon' => 'fa-shopping-cart',
                        'color' => 'primary',
                        'title' => 'Penjualan #' . $item->nomor,
                        'subtitle' => $item->kontak->nama ?? 'N/A',
                        'url' => route('penjualan.show', $item->id),
                        'time' => $item->created_at,
                    ]);
                }

                foreach ($pendingPembelian as $item) {
                    $pendingNotifications->push([
                        'type' => 'pembelian',
                        'icon' => 'fa-truck',
                        'color' => 'success',
                        'title' => 'Pembelian #' . $item->nomor,
                        'subtitle' => $item->kontak->nama ?? 'N/A',
                        'url' => route('pembelian.show', $item->id),
                        'time' => $item->created_at,
                    ]);
                }

                foreach ($pendingBiaya as $item) {
                    $pendingNotifications->push([
                        'type' => 'biaya',
                        'icon' => 'fa-file-invoice-dollar',
                        'color' => 'warning',
                        'title' => 'Biaya #' . $item->nomor,
                        'subtitle' => $item->nama_biaya ?? 'N/A',
                        'url' => route('biaya.show', $item->id),
                        'time' => $item->created_at,
                    ]);
                }

                // Sort by time desc and take 10
                $pendingNotifications = $pendingNotifications->sortByDesc('time')->take(10);

                // Count totals
                if ($user->role === 'super_admin') {
                    $totalPending = Penjualan::where('status', 'pending')->count()
                        + Pembelian::where('status', 'pending')->count()
                        + Biaya::where('status', 'pending')->count();
                } else {
                    $totalPending = Penjualan::where('status', 'pending')->where('approver_id', $user->id)->count()
                        + Pembelian::where('status', 'pending')->where('approver_id', $user->id)->count()
                        + Biaya::where('status', 'pending')->where('approver_id', $user->id)->count();
                }

                $view->with('pendingNotifications', $pendingNotifications);
                $view->with('totalPending', $totalPending);
            }
        });
    }
}

