<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        // 👇 PERBAIKAN DI SINI: Jadikan keduanya String murni dan buang spasi gaib
        $dbOtp = (string)$user->otp;
        $inputOtp = trim((string)$request->otp);

        // Cek kecocokan OTP yang sudah dibersihkan
        if ($dbOtp !== $inputOtp) {
            return response()->json([
                'message' => 'Kode OTP salah!',
                // 💡 Buka komentar 2 baris di bawah ini JIKA MASIH ERROR, 
                // agar kita bisa melihat bentuk asli datanya di terminal Flutter!
                // 'db_punya_laravel' => $dbOtp,
                // 'input_dari_flutter' => $inputOtp,
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
}