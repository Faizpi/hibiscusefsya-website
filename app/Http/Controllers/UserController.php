<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Gudang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with('gudang')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $gudangs = Gudang::all();
        $roles = User::getAvailableRoles();
        return view('users.create', compact('gudangs', 'roles'));
    }

    public function store(Request $request)
    {

        // Tentukan role yang diperbolehkan berdasarkan user yang login
        $allowedRoles = array_keys(User::getAvailableRoles());

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($allowedRoles)],
            'alamat' => ['nullable', 'string'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'gudang_id' => $request->gudang_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        // Cegah admin biasa mengedit super_admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki izin untuk mengedit Super Admin.');
        }

        $gudangs = Gudang::all();
        $roles = User::getAvailableRoles();
        return view('users.edit', compact('user', 'gudangs', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Cegah admin biasa mengedit super_admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki izin untuk mengedit Super Admin.');
        }

        // Tentukan role yang diperbolehkan
        $allowedRoles = array_keys(User::getAvailableRoles());

        // Jika bukan super_admin dan mencoba mengubah role super_admin, pertahankan role asli
        $roleValidation = ['required', Rule::in($allowedRoles)];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => $roleValidation,
            'alamat' => ['nullable', 'string'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = $request->only('name', 'email', 'alamat', 'no_telp', 'gudang_id');

        // Hanya update role jika user punya izin
        if (in_array($request->role, $allowedRoles)) {
            $data['role'] = $request->role;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        // Cegah admin biasa menghapus super_admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki izin untuk menghapus Super Admin.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}