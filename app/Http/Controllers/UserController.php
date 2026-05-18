<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Gudang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with('gudang', 'gudangs', 'spectatorGudangs');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $users */
        $users = $query->orderByRaw("FIELD(role, 'super_admin','spectator','admin','user')")
            ->orderBy('name')
            ->paginate(12);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $gudangs = Gudang::all();
        $roles = User::getAvailableRoles();
        $exportPermissions = [
            'can_export_pdf' => false,
            'can_export_excel' => false,
        ];

        return view('users.create', compact('gudangs', 'roles', 'exportPermissions'));
    }

    public function store(Request $request)
    {

        // Tentukan role yang diperbolehkan berdasarkan user yang login
        $allowedRoles = array_keys(User::getAvailableRoles());

        // For admin/spectator: gudangs array is required, for user: gudang_id is required
        if ($request->role === 'admin' || $request->role === 'spectator') {
            $validationRules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', Rule::in($allowedRoles)],
                'alamat' => ['nullable', 'string'],
                'no_telp' => ['nullable', 'string', 'max:20'],
                'gudangs' => ['required', 'array', 'min:1'],
                'gudangs.*' => ['exists:gudangs,id'],
                'can_export_pdf' => ['nullable', 'boolean'],
                'can_export_excel' => ['nullable', 'boolean'],
            ];
            $messages = [
                'gudangs.required' => 'Pilih minimal satu gudang untuk ' . ucfirst($request->role) . '.',
                'gudangs.min' => 'Pilih minimal satu gudang untuk ' . ucfirst($request->role) . '.',
            ];
        } elseif ($request->role === 'user') {
            $validationRules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', Rule::in($allowedRoles)],
                'alamat' => ['nullable', 'string'],
                'no_telp' => ['nullable', 'string', 'max:20'],
                'gudang_id' => ['required', 'exists:gudangs,id'],
            ];
            $messages = [
                'gudang_id.required' => 'Gudang wajib dipilih untuk role User.',
            ];
        } else {
            // Super admin - no gudang required
            $validationRules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', Rule::in($allowedRoles)],
                'alamat' => ['nullable', 'string'],
                'no_telp' => ['nullable', 'string', 'max:20'],
                'gudang_id' => ['nullable', 'exists:gudangs,id'],
            ];
            $messages = [];
        }

        $request->validate($validationRules, $messages);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'gudang_id' => ($request->role === 'user') ? $request->gudang_id : null,
            'can_export_pdf' => $request->role === 'admin' ? $request->boolean('can_export_pdf') : false,
            'can_export_excel' => $request->role === 'admin' ? $request->boolean('can_export_excel') : false,
        ]);

        // Jika role admin, sync gudang ke pivot table admin_gudang
        if ($request->role === 'admin' && $request->gudangs) {
            $user->gudangs()->sync($request->gudangs);
            // Set current_gudang_id ke gudang pertama
            $user->current_gudang_id = $request->gudangs[0];
            $user->save();
        }
        // Jika role spectator, sync gudang ke pivot table spectator_gudang
        elseif ($request->role === 'spectator' && $request->gudangs) {
            $user->spectatorGudangs()->sync($request->gudangs);
            // Set current_gudang_id ke gudang pertama
            $user->current_gudang_id = $request->gudangs[0];
            $user->save();
        }

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
        $exportPermissions = [
            'can_export_pdf' => $user->can_export_pdf,
            'can_export_excel' => $user->can_export_excel,
        ];

        return view('users.edit', compact('user', 'gudangs', 'roles', 'exportPermissions'));
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

        // Validation rules based on role
        if ($request->role === 'admin' || $request->role === 'spectator') {
            // Multi-gudang validation
            $validationRules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'role' => $roleValidation,
                'alamat' => ['nullable', 'string'],
                'no_telp' => ['nullable', 'string', 'max:20'],
                'gudangs' => ['required', 'array', 'min:1'],
                'gudangs.*' => ['exists:gudangs,id'],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
                'can_export_pdf' => ['nullable', 'boolean'],
                'can_export_excel' => ['nullable', 'boolean'],
            ];
            $messages = [
                'gudangs.required' => 'Pilih minimal satu gudang untuk role ' . ucfirst($request->role) . '.',
                'gudangs.min' => 'Pilih minimal satu gudang untuk role ' . ucfirst($request->role) . '.',
            ];
        } elseif ($request->role === 'user') {
            // Single gudang validation for user
            $validationRules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'role' => $roleValidation,
                'alamat' => ['nullable', 'string'],
                'no_telp' => ['nullable', 'string', 'max:20'],
                'gudang_id' => ['required', 'exists:gudangs,id'],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ];
            $messages = [
                'gudang_id.required' => 'Gudang wajib dipilih untuk role User.',
            ];
        } else {
            // Super admin - no gudang required
            $validationRules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'role' => $roleValidation,
                'alamat' => ['nullable', 'string'],
                'no_telp' => ['nullable', 'string', 'max:20'],
                'gudang_id' => ['nullable', 'exists:gudangs,id'],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ];
            $messages = [];
        }

        $request->validate($validationRules, $messages);

        $data = $request->only('name', 'email', 'alamat', 'no_telp');

        // Set export permissions for admin role (only super_admin can set these)
        if ($request->role === 'admin' && auth()->user()->isSuperAdmin()) {
            $data['can_export_pdf'] = $request->boolean('can_export_pdf');
            $data['can_export_excel'] = $request->boolean('can_export_excel');
        } elseif ($request->role !== 'admin') {
            // Reset export permissions if role is not admin
            $data['can_export_pdf'] = false;
            $data['can_export_excel'] = false;
        }

        // Set gudang_id for user role, null for admin/spectator (they use pivot tables)
        if ($request->role === 'user') {
            $data['gudang_id'] = $request->gudang_id;
        } elseif ($request->role === 'admin' || $request->role === 'spectator') {
            $data['gudang_id'] = null;
        }

        // Hanya update role jika user punya izin
        if (in_array($request->role, $allowedRoles)) {
            $data['role'] = $request->role;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Handle pivot table sync based on role change
        $oldRole = $user->getOriginal('role') ?? $user->role;
        $newRole = $request->role;

        // Clear old pivot tables if role changed
        if ($oldRole !== $newRole) {
            if ($oldRole === 'admin') {
                $user->gudangs()->detach();
            } elseif ($oldRole === 'spectator') {
                $user->spectatorGudangs()->detach();
            }
        }

        // Sync new gudang assignment
        if ($newRole === 'admin' && $request->gudangs) {
            $user->gudangs()->sync($request->gudangs);
            // Set current_gudang_id if not set or not valid
            if (!$user->current_gudang_id || !in_array($user->current_gudang_id, $request->gudangs)) {
                $user->current_gudang_id = $request->gudangs[0];
                $user->save();
            }
        } elseif ($newRole === 'spectator' && $request->gudangs) {
            $user->spectatorGudangs()->sync($request->gudangs);
            // Set current_gudang_id if not set or not valid
            if (!$user->current_gudang_id || !in_array($user->current_gudang_id, $request->gudangs)) {
                $user->current_gudang_id = $request->gudangs[0];
                $user->save();
            }
        } elseif ($newRole === 'user' && $request->gudang_id) {
            // Clear any pivot table entries
            $user->gudangs()->detach();
            $user->spectatorGudangs()->detach();
            $user->current_gudang_id = null;
            $user->save();
        }

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

        // Cek apakah user punya transaksi (penjualan, pembelian, kunjungan)
        $penjualanCount = \App\Penjualan::where('user_id', $user->id)->count();
        $pembelianCount = \App\Pembelian::where('user_id', $user->id)->count();
        $kunjunganCount = \App\Kunjungan::where('user_id', $user->id)->count();

        if ($penjualanCount > 0 || $pembelianCount > 0 || $kunjunganCount > 0) {
            return redirect()->route('users.index')->with(
                'error',
                'Tidak dapat menghapus user karena masih memiliki transaksi. ' .
                'Penjualan: ' . $penjualanCount . ', Pembelian: ' . $pembelianCount . ', Kunjungan: ' . $kunjunganCount
            );
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function updateEmailRecipient(Request $request, User $user)
    {
        if (!in_array($user->role, ['super_admin', 'admin'])) {
            return redirect()->route('users.index', $request->only('role', 'search', 'page'))
                ->with('error', 'Pengaturan penerima email hanya berlaku untuk Super Admin dan Admin.');
        }

        $request->validate([
            'receives_transaction_email' => ['nullable', 'boolean'],
        ]);

        $user->receives_transaction_email = $request->boolean('receives_transaction_email');
        $user->save();

        return redirect()->route('users.index', $request->only('role', 'search', 'page'))
            ->with('success', 'Pengaturan email untuk user ' . $user->name . ' berhasil diperbarui.');
    }
}