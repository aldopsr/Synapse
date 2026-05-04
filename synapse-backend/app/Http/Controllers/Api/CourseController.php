<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    // 1. LIHAT DAFTAR MATKUL
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Asumsi: di tabel 'users' Kapten ada kolom 'role' (admin/dosen/mahasiswa)
        if ($user->role === 'admin') {
            // Jika yang login ADMIN: Tampilkan semua matkul yang ada di sistem
            // Opsional: Boleh di-load juga data dosennya kalau ada relasinya
            $courses = Course::latest()->get(); 
        } else {
            // Jika yang login DOSEN: Hanya tampilkan matkul yang DITUGASKAN kepadanya
            $courses = Course::where('dosen_id', $user->id)->latest()->get();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data Mata Kuliah',
            'data' => $courses
        ]);
    }

    // 2. BUAT MATKUL BARU (KHUSUS ADMIN)
    public function store(Request $request)
    {
        $user = $request->user();

        // 🌟 KUNCI: Cek apakah user yang request ini adalah Admin!
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Hanya Admin yang dapat membuat Mata Kuliah.'
            ], 403);
        }

        // 🌟 UPDATE VALIDASI: Admin WAJIB mengirimkan ID Dosen saat buat matkul
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dosen_id' => 'required|exists:users,id' // Pastikan ID Dosen ini valid di database
        ]);

        // Simpan matkul baru
        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'dosen_id' => $request->dosen_id, // Tugaskan matkul ke Dosen yang dipilih Admin
            'created_by' => $user->id, // (Opsional) Rekam ID Admin yang membuat matkul ini
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mata Kuliah berhasil ditambahkan dan ditugaskan ke Dosen!',
            'data' => $course
        ], 201);
    }
}