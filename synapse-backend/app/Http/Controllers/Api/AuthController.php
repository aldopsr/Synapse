<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\PasswordChangedMail;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    // ============================================================
    // REGISTER
    // ============================================================
    public function register(Request $request)
    {
        if ($request->role === 'mahasiswa') {
            $request->validate([
                'name'     => 'required|string|max:255',
                'duel_code'  => $this->_generateDuelCode(),
                'email'    => 'required|email|unique:users,email|ends_with:@apps.ipb.ac.id',
                'password' => 'required|min:6',
                'nim'      => [
                    'required',
                    'string',
                    // Unik — 1 NIM hanya boleh 1 akun
                    'unique:users,nim',
                    // Format NIM IPB: 9–11 karakter alfanumerik
                    'regex:/^[A-Za-z][0-9]{8,10}$/',
                ],
                'kelas'    => 'nullable|string|max:10',
            ], [
                'nim.unique' => 'NIM ini sudah terdaftar. Setiap NIM hanya boleh digunakan satu akun.',
                'nim.regex'  => 'Format NIM tidak valid. NIM IPB diawali huruf dan diikuti 8–10 digit angka (contoh: J0409241025).',
                'email.ends_with' => 'Mahasiswa IPB wajib menggunakan email @apps.ipb.ac.id.',
            ]);
        } else {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);
        }

        $otp  = rand(100000, 999999);
        $role = $request->role === 'mahasiswa' ? 'mahasiswa' : 'public';

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => $role,
            'nim'            => ($role === 'mahasiswa') ? $request->nim : null,
            'kelas'          => ($role === 'mahasiswa') ? ($request->kelas ?? null) : null,
            'otp'            => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'message' => 'Registrasi Berhasil! Cek email untuk OTP.',
            'user'    => $user,
        ], 201);
    }

    // ============================================================
    // LOGIN
    // ============================================================
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau Password salah!'], 401);
        }

        $token = $user->createToken('synapse_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil!',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }

    // ============================================================
    // GET USER PROFILE
    // ============================================================
    public function getUser(Request $request)
    {
        return response()->json([
            'message' => 'Berhasil',
            'user'    => $request->user(),
        ], 200);
    }

    // ============================================================
    // LOGOUT
    // ============================================================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil'], 200);
    }

    // ============================================================
    // CHANGE PASSWORD
    // ============================================================
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ], [
            'new_password.confirmed' => 'Konfirmasi password tidak cocok!',
            'new_password.min'       => 'Password baru minimal 6 karakter!',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah!'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        Mail::to($user->email)->send(new PasswordChangedMail($user->name));

        return response()->json(['message' => 'Password berhasil diubah!'], 200);
    }

    // ============================================================
    // VERIFY OTP
    // ============================================================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        if ((string) $user->otp !== (string) $request->otp) {
            return response()->json(['message' => 'Kode OTP salah.'], 422);
        }

        if (Carbon::now()->gt(Carbon::parse($user->otp_expires_at))) {
            return response()->json(['message' => 'Kode OTP sudah kadaluarsa.'], 422);
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);

        return response()->json(['message' => 'Verifikasi berhasil! Silakan login.'], 200);
    }

    // ============================================================
    // RESEND OTP
    // ============================================================
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        $otp = rand(100000, 999999);
        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'Kode OTP baru telah dikirim.'], 200);
    }

    // ============================================================
    // FORGOT PASSWORD — semua pakai DB field, bukan Cache
    // ============================================================
    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar di sistem.'], 404);
        }

        if (!in_array($user->role, ['dosen', 'admin', 'superadmin'])) {
            return response()->json(['message' => 'Email ini bukan akun dosen. Fitur lupa sandi hanya tersedia untuk dosen.'], 403);
        }

        $otp = rand(100000, 999999);

        // Simpan di Cache — TERPISAH dari OTP registrasi di DB
        Cache::put('otp_reset_' . $request->email, (string)$otp, now()->addMinutes(10));

        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP reset password telah dikirim.'], 200);
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string',
        ]);

        $cachedOtp = Cache::get('otp_reset_' . $request->email);

        if (!$cachedOtp || (string)$cachedOtp !== (string)$request->otp) {
            return response()->json(['message' => 'OTP salah atau sudah kadaluarsa.'], 422);
        }

        // Buat reset token
        $resetToken = \Illuminate\Support\Str::random(40);
        Cache::put("reset_token_{$request->email}", $resetToken, now()->addMinutes(15));

        // Hapus OTP setelah diverifikasi
        Cache::forget('otp_reset_' . $request->email);

        return response()->json([
            'message'     => 'OTP valid.',
            'reset_token' => $resetToken,
        ], 200);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                     => 'required|email',
            'reset_token'               => 'required|string',
            'new_password'              => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        $cached = Cache::get("reset_token_{$request->email}");
        if (!$cached || $cached !== $request->reset_token) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kadaluarsa.'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        Cache::forget("reset_token_{$request->email}");

        Mail::to($user->email)->send(new PasswordChangedMail($user->name));

        return response()->json(['message' => 'Password berhasil direset!'], 200);
    }
    
    private function _generateDuelCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (\App\Models\User::where('duel_code', $code)->exists());
        return $code;
    }
}