<?php

namespace App\Http\Controllers;

use App\User;
use App\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpectatorGudangController extends Controller
{
    /**
     * Tampilkan list spectator dan gudang mereka (super admin only)
     */
    public function index()
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Ambil semua spectator
        $spectators = User::where('role', 'spectator')
            ->with('spectatorGudangs')
            ->get();

        $gudangs = Gudang::all();

        return view('spectator-gudang.index', compact('spectators', 'gudangs'));
    }

    /**
     * Edit gudang untuk spectator tertentu
     */
    public function edit(Request $request, User $spectator)
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Ensure we resolve the correct user from route param (avoid binding issues)
        $spectatorId = $request->route('spectator_gudang');
        $spectator = User::find($spectatorId);

        // Safety check - validate user exists and has a name
        if (!$spectator || !$spectator->id || !$spectator->name) {
            return redirect()->route('spectator-gudang.index')
                ->with('error', 'User tidak valid atau tidak ditemukan.');
        }

        // Check if user is spectator (strict check)
        if ($spectator->role !== 'spectator') {
            return redirect()->route('spectator-gudang.index')
                ->with('error', "Pengguna '{$spectator->name}' (Role: {$spectator->role}) bukan spectator. Tidak bisa diubah gudangnya.");
        }

        $gudangs = Gudang::all();
        $assignedGudangs = $spectator->spectatorGudangs()->pluck('gudangs.id')->toArray();

        return view('spectator-gudang.edit', compact('spectator', 'gudangs', 'assignedGudangs'));
    }

    /**
     * Update gudang untuk spectator (assign/unassign)
     */
    public function update(Request $request, User $spectator)
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Ensure we resolve the correct user from route param (avoid binding issues)
        $spectatorId = $request->route('spectator_gudang');
        $spectator = User::find($spectatorId);

        // Safety check - validate user exists and has a name
        if (!$spectator || !$spectator->id || !$spectator->name) {
            return redirect()->route('spectator-gudang.index')
                ->with('error', 'User tidak valid atau tidak ditemukan.');
        }

        // Check if user is spectator (strict check)
        if ($spectator->role !== 'spectator') {
            return redirect()->route('spectator-gudang.index')
                ->with('error', "Pengguna '{$spectator->name}' (Role: {$spectator->role}) bukan spectator. Tidak bisa diubah gudangnya.");
        }

        // Validasi: spectator minimal harus punya 1 gudang
        $request->validate([
            'gudangs' => 'required|array|min:1',
            'gudangs.*' => 'exists:gudangs,id',
        ]);

        // Update pivot table
        $spectator->spectatorGudangs()->sync($request->gudangs);

        // Set current_gudang_id ke gudang pertama jika belum ada
        if (!$spectator->current_gudang_id || !in_array($spectator->current_gudang_id, $request->gudangs)) {
            $spectator->current_gudang_id = $request->gudangs[0];
            $spectator->save();
        }

        return redirect()->route('spectator-gudang.index')
            ->with('success', 'Gudang spectator berhasil diperbarui.');
    }
}
