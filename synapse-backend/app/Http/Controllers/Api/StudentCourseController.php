<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Material; // Nanti buat ambil materi

class StudentCourseController extends Controller
{
    // 1. LIHAT SEMUA DAFTAR MATKUL (KHUSUS MAHASISWA)
    public function index()
    {
        // Sementara kita tampilkan semua matkul yang ada di database
        $courses = Course::all();
        
        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    // 2. LIHAT MATERI DI DALAM MATKUL (KHUSUS MAHASISWA)
    public function getMaterials($course_id)
    {
        $materials = Material::where('course_id', $course_id)->get();

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }
}