<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    // 1. MENGAMBIL DAFTAR DOSEN
    public function index()
    {
        $dosen = User::with('course')->where('role', 'dosen')->get();
    
    return response()->json(['data' => $dosen], 200);
    }

    // 2. MEMBUAT AKUN DOSEN BARU
    public function store(Request $request)
{
    // Validasi data yang masuk
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'course_id' => 'required|exists:courses,id' // Validasi matkul
    ]);

    // Simpan ke database
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'dosen',
        'course_id' => $request->course_id,
    ]);

    return response()->json(['message' => 'Akun dosen berhasil dibuat', 'data' => $user], 201);
}

    // 3. MENGHAPUS AKUN DOSEN
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Akun tidak ditemukan!'], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun Dosen berhasil dihapus!'
        ], 200);
    }
}