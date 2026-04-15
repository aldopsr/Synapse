<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Wajib ditambahkan untuk enkripsi password

class WebUserController extends Controller
{
    // 1. Tampilkan semua daftar pengguna
    public function index()
    {
        // Ambil semua user KECUALI dirinya sendiri (Admin yang sedang login)
        $users = User::where('id', '!=', auth()->id())->orderBy('role')->get();
        return view('users.index', compact('users'));
    }

    // 2. Buka Form Tambah Pengguna Baru
    public function create()
    {
        return view('users.create');
    }

    // 3. Simpan Pengguna Baru ke Database
    public function store(Request $request)
    {
        // Validasi inputan Admin
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', // Minimal 8 karakter
            'role' => 'required|in:admin,dosen,mahasiswa', // Pilihan role yang sah
        ]);

        // Buat Akun
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password WAJIB di-hash!
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Akun Pengguna baru berhasil ditambahkan! 🎉');
    }

    // 4. Tombol Nuklir (Hapus Pengguna)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus dari sistem! 🗑️');
    }
}