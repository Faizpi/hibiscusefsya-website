<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
     * Upload & compress avatar foto profil.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // max 5MB input
        ]);

        $user = auth()->user();

        // Hapus avatar lama jika ada
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $file = $request->file('avatar');

        // Compress & resize menggunakan PHP GD (built-in, tanpa library tambahan)
        $compressed = $this->compressImage($file);

        $filename = 'avatars/' . $user->id . '_' . time() . '.jpg';
        Storage::disk('public')->put($filename, $compressed);

        $user->update(['avatar' => $filename]);

        return redirect()->route('profil.show')
            ->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Compress image using GD: resize to max 400x400, quality 80 JPEG.
     */
    private function compressImage($file): string
    {
        $mime = $file->getMimeType();
        $path = $file->getRealPath();

        // Create image resource from uploaded file
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $src = imagecreatefrompng($path);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($path);
                break;
            default:
                $src = imagecreatefromjpeg($path);
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Resize to max 400x400, keeping aspect ratio
        $maxSize = 400;
        if ($origW > $maxSize || $origH > $maxSize) {
            if ($origW > $origH) {
                $newW = $maxSize;
                $newH = (int) round($origH * $maxSize / $origW);
            } else {
                $newH = $maxSize;
                $newW = (int) round($origW * $maxSize / $origH);
            }
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);

        // Handle transparency for PNG
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Output to buffer as JPEG quality 82
        ob_start();
        imagejpeg($dst, null, 82);
        $data = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $data;
    }

    /**
     * Hapus avatar profil (reset ke default huruf).
     */
    public function deleteAvatar()
    {
        $user = auth()->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        return redirect()->route('profil.show')
            ->with('success', 'Foto profil berhasil dihapus.');
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
