<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\Quiz;

class StudentDataController extends Controller
{
    public function quizParticipants(Request $request)
    {
        $user     = $request->user();
        $courseId = $user->role === 'dosen'
            ? ($user->course_id ?? null)
            : $request->query('course_id');

        if (!$courseId) {
            return response()->json(['message' => 'Kamu belum ditugaskan ke mata kuliah.', 'data' => []], 200);
        }

        // FIX: pakai ->get()->map() bukan ->pluck('_id')
        $quizIds = Quiz::where('course_id', $courseId)->get()
            ->map(fn($q) => (string) $q->id)
            ->toArray();

        if (empty($quizIds)) {
            return response()->json(['message' => 'Belum ada quiz di mata kuliah ini.', 'data' => []], 200);
        }

        $attempts = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($attempts->isEmpty()) {
            return response()->json(['message' => 'Belum ada mahasiswa yang mengerjakan quiz.', 'data' => []], 200);
        }

        $userIds = $attempts->pluck('user_id')->unique()->toArray();

        // FIX: keyBy pakai ->id bukan ->_id
        $users   = User::whereIn('_id', $userIds)->get()
            ->keyBy(fn($u) => (string) $u->id);

        // FIX: keyBy pakai ->id bukan ->_id
        $quizMap = Quiz::whereIn('_id', $quizIds)->get()
            ->keyBy(fn($q) => (string) $q->id);

        $byUser = $attempts->groupBy('user_id');

        $result = $byUser->map(function ($userAttempts, $userId) use ($users, $quizMap) {
            $userData = $users->get((string) $userId);
            if (!$userData) return null;

            $nim      = $userData->nim ?? '';
            $avgScore = round($userAttempts->avg('score'), 1);

            return [
                'user_id'          => (string) $userId,
                'name'             => $userData->name,
                'email'            => $userData->email,
                'nim'              => $nim,
                'kelas'            => $userData->kelas ?? '-',
                'nim_info'         => [
                    'angkatan' => strlen($nim) >= 8 ? '20' . substr($nim, 5, 2) : '-',
                    'sekolah'  => $this->_getSekolah($nim),
                    'jenjang'  => $this->_getJenjang($nim),
                ],
                'avg_score'        => $avgScore,
                'total_quiz_taken' => $userAttempts->count(),
                'last_activity'    => $userAttempts->sortByDesc('created_at')->first()->created_at,
                'attempts'         => $userAttempts->map(fn($a) => [
                    'quiz_id'    => $a->quiz_id,
                    'quiz_title' => $quizMap->get((string) $a->quiz_id)?->title ?? 'Quiz Dihapus',
                    'score'      => $a->score,
                    'is_passed'  => $a->is_passed,
                    'created_at' => $a->created_at,
                ])->values(),
            ];
        })->filter()->values();

        return response()->json([
            'message' => 'Berhasil',
            'total'   => $result->count(),
            'data'    => $result,
        ], 200);
    }

    public function allStudents(Request $request)
    {
        $query = User::where('role', 'mahasiswa');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name',  'like', "%{$s}%")
                  ->orWhere('nim',   'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $students = $query->orderBy('name')->get();

        // FIX: pakai ->id bukan ->_id
        $userIds = $students->map(fn($u) => (string) $u->id)->toArray();
        $attemptCounts = QuizAttempt::whereIn('user_id', $userIds)->get()->groupBy('user_id');

        $result = $students->map(function ($u) use ($attemptCounts) {
            $uid      = (string) $u->id; // FIX
            $nim      = $u->nim ?? '';
            $attempts = $attemptCounts->get($uid, collect());
            $avgScore = $attempts->count() > 0 ? round($attempts->avg('score'), 1) : null;

            return [
                'user_id'          => $uid,
                'name'             => $u->name,
                'email'            => $u->email,
                'nim'              => $nim,
                'kelas'            => $u->kelas ?? '-',
                'nim_info'         => [
                    'angkatan' => strlen($nim) >= 8 ? '20' . substr($nim, 5, 2) : '-',
                    'sekolah'  => $this->_getSekolah($nim),
                    'jenjang'  => $this->_getJenjang($nim),
                ],
                'total_quiz_taken' => $attempts->count(),
                'avg_score'        => $avgScore,
                'registered_at'    => $u->created_at,
            ];
        });

        $angkatanList = $result->pluck('nim_info.angkatan')->unique()->filter()->sort()->values();
        $sekolahList  = $result->pluck('nim_info.sekolah')->unique()->filter()->sort()->values();

        return response()->json([
            'message'        => 'Berhasil',
            'total'          => $result->count(),
            'filter_options' => ['angkatan' => $angkatanList, 'fakultas' => $sekolahList],
            'data'           => $result,
        ], 200);
    }

    public function studentDetail(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan.'], 404);

        $attempts = QuizAttempt::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        $quizIds  = $attempts->pluck('quiz_id')->toArray();

        // FIX: keyBy pakai ->id
        $quizMap  = Quiz::whereIn('_id', $quizIds)->get()
            ->keyBy(fn($q) => (string) $q->id);

        $nim = $user->nim ?? '';

        $attemptDetail = $attempts->map(fn($a) => [
            'quiz_id'            => $a->quiz_id,
            'quiz_title'         => $quizMap->get((string) $a->quiz_id)?->title ?? 'Quiz Dihapus',
            'score'              => $a->score,
            'earned_points'      => $a->earned_points      ?? 0,
            'max_points'         => $a->max_points          ?? 0,
            'correct_count'      => $a->correct_count       ?? 0,
            'total_questions'    => $a->total_questions     ?? 0,
            'is_passed'          => $a->is_passed           ?? false,
            'time_taken_seconds' => $a->time_taken_seconds  ?? 0,
            'created_at'         => $a->created_at,
        ]);

        $avgScore = $attempts->count() > 0 ? round($attempts->avg('score'), 1) : null;

        return response()->json([
            'message' => 'Berhasil',
            'student' => [
                'user_id'       => (string) $user->id, // FIX
                'name'          => $user->name,
                'email'         => $user->email,
                'nim'           => $nim,
                'kelas'         => $user->kelas ?? '-',
                'nim_info'      => [
                    'angkatan' => strlen($nim) >= 8 ? '20' . substr($nim, 5, 2) : '-',
                    'sekolah'  => $this->_getSekolah($nim),
                    'jenjang'  => $this->_getJenjang($nim),
                ],
                'registered_at' => $user->created_at,
            ],
            'summary' => [
                'total_quiz_taken' => $attempts->count(),
                'avg_score'        => $avgScore,
                'passed_count'     => $attempts->where('is_passed', true)->count(),
                'failed_count'     => $attempts->where('is_passed', false)->count(),
            ],
            'attempts' => $attemptDetail,
        ], 200);
    }

    private function _getSekolah(string $nim): string
    {
        if (empty($nim)) return '-';
        $map = [
            'J' => 'Sekolah Vokasi',      'A' => 'Fak. Pertanian',
            'B' => 'Fak. Kedokteran Hewan','C' => 'Fak. Perikanan & Ilmu Kelautan',
            'D' => 'Fak. Peternakan',      'E' => 'Fak. Kehutanan & Lingkungan',
            'F' => 'Fak. Teknologi Pertanian','G' => 'Fak. MIPA',
            'H' => 'Fak. Ekonomi & Manajemen','I' => 'Fak. Ekologi Manusia',
            'K' => 'Fak. Kedokteran',
        ];
        return $map[strtoupper($nim[0])] ?? "Kode '{$nim[0]}'";
    }

    private function _getJenjang(string $nim): string
    {
        if (strlen($nim) < 3) return '-';
        $map = ['1'=>'S1 Reguler','2'=>'S1 Alih Jenis','3'=>'D3 Vokasi',
                '4'=>'D4 / Sarjana Terapan','5'=>'S2','6'=>'S3'];
        return $map[$nim[2]] ?? '-';
    }

    // ── Edit data mahasiswa ────────────────────────────────────
    public function update(Request $request, $userId)
    {
        $admin = $request->user();
        if (!in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }
 
        $user = User::find($userId);
        if (!$user || $user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
        }
 
        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|max:255',
            'nim'      => 'sometimes|string|max:50',
            'kelas'    => 'sometimes|string|max:50',
            'password' => 'sometimes|string|min:8',
        ]);
 
        $updateData = array_filter([
            'name'  => $request->name,
            'email' => $request->email,
            'nim'   => $request->nim,
            'kelas' => $request->kelas,
        ]);
 
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }
 
        if (empty($updateData)) {
            return response()->json(['message' => 'Tidak ada data yang diubah'], 422);
        }
 
        $user->update($updateData);
 
        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data'    => $user->fresh(),
        ]);
    }
 
    // ── Hapus akun mahasiswa ───────────────────────────────────
    public function destroy(Request $request, $userId)
    {
        $admin = $request->user();
        if (!in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }
 
        $user = User::find($userId);
        if (!$user || $user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
        }
 
        // Hapus data terkait (quiz attempts, duel, fcm token)
        \App\Models\QuizAttempt::where('user_id', (string) $userId)->delete();
        \App\Models\Duel::where('challenger_id', (string) $userId)
            ->orWhere('opponent_id', (string) $userId)->delete();
        \App\Models\FcmToken::where('user_id', (string) $userId)->delete();
 
        $user->delete();
 
        return response()->json(['message' => 'Akun mahasiswa berhasil dihapus']);
    }
}