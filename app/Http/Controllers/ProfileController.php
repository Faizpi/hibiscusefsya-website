<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil user yang sedang login.
     */
    public function show()
    {
        $user = auth()->user();
        return view('profil.show', compact('user'));
    }

    /**
     * Update profil (no_telp & alamat saja, nama & email readonly).
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'no_telp' => ['nullable', 'string', 'max:20'],
            'alamat'  => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'no_telp' => $request->no_telp,
            'alamat'  => $request->alamat,
        ]);

        return redirect()->route('profil.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Ubah password.
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password'          => ['required', 'string'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ], [
            'current_password.required'     => 'Password saat ini wajib diisi.',
            'new_password.required'         => 'Password baru wajib diisi.',
            'new_password.min'              => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini salah.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profil.show')
            ->with('success', 'Password berhasil diubah.');
    }
}
