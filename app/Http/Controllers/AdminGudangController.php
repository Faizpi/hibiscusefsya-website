<?php

namespace App\Http\Controllers;

use App\User;
use App\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGudangController extends Controller
{
    /**
     * Tampilkan list admin dan gudang mereka (super admin only)
     */
    public function index()
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Ambil semua admin yang bukan super_admin
        $admins = User::where('role', 'admin')
            ->with('gudangs')
            ->get();

        $gudangs = Gudang::all();

        return view('admin-gudang.index', compact('admins', 'gudangs'));
    }

    /**
     * Edit gudang untuk admin tertentu
     */
    public function edit(Request $request, User $admin)
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Ensure we resolve the correct user from route param (avoid binding issues)
        $adminId = $request->route('admin_gudang');
        $admin = User::find($adminId);

        // Safety check - validate user exists and has a name
        if (!$admin || !$admin->id || !$admin->name) {
            return redirect()->route('admin-gudang.index')
                ->with('error', 'User tidak valid atau tidak ditemukan.');
        }

        // Check if user is admin (strict check)
        if ($admin->role !== 'admin') {
            return redirect()->route('admin-gudang.index')
                ->with('error', "Pengguna '{$admin->name}' (Role: {$admin->role}) bukan admin. Tidak bisa diubah gudangnya.");
        }

        $gudangs = Gudang::all();
        $assignedGudangs = $admin->gudangs()->pluck('gudangs.id')->toArray();

        return view('admin-gudang.edit', compact('admin', 'gudangs', 'assignedGudangs'));
    }

    /**
     * Update gudang untuk admin (assign/unassign)
     */
    public function update(Request $request, User $admin)
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Ensure we resolve the correct user from route param (avoid binding issues)
        $adminId = $request->route('admin_gudang');
        $admin = User::find($adminId);

        // Safety check - validate user exists and has a name
        if (!$admin || !$admin->id || !$admin->name) {
            return redirect()->route('admin-gudang.index')
                ->with('error', 'User tidak valid atau tidak ditemukan.');
        }

        // Check if user is admin (strict check)
        if ($admin->role !== 'admin') {
            return redirect()->route('admin-gudang.index')
                ->with('error', "Pengguna '{$admin->name}' (Role: {$admin->role}) bukan admin. Tidak bisa diubah gudangnya.");
        }

        // Validasi: admin minimal harus punya 1 gudang
        $request->validate([
            'gudangs' => 'required|array|min:1',
            'gudangs.*' => 'exists:gudangs,id',
        ]);

        // Update pivot table
        $admin->gudangs()->sync($request->gudangs);

        // Set current_gudang_id ke gudang pertama jika belum ada
        if (!$admin->current_gudang_id || !in_array($admin->current_gudang_id, $request->gudangs)) {
            $admin->current_gudang_id = $request->gudangs[0];
            $admin->save();
        }

        return redirect()->route('admin-gudang.index')
            ->with('success', 'Gudang admin berhasil diperbarui.');
    }

    /**
     * Admin switch gudang (via profile dropdown)
     */
    public function switchGudang(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return back()->with('error', 'Hanya admin yang bisa switch gudang.');
        }

        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
        ]);

        // Check jika admin bisa akses gudang ini
        if (!$user->canAccessGudang($request->gudang_id)) {
            return back()->with('error', 'Anda tidak diberi akses ke gudang ini.');
        }

        // Update current_gudang_id
        $user->current_gudang_id = $request->gudang_id;
        $user->save();

        return back()->with('success', 'Gudang aktif berhasil diubah.');
    }
}
