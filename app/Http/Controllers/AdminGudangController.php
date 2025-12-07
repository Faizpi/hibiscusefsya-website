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
    public function edit(User $admin)
    {
        // Check super admin
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($admin->role !== 'admin') {
            return back()->with('error', 'User ini bukan admin.');
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

        if ($admin->role !== 'admin') {
            return back()->with('error', 'User ini bukan admin.');
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
