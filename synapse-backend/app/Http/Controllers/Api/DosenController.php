<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    // 1. MENGAMBIL DAFTAR DOSEN
    public function index()
    {
        $dosen = User::where('role', 'dosen')->get();

        return response()->json(['data' => $dosen], 200);
    }

    // 2. MEMBUAT AKUN DOSEN BARU
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $courseIds = $request->input('course_ids', []);
        if (empty($courseIds) && $request->filled('course_id')) {
            $courseIds = [$request->course_id];
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'dosen',
            'course_id' => $courseIds[0] ?? null,
        ]);

        $this->syncDosenToCourses((string) $user->_id, [], $courseIds);

        return response()->json(['message' => 'Akun dosen berhasil dibuat', 'data' => $user], 201);
    }

    // 3. MENGHAPUS AKUN DOSEN
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Akun tidak ditemukan!'], 404);
        }

        // Hapus dosen dari semua course yang mengandung ID-nya
        $this->syncDosenToCourses((string) $user->_id, $this->getDosenCourseIds((string) $user->_id), []);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun Dosen berhasil dihapus!'
        ], 200);
    }

    // 4. UPDATE DATA DOSEN
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $newCourseIds = $request->input('course_ids', []);
        if (empty($newCourseIds) && $request->filled('course_id')) {
            $newCourseIds = [$request->course_id];
        }

        if (!empty($newCourseIds)) {
            $data['course_id'] = $newCourseIds[0];
            $oldCourseIds = $this->getDosenCourseIds((string) $user->_id);
            $this->syncDosenToCourses((string) $user->_id, $oldCourseIds, $newCourseIds);
        }

        $user->update($data);

        return response()->json(['success' => true, 'data' => $user]);
    }

    // Ambil semua course_id yang saat ini mengandung dosen ini
    private function getDosenCourseIds(string $dosenId): array
    {
        return Course::where('dosen_ids', $dosenId)
            ->get(['_id'])
            ->map(fn($c) => (string) $c->_id)
            ->toArray();
    }

    // Sync dosen_ids di dokumen Course: hapus dari oldIds, tambah ke newIds
    private function syncDosenToCourses(string $dosenId, array $oldCourseIds, array $newCourseIds): void
    {
        $toRemove = array_diff($oldCourseIds, $newCourseIds);
        $toAdd    = array_diff($newCourseIds, $oldCourseIds);

        foreach ($toRemove as $courseId) {
            $course = Course::find($courseId);
            if ($course) {
                $ids = array_values(array_filter(
                    $course->dosen_ids ?? [],
                    fn($d) => (string) $d !== $dosenId
                ));
                $course->update(['dosen_ids' => $ids]);
            }
        }

        foreach ($toAdd as $courseId) {
            $course = Course::find($courseId);
            if ($course) {
                $ids = $course->dosen_ids ?? [];
                if (!in_array($dosenId, array_map('strval', $ids))) {
                    $ids[] = $dosenId;
                    $course->update(['dosen_ids' => $ids]);
                }
            }
        }
    }
}