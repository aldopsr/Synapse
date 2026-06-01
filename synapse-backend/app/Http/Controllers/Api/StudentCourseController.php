<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Material;

class StudentCourseController extends Controller
{
    /**
     * GET /api/public/courses
     * Semua matkul — tamu & mahasiswa bisa akses
     */
    public function index(Request $request)
{
    $user = auth('sanctum')->user();
    $role = $user ? ($user->role ?? 'public') : 'guest';
    $isLimited = ($user === null || in_array($role, ['guest', 'public']));

    $courses = Course::latest()->get()->filter(function ($course) use ($isLimited) {
        $query = Material::where('course_id', $course->_id ?? $course->id);

        if ($isLimited) {
            $query->where('visibility', 'umum');
        }

        return $query->exists();
    })->values();

    return response()->json([
        'success' => true,
        'data'    => $courses,
    ]);
}

    /**
     * GET /api/public/courses/{course_id}/materials
     * Materi per matkul — tamu hanya dapat visibility='umum',
     * mahasiswa/public dapat semua (backend cek token)
     */
    public function getMaterials(Request $request, $course_id)
    {
        $user = auth('sanctum')->user(); // ← ini yang benar untuk public route
        $role = $user ? ($user->role ?? 'public') : 'guest';

        $query = Material::where('course_id', $course_id)
            ->with('ar_assets')
            ->with('questions')
            ->latest();

        // Tamu hanya lihat materi 'umum'
        if ($user === null || in_array($role, ['guest', 'public'])) {
            $query->where('visibility', 'umum');
        }

        $materials = $query->get();

        // Transform image path → full URL
        // $materials->transform(function ($item) {
        //     if ($item->image && !str_starts_with($item->image, 'http')) {
        //         $item->image = asset('storage/' . $item->image);
        //     }
        //     return $item;
        // });

        return response()->json([
            'success' => true,
            'data'    => $materials,
        ]);
    }
}