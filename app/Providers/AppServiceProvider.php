<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Carbon\Carbon;

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
                    $pendingPenjualan = Penjualan::with('user')->where('status', 'pending')->latest()->take(5)->get();
                    $pendingPembelian = Pembelian::with('user')->where('status', 'pending')->latest()->take(5)->get();
                    $pendingBiaya = Biaya::with('user')->where('status', 'pending')->latest()->take(5)->get();
                } else {
                    // Admin sees only their pending (as approver)
                    $pendingPenjualan = Penjualan::with('user')->where('status', 'pending')
                        ->where('approver_id', $user->id)
                        ->latest()->take(5)->get();
                    $pendingPembelian = Pembelian::with('user')->where('status', 'pending')
                        ->where('approver_id', $user->id)
                        ->latest()->take(5)->get();
                    $pendingBiaya = Biaya::with('user')->where('status', 'pending')
                        ->where('approver_id', $user->id)
                        ->latest()->take(5)->get();
                }

                // Helper to format nomor
                $formatNomor = function($item, $prefix) {
                    $date = $item->tgl_transaksi ? Carbon::parse($item->tgl_transaksi) : $item->created_at;
                    return sprintf('%s-%s%02d', $prefix, $date->format('ymd'), $item->no_urut_harian);
                };

                // Map to notifications
                foreach ($pendingPenjualan as $item) {
                    $pendingNotifications->push([
                        'type' => 'penjualan',
                        'icon' => 'fa-shopping-cart',
                        'color' => 'primary',
                        'title' => $formatNomor($item, 'PJ'),
                        'subtitle' => $item->pelanggan ?: ($item->user->name ?? '-'),
                        'amount' => $item->grand_total,
                        'url' => route('penjualan.show', $item->id),
                        'time' => $item->created_at,
                    ]);
                }

                foreach ($pendingPembelian as $item) {
                    $pendingNotifications->push([
                        'type' => 'pembelian',
                        'icon' => 'fa-truck',
                        'color' => 'success',
                        'title' => $formatNomor($item, 'PB'),
                        'subtitle' => $item->staf_penyetuju ?: ($item->user->name ?? '-'),
                        'amount' => $item->grand_total,
                        'url' => route('pembelian.show', $item->id),
                        'time' => $item->created_at,
                    ]);
                }

                foreach ($pendingBiaya as $item) {
                    $pendingNotifications->push([
                        'type' => 'biaya',
                        'icon' => 'fa-receipt',
                        'color' => 'warning',
                        'title' => $formatNomor($item, 'BY'),
                        'subtitle' => $item->penerima ?: ($item->user->name ?? '-'),
                        'amount' => $item->grand_total,
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

