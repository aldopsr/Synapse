<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\Quiz;

class StudentDataController extends Controller
{
    // ============================================================
    // DOSEN: Mahasiswa yang sudah mengerjakan quiz di course mereka
    // ============================================================
    public function quizParticipants(Request $request)
    {
        $user     = $request->user();
        $courseId = $user->role === 'dosen'
            ? ($user->course_id ?? null)
            : $request->query('course_id');

        if (!$courseId) {
            return response()->json([
                'message' => 'Kamu belum ditugaskan ke mata kuliah.',
                'data'    => [],
            ], 200);
        }

        // Ambil semua quiz_id di course ini
        $quizIds = Quiz::where('course_id', $courseId)->pluck('_id')->toArray();

        if (empty($quizIds)) {
            return response()->json([
                'message' => 'Belum ada quiz di mata kuliah ini.',
                'data'    => [],
            ], 200);
        }

        // Ambil semua attempt untuk quiz-quiz ini
        $attempts = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($attempts->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada mahasiswa yang mengerjakan quiz.',
                'data'    => [],
            ], 200);
        }

        // FIX: Manual lookup user — MongoDB tidak support with() cross-collection
        $userIds  = $attempts->pluck('user_id')->unique()->toArray();
        $users    = User::whereIn('_id', $userIds)->get()->keyBy(fn($u) => (string) $u->_id);

        // Ambil judul quiz juga
        $quizMap  = Quiz::whereIn('_id', $quizIds)->get()->keyBy(fn($q) => (string) $q->_id);

        // Group attempts by user_id
        $byUser = $attempts->groupBy('user_id');

        $result = $byUser->map(function ($userAttempts, $userId) use ($users, $quizMap) {
            $userData = $users->get((string) $userId);
            if (!$userData) return null;

            $nim        = $userData->nim ?? '';
            $angkatan   = strlen($nim) >= 8 ? '20' . substr($nim, 5, 2) : '-';
            $sekolah    = $this->_getSekolah($nim);

            $avgScore   = round($userAttempts->avg('score'), 1);
            $total      = $userAttempts->count();

            return [
                'user_id'          => (string) $userId,
                'name'             => $userData->name,
                'email'            => $userData->email,
                'nim'              => $nim,
                'kelas'            => $userData->kelas ?? '-',
                'nim_info'         => [
                    'angkatan' => $angkatan,
                    'sekolah'  => $sekolah,
                    'jenjang'  => $this->_getJenjang($nim),
                ],
                'avg_score'        => $avgScore,
                'total_quiz_taken' => $total,
                'last_activity'    => $userAttempts->sortByDesc('created_at')->first()->created_at,
                'attempts'         => $userAttempts->map(fn($a) => [
                    'quiz_id'    => $a->quiz_id,
                    'quiz_title' => isset($quizMap[(string)$a->quiz_id])
                        ? $quizMap[(string)$a->quiz_id]->title
                        : 'Quiz Dihapus',
                    'score'      => $a->score,
                    'is_passed'  => $a->is_passed,
                    'created_at' => $a->created_at,
                ])->values(),
            ];
        })->filter()->values();

        // Group by angkatan
        $grouped = $result->groupBy(fn($item) => $item['nim_info']['angkatan'] ?? '-')
            ->map(fn($g) => $g->values());

        return response()->json([
            'message'              => 'Berhasil',
            'total'                => $result->count(),
            'grouped_by_angkatan'  => $grouped,
            'data'                 => $result,
        ], 200);
    }

    // ============================================================
    // ADMIN: Semua mahasiswa
    // ============================================================
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
        if ($angkatan = $request->angkatan) {
            $suffix = substr($angkatan, -2);
            $query->where('nim', 'like', "_____{$suffix}%");
        }
        if ($fak = $request->fakultas) {
            $query->where('nim', 'like', "{$fak}%");
        }

        $students = $query->orderBy('name')->get();

        // Manual count attempts per user
        $userIds      = $students->pluck('_id')->toArray();
        $attemptCounts = QuizAttempt::whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $result = $students->map(function ($u) use ($attemptCounts) {
            $uid      = (string) $u->_id;
            $nim      = $u->nim ?? '';
            $attempts = $attemptCounts->get($uid, collect());
            $avgScore = $attempts->count() > 0
                ? round($attempts->avg('score'), 1)
                : null;

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
            'filter_options' => [
                'angkatan' => $angkatanList,
                'fakultas' => $sekolahList,
            ],
            'data'           => $result,
        ], 200);
    }

    // ============================================================
    // ADMIN: Detail satu mahasiswa
    // ============================================================
    public function studentDetail(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $attempts = QuizAttempt::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $quizIds  = $attempts->pluck('quiz_id')->toArray();
        $quizMap  = Quiz::whereIn('_id', $quizIds)->get()
            ->keyBy(fn($q) => (string) $q->_id);

        $nim = $user->nim ?? '';

        $attemptDetail = $attempts->map(fn($a) => [
            'quiz_id'            => $a->quiz_id,
            'quiz_title'         => isset($quizMap[(string)$a->quiz_id])
                ? $quizMap[(string)$a->quiz_id]->title
                : 'Quiz Dihapus',
            'score'              => $a->score,
            'earned_points'      => $a->earned_points   ?? 0,
            'max_points'         => $a->max_points       ?? 0,
            'correct_count'      => $a->correct_count    ?? 0,
            'total_questions'    => $a->total_questions  ?? 0,
            'is_passed'          => $a->is_passed        ?? false,
            'time_taken_seconds' => $a->time_taken_seconds ?? 0,
            'created_at'         => $a->created_at,
        ]);

        $avgScore = $attempts->count() > 0
            ? round($attempts->avg('score'), 1)
            : null;

        return response()->json([
            'message' => 'Berhasil',
            'student' => [
                'user_id'       => (string) $user->_id,
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

    // ============================================================
    // Helper NIM parser
    // ============================================================
    private function _getSekolah(string $nim): string
    {
        if (empty($nim)) return '-';
        $map = [
            'J' => 'Sekolah Vokasi',
            'A' => 'Fak. Pertanian',
            'B' => 'Fak. Kedokteran Hewan',
            'C' => 'Fak. Perikanan & Ilmu Kelautan',
            'D' => 'Fak. Peternakan',
            'E' => 'Fak. Kehutanan & Lingkungan',
            'F' => 'Fak. Teknologi Pertanian',
            'G' => 'Fak. MIPA',
            'H' => 'Fak. Ekonomi & Manajemen',
            'I' => 'Fak. Ekologi Manusia',
            'K' => 'Fak. Kedokteran',
        ];
        return $map[strtoupper($nim[0])] ?? "Kode '{$nim[0]}'";
    }

    private function _getJenjang(string $nim): string
    {
        if (strlen($nim) < 3) return '-';
        $map = [
            '1' => 'S1 Reguler',
            '2' => 'S1 Alih Jenis',
            '3' => 'D3 Vokasi',
            '4' => 'D4 / Sarjana Terapan',
            '5' => 'S2',
            '6' => 'S3',
        ];
        return $map[$nim[2]] ?? '-';
    }
}