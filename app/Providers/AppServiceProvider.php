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

                // Helper to format nomor (same format as controllers)
                $formatNomor = function ($item, $prefix) {
                    $dateCode = $item->created_at->format('Ymd');
                    $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
                    return "{$prefix}-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
                };

                // Map to notifications
                foreach ($pendingPenjualan as $item) {
                    $pendingNotifications->push([
                        'type' => 'penjualan',
                        'icon' => 'fa-shopping-cart',
                        'color' => 'primary',
                        'title' => $formatNomor($item, 'INV'),
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
                        'title' => $formatNomor($item, 'PR'),
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
                        'title' => $formatNomor($item, 'EXP'),
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

