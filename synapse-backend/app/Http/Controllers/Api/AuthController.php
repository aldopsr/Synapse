<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache; 

class AuthController extends Controller
{
    // --- 1. REGISTER ---
    public function register(Request $request)
    {
        // Validasi Mahasiswa vs Publik
        if ($request->role === 'mahasiswa') {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email|ends_with:@apps.ipb.ac.id',
                'password' => 'required|min:6',
                'nim' => 'required|string|unique:users,nim',
            ]);
        }
        else {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);
        }

        // Generate 6 Digit OTP Acak
        $otp = rand(100000, 999999);

        // Buat User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role === 'mahasiswa' ? 'mahasiswa' : 'public',
            'nim' => $request->nim ?? null,
            'kelas' => $request->kelas ?? null,
            'otp' => $otp, // Simpan OTP
            'otp_expires_at' => Carbon::now()->addMinutes(10), // Berlaku 10 menit
        ]);

        // Kirim Email OTP
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'message' => 'Registrasi Berhasil! Cek email untuk OTP.',
            'user' => $user
        ], 201);
    }

    // --- 2. LOGIN ---
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau Password salah!'], 401);
        }

        $token = $user->createToken('synapse_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil!',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function changePassword(Request $request)
    {
        // 1. Validasi input dari Flutter
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // confirmed otomatis mengecek 'new_password_confirmation'
        ], [
            'new_password.confirmed' => 'Konfirmasi password tidak cocok!',
            'new_password.min' => 'Password baru minimal 6 karakter!'
        ]);

        // 2. Ambil data user yang sedang login saat ini
        $user = $request->user();

        // 3. Cek apakah password lama yang dimasukkan COCOK dengan yang ada di database
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama yang Kapten masukkan salah!'
            ], 400); // 400 Bad Request
        }

        // 4. Jika cocok, ganti password lama dengan password baru (jangan lupa di-Hash/enkripsi)
        $user->password = Hash::make($request->new_password);
        $user->save();

        // 5. Beri laporan sukses ke Flutter
        return response()->json([
            'message' => 'Password berhasil diperbarui!'
        ], 200);
    }

    // --- 3. LOGOUT ---
    public function logout(Request $request)
    {
        // Fitur canggih Sanctum: Langsung cari tiket yang sedang dipakai, lalu hancurkan!
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout, Tiket VIP telah hangus!'
        ], 200);
    }

    // --- 4. AMBIL DATA USER (Untuk HomeScreen Flutter) ---
    public function getUser(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    // --- 5. VERIFIKASI OTP ---
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required', // Hapus 'string' agar menerima angka atau teks
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $dbOtp = (string)$user->otp;
        $inputOtp = trim((string)$request->otp);

        // Cek kecocokan OTP yang sudah dibersihkan
        if ($dbOtp !== $inputOtp) {
            return response()->json([
                'message' => 'Kode OTP salah!',
            ], 400);
        }

        // Cek apakah OTP kedaluwarsa
        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'Kode OTP sudah kedaluwarsa. Silakan minta ulang.'], 400);
        }

        // Jika Sukses: Hapus OTP dan tandai email terverifikasi
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json(['message' => 'Verifikasi Sukses!'], 200);
    }

    // --- 6. KIRIM ULANG OTP ---
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Generate OTP Baru
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Kirim ulang email
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP baru telah dikirim ke email Anda'], 200);
    }

    // ====================================================================
    // --- FITUR LUPA PASSWORD (MENGGUNAKAN CACHE) ---
    // ====================================================================

    // 1. Kirim OTP untuk Reset Password
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $otp = rand(100000, 999999);
        
        // Simpan OTP di Cache selama 10 Menit
        Cache::put('otp_reset_' . $request->email, $otp, now()->addMinutes(10));

        Mail::raw("Halo, ini pesan dari SYNAPSE.\n\nKode OTP untuk reset password Anda adalah: $otp\n\nKode ini hanya berlaku selama 10 menit.", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Kode OTP Reset Password - SYNAPSE');
        });

        return response()->json(['message' => 'OTP reset password berhasil dikirim'], 200);
    }

    // 2. Verifikasi OTP Reset Password (TIDAK BENTROK DENGAN REGISTRASI)
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $cachedOtp = Cache::get('otp_reset_' . $request->email);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            return response()->json(['message' => 'OTP Valid'], 200);
        }

        return response()->json(['message' => 'Kode OTP salah atau sudah kadaluarsa'], 400);
    }

    // 3. Simpan Password Baru
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'new_password' => 'required|min:6'
        ]);

        $cachedOtp = Cache::get('otp_reset_' . $request->email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Gagal mengubah password. OTP tidak valid.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->new_password);
        $user->save();

        Cache::forget('otp_reset_' . $request->email);

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.'], 200);
    }
}