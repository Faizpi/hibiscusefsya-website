<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PersonalAccessToken;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Helper: format user data array (termasuk avatar_url).
     */
    private function formatUser(User $user): array
    {
        return [
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'role'               => $user->role,
            'alamat'             => $user->alamat,
            'no_telp'            => $user->no_telp,
            'avatar_url'         => $user->avatarUrl(),
            'gudang_id'          => $user->gudang_id,
            'current_gudang_id'  => $user->current_gudang_id,
        ];
    }

    /**
     * Login dan dapatkan API token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Generate token
        $plainToken  = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        PersonalAccessToken::create([
            'user_id'    => $user->id,
            'name'       => $request->device_name ?? 'mobile',
            'token'      => $hashedToken,
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $plainToken,
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * Logout (revoke token).
     */
    public function logout(Request $request)
    {
        $tokenId = $request->get('api_token_id');
        if ($tokenId) {
            PersonalAccessToken::where('id', $tokenId)->delete();
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * Get current user profile.
     */
    public function profile(Request $request)
    {
        $user   = auth()->user();
        $gudang = $user->getCurrentGudang();

        return response()->json([
            'user'   => $this->formatUser($user),
            'gudang' => $gudang ? [
                'id'             => $gudang->id,
                'nama_gudang'    => $gudang->nama_gudang,
                'alamat_gudang'  => $gudang->alamat_gudang,
            ] : null,
        ]);
    }

    /**
     * Update profil (no_telp & alamat; nama bersifat readonly di UI).
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'alamat'  => 'nullable|string',
            'no_telp' => 'nullable|string|max:20',
        ]);

        $data = $request->only('no_telp', 'alamat');
        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }

        $user->update($data);

        return response()->json(['message' => 'Profil berhasil diupdate.', 'user' => $this->formatUser($user)]);
    }

    /**
     * Upload & compress avatar via API (dari Flutter).
     * Diterima sebagai multipart/form-data dengan field 'avatar' berisi base64 string
     * atau sebagai binary image file.
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();

        // Pastikan direktori avatars ada
        $avatarsDir = public_path('storage/avatars');
        if (!is_dir($avatarsDir)) {
            mkdir($avatarsDir, 0755, true);
        }

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            ]);

            // Hapus avatar lama
            if ($user->avatar) {
                $oldPath = public_path('storage/' . $user->avatar);
                if (file_exists($oldPath)) @unlink($oldPath);
            }

            $file       = $request->file('avatar');
            $compressed = $this->compressImage($file->getRealPath(), $file->getMimeType());
            $filename   = 'avatars/' . $user->id . '_' . time() . '.jpg';
            file_put_contents(public_path('storage/' . $filename), $compressed);
            $user->update(['avatar' => $filename]);

        } elseif ($request->filled('avatar_base64')) {
            $base64 = $request->input('avatar_base64');
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64, 2)[1];
            }
            $imageData = base64_decode($base64);
            if (!$imageData) {
                return response()->json(['message' => 'Data gambar tidak valid.'], 422);
            }

            if ($user->avatar) {
                $oldPath = public_path('storage/' . $user->avatar);
                if (file_exists($oldPath)) @unlink($oldPath);
            }

            $tmpPath = sys_get_temp_dir() . '/avatar_' . $user->id . '.jpg';
            file_put_contents($tmpPath, $imageData);
            $compressed = $this->compressImage($tmpPath, 'image/jpeg');
            @unlink($tmpPath);

            $filename = 'avatars/' . $user->id . '_' . time() . '.jpg';
            file_put_contents(public_path('storage/' . $filename), $compressed);
            $user->update(['avatar' => $filename]);

        } else {
            return response()->json(['message' => 'Tidak ada file avatar yang dikirim.'], 422);
        }

        return response()->json([
            'message'    => 'Avatar berhasil diupload.',
            'avatar_url' => $user->avatarUrl(),
            'user'       => $this->formatUser($user),
        ]);
    }

    /**
     * Hapus avatar (reset ke default huruf).
     */
    public function deleteAvatar()
    {
        $user = auth()->user();
        if ($user->avatar) {
            $path = public_path('storage/' . $user->avatar);
            if (file_exists($path)) @unlink($path);
            $user->update(['avatar' => null]);
        }
        return response()->json(['message' => 'Avatar berhasil dihapus.', 'user' => $this->formatUser($user)]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    /**
     * Compress image using GD: resize to max 400x400, JPEG quality 82.
     */
    private function compressImage(string $path, string $mime): string
    {
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($path);
                break;
            case 'image/webp':
                $src = @imagecreatefromwebp($path);
                break;
            default:
                $src = @imagecreatefromjpeg($path);
        }

        if (!$src) {
            // Fallback: return raw file content if GD can't process it
            return file_get_contents($path);
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

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

        $dst   = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        imagejpeg($dst, null, 82);
        $data = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $data;
    }
}
