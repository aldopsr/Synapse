<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Course;

class FypController extends Controller
{
    // ============================================================
    // GET /api/fyp
    // Analisis kekuatan & kelemahan user berdasarkan hasil kuis
    // per mata kuliah. Diurutkan dari yang paling perlu didalami.
    // ============================================================
    public function index(Request $request)
    {
        $user   = $request->user();
        $userId = (string) $user->id;

        // 1. Ambil semua mata kuliah yang punya kuis aktif
        $allCourses = Course::latest()->get();

        if ($allCourses->isEmpty()) {
            return response()->json([
                'message' => 'Berhasil',
                'summary' => $this->_emptySummary(),
                'data'    => [],
            ], 200);
        }

        // 2. Ambil semua kuis aktif, grup per course_id
        $courseIds = $allCourses->map(fn($c) => (string) $c->id)->toArray();

        $allQuizzes = Quiz::where('is_active', true)
            ->whereIn('course_id', $courseIds)
            ->get();

        // course_id => [quiz_ids]
        $quizByCourse = $allQuizzes->groupBy(fn($q) => (string) $q->course_id)
            ->map(fn($qs) => $qs->map(fn($q) => (string) $q->id)->toArray());

        // 3. Ambil semua attempt user, untuk semua kuis
        $allQuizIds   = $allQuizzes->map(fn($q) => (string) $q->id)->toArray();
        $userAttempts = QuizAttempt::where('user_id', $userId)
            ->whereIn('quiz_id', $allQuizIds)
            ->get()
            ->keyBy('quiz_id');

        // 4. Bangun analisis per mata kuliah
        $result = [];

        foreach ($allCourses as $course) {
            $cid     = (string) $course->id;
            $quizIds = $quizByCourse->get($cid, []);

            // Matkul tanpa kuis aktif -> skip
            if (empty($quizIds)) continue;

            // Kumpulkan attempt user untuk matkul ini
            $attempts = collect($quizIds)
                ->map(fn($qid) => $userAttempts->get($qid))
                ->filter();

            $totalQuizzes   = count($quizIds);
            $attemptedCount = $attempts->count();

            // Belum mengerjakan satu pun kuis di matkul ini
            if ($attemptedCount === 0) {
                $result[] = [
                    'course_id'      => $cid,
                    'course_title'   => $course->title ?? $course->name ?? 'Mata Kuliah',
                    'course_image'   => $course->image ?? null,
                    'status'         => 'belum_dikerjakan',
                    'status_label'   => 'Belum Dijelajahi',
                    'status_color'   => 'grey',
                    'avg_score'      => null,
                    'passing_score'  => null,
                    'is_passed'      => false,
                    'total_quizzes'  => $totalQuizzes,
                    'done_quizzes'   => 0,
                    'priority_score' => 0,
                    'insight'        => 'Kamu belum mengerjakan kuis di mata kuliah ini.',
                    'action_label'   => 'Mulai Kuis',
                ];
                continue;
            }

            // Hitung rata-rata skor
            $avgScore  = round($attempts->avg('score'), 1);
            $allPassed = $attempts->every(fn($a) => $a->is_passed == true);
            $anyPassed = $attempts->contains(fn($a) => $a->is_passed == true);

            // Ambil passing_score dari kuis pertama matkul ini
            $firstQuizId  = $quizIds[0];
            $firstQuiz    = $allQuizzes->first(fn($q) => (string) $q->id === $firstQuizId);
            $passingScore = (int) ($firstQuiz?->passing_score ?? 70);

            [$status, $label, $color, $priority, $insight, $action] =
                $this->_classify($avgScore, $passingScore, $allPassed,
                                 $anyPassed, $attemptedCount, $totalQuizzes);

            // Matkul dikuasai -> tidak tampil di FYP
            if ($status === 'dikuasai') continue;

            $result[] = [
                'course_id'      => $cid,
                'course_title'   => $course->title ?? $course->name ?? 'Mata Kuliah',
                'course_image'   => $course->image ?? null,
                'status'         => $status,
                'status_label'   => $label,
                'status_color'   => $color,
                'avg_score'      => $avgScore,
                'passing_score'  => $passingScore,
                'is_passed'      => $allPassed,
                'total_quizzes'  => $totalQuizzes,
                'done_quizzes'   => $attemptedCount,
                'priority_score' => $priority,
                'insight'        => $insight,
                'action_label'   => $action,
            ];
        }

        // 5. Urutkan: priority tertinggi dulu, lalu skor terendah
        usort($result, function ($a, $b) {
            if ($b['priority_score'] !== $a['priority_score']) {
                return $b['priority_score'] - $a['priority_score'];
            }
            $aScore = $a['avg_score'] ?? 101;
            $bScore = $b['avg_score'] ?? 101;
            return $aScore <=> $bScore;
        });

        return response()->json([
            'message' => 'Berhasil',
            'summary' => $this->_buildSummary($result),
            'data'    => array_values($result),
        ], 200);
    }

    private function _classify(
        float $avgScore,
        int   $passingScore,
        bool  $allPassed,
        bool  $anyPassed,
        int   $done,
        int   $total
    ): array {
        // Kritis: rata-rata sangat rendah
        if ($avgScore < 60) {
            return [
                'kritis', 'Perlu Perhatian Segera', 'red', 100,
                "Rata-rata skormu {$avgScore} — jauh di bawah KKM {$passingScore}. Fokus di sini dulu.",
                'Pelajari Ulang',
            ];
        }

        // Gagal: di bawah passing
        if (!$allPassed && $avgScore < $passingScore) {
            $gap = round($passingScore - $avgScore);
            return [
                'perlu_latihan', 'Perlu Latihan', 'orange', 80,
                "Skormu {$avgScore}, belum mencapai KKM {$passingScore}. Kurang {$gap} poin lagi.",
                'Coba Lagi',
            ];
        }

        // Lulus tapi pas-pasan
        if ($allPassed && $avgScore <= $passingScore + 10) {
            return [
                'hampir_optimal', 'Bisa Lebih Baik', 'yellow', 50,
                "Lulus dengan skor {$avgScore}. Masih ada ruang untuk tumbuh.",
                'Tingkatkan Skor',
            ];
        }

        // Belum semua kuis dikerjakan
        if ($done < $total) {
            return [
                'belum_lengkap', 'Belum Selesai', 'blue', 30,
                "Baru {$done} dari {$total} kuis dikerjakan di mata kuliah ini.",
                'Lanjutkan',
            ];
        }

        // Dikuasai -> tidak ditampilkan
        return ['dikuasai', 'Dikuasai', 'green', -1, '', ''];
    }

    private function _buildSummary(array $items): array
    {
        $counts = array_count_values(array_column($items, 'status'));
        return [
            'kritis'           => $counts['kritis']           ?? 0,
            'perlu_latihan'    => $counts['perlu_latihan']    ?? 0,
            'hampir_optimal'   => $counts['hampir_optimal']   ?? 0,
            'belum_dikerjakan' => $counts['belum_dikerjakan'] ?? 0,
            'belum_lengkap'    => $counts['belum_lengkap']    ?? 0,
            'total_items'      => count($items),
        ];
    }

    private function _emptySummary(): array
    {
        return [
            'kritis' => 0, 'perlu_latihan' => 0, 'hampir_optimal' => 0,
            'belum_dikerjakan' => 0, 'belum_lengkap' => 0, 'total_items' => 0,
        ];
    }
}