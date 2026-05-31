<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;

class CourseController extends Controller
{
    // 1. LIHAT DAFTAR MATKUL
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin' || $user->role === 'superadmin') {
            $courses = Course::latest()->get();
        } else {
            // Dosen: tampilkan matkul yang dosen_ids-nya mengandung ID dosen ini
            $courses = Course::where('dosen_ids', $user->id)->latest()->get();
        }

        $courses = $courses->map(fn($c) => $this->withDosens($c));

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data Mata Kuliah',
            'data'    => $courses,
        ]);
    }

    // 2. BUAT MATKUL BARU (KHUSUS ADMIN)
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $user->role !== 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Hanya Admin yang dapat membuat Mata Kuliah.',
            ], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'dosen_ids'   => 'nullable|array',
            'dosen_ids.*' => 'exists:users,_id',
        ]);

        $course = Course::create([
            'title'       => $request->title,
            'description' => $request->description,
            'dosen_ids'   => $request->dosen_ids ?? [],
            'created_by'  => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mata Kuliah berhasil ditambahkan!',
            'data'    => $this->withDosens($course),
        ], 201);
    }

    // 3. UPDATE DAFTAR DOSEN PENGAMPU
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'dosen_ids'   => 'required|array|min:1',
            'dosen_ids.*' => 'exists:users,_id',
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $data = ['dosen_ids' => $request->dosen_ids];
        if ($request->has('title'))       $data['title']       = $request->title;
        if ($request->has('description')) $data['description'] = $request->description;

        $course->update($data);

        return response()->json([
            'success' => true,
            'data'    => $this->withDosens($course->fresh()),
        ]);
    }

    // 4. TAMBAH SATU DOSEN KE MATKUL
    public function addDosen(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $request->validate(['dosen_id' => 'required|exists:users,_id']);

        $ids = $course->dosen_ids ?? [];
        if (!in_array($request->dosen_id, $ids)) {
            $ids[] = $request->dosen_id;
            $course->update(['dosen_ids' => $ids]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dosen berhasil ditambahkan ke matkul.',
            'data'    => $this->withDosens($course->fresh()),
        ]);
    }

    // 5. HAPUS SATU DOSEN DARI MATKUL
    public function removeDosen(Request $request, $id, $dosenId)
    {
        $course = Course::findOrFail($id);

        $ids = array_values(array_filter(
            $course->dosen_ids ?? [],
            fn($d) => $d !== $dosenId
        ));
        $course->update(['dosen_ids' => $ids]);

        return response()->json([
            'success' => true,
            'message' => 'Dosen berhasil dihapus dari matkul.',
            'data'    => $this->withDosens($course->fresh()),
        ]);
    }

    // Helper: tambahkan data dosen ke response course
    private function withDosens(Course $course): array
    {
        $data           = $course->toArray();
        $data['dosens'] = User::whereIn('_id', $course->dosen_ids ?? [])
            ->get(['_id', 'name', 'email'])
            ->toArray();
        return $data;
    }
}
