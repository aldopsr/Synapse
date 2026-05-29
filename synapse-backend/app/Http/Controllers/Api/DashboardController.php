<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Duel;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = $request->user();

        // ═══════════════════════════════════════════════
        // 👑 ADMIN / SUPERADMIN
        // ═══════════════════════════════════════════════
        if (in_array($user->role, ['admin', 'superadmin'])) {
            $totalDosen     = User::where('role', 'dosen')->count();
            $totalMahasiswa = User::where('role', 'mahasiswa')->count();
            $totalMateri    = Material::count();
            $totalDuel      = Duel::count();
            $totalAR = 0;
            try { $totalAR = DB::table('ar_assets')->count(); } catch (\Exception $e) {}

            // ── Grafik 1: Aktivitas per mata kuliah ──
            $courses        = Course::all();
            $matkul         = [];
            $matkulAttempts = [];
            foreach ($courses as $c) {
                $qids = Quiz::where('course_id', (string) $c->id)
                    ->get()->map(fn($q) => (string) $q->id)->toArray();
                $cnt = empty($qids) ? 0
                    : QuizAttempt::whereIn('quiz_id', $qids)->count();
                if ($cnt > 0) {
                    $matkul[]         = $c->title ?? $c->name ?? '-';
                    $matkulAttempts[] = $cnt;
                }
            }
            array_multisort($matkulAttempts, SORT_DESC, $matkul);
            $matkul         = array_slice($matkul, 0, 8);
            $matkulAttempts = array_slice($matkulAttempts, 0, 8);

            // ── Grafik 2: Registrasi user per 6 bulan ──
            $regLabels = [];
            $regData   = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = Carbon::now()->subMonths($i);
                $regLabels[] = $m->translatedFormat('M Y');
                $regData[]   = User::whereIn('role', ['mahasiswa', 'public'])
                    ->whereMonth('created_at', $m->month)
                    ->whereYear('created_at',  $m->year)
                    ->count();
            }

            // ── Leaderboard A: Dosen Teraktif ──
            $dosenList  = User::where('role', 'dosen')->get();
            $dosenBoard = $dosenList->map(function ($d) {
                $courseIds = Course::where('dosen_id', (string) $d->id)
                    ->get()->map(fn($c) => (string) $c->id)->toArray();
                if (empty($courseIds) && $d->course_id) $courseIds = [$d->course_id];

                $jumlahMateri = Material::where('user_id', (string) $d->id)->count();
                $jumlahKuis   = empty($courseIds) ? 0
                    : Quiz::whereIn('course_id', $courseIds)->count();
                $quizIds = empty($courseIds) ? []
                    : Quiz::whereIn('course_id', $courseIds)
                        ->get()->map(fn($q) => (string) $q->id)->toArray();
                $jumlahAttempts = empty($quizIds) ? 0
                    : QuizAttempt::whereIn('quiz_id', $quizIds)
                        ->distinct('user_id')->count('user_id');
                $skor = ($jumlahMateri * 2) + ($jumlahKuis * 3) + $jumlahAttempts;

                return [
                    'name'            => $d->name,
                    'jumlah_materi'   => $jumlahMateri,
                    'jumlah_kuis'     => $jumlahKuis,
                    'mahasiswa_aktif' => $jumlahAttempts,
                    'skor_aktivitas'  => $skor,
                ];
            })->sortByDesc('skor_aktivitas')->values()->take(10)->toArray();

            // ── Leaderboard B: Top Mahasiswa Sistem ──
            $allAttempts    = QuizAttempt::all();
            $byUser         = $allAttempts->groupBy('user_id');
            $mahasiswaBoard = $byUser->map(function ($attempts, $userId) {
                $u = User::find($userId);
                if (!$u || $u->role !== 'mahasiswa') return null;

                $avgScore  = round($attempts->avg('score'), 1);
                $totalKuis = $attempts->count();
                $duelWon   = Duel::where('winner_id', (string) $userId)->count();

                return [
                    'name'       => $u->name,
                    'nim'        => $u->nim ?? '-',
                    'avg_score'  => $avgScore,
                    'total_kuis' => $totalKuis,
                    'duel_won'   => $duelWon,
                    'skor'       => ($avgScore * 0.5) + ($totalKuis * 3) + ($duelWon * 5),
                ];
            })->filter()->sortByDesc('skor')->values()->take(10)->toArray();

            return response()->json([
                'success' => true,
                'role'    => 'admin',
                'cards'   => [
                    'total_dosen'     => $totalDosen,
                    'total_mahasiswa' => $totalMahasiswa,
                    'total_materi'    => $totalMateri,
                    'total_ar'        => $totalAR,
                    'total_duel'      => $totalDuel,
                ],
                'charts' => [
                    'matkul_activity' => [
                        'labels' => $matkul,
                        'data'   => $matkulAttempts,
                    ],
                    'registrasi' => [
                        'labels' => $regLabels,
                        'data'   => $regData,
                    ],
                ],
                'leaderboard' => [
                    'dosen'     => $dosenBoard,
                    'mahasiswa' => $mahasiswaBoard,
                ],
            ]);
        }

        // ═══════════════════════════════════════════════
        // 👨‍🏫 DOSEN
        // ═══════════════════════════════════════════════
        if ($user->role === 'dosen') {
            $courseIds = Course::where('dosen_id', (string) $user->id)
                ->get()->map(fn($c) => (string) $c->id)->toArray();
            if (empty($courseIds) && $user->course_id) {
                $courseIds = [$user->course_id];
            }

            $materiSaya = Material::where('user_id', (string) $user->id)->count();

            try {
                $allKuisIds = empty($courseIds) ? []
                    : Quiz::whereIn('course_id', $courseIds)
                        ->get()->map(fn($q) => (string) $q->id)->toArray();

                $kuisAktif    = empty($courseIds) ? 0 : Quiz::whereIn('course_id', $courseIds)->where('is_active', true)->count();
                $kuisNonaktif = empty($courseIds) ? 0 : Quiz::whereIn('course_id', $courseIds)->where('is_active', false)->count();
                $rataNilai    = empty($allKuisIds) ? 0 : (QuizAttempt::whereIn('quiz_id', $allKuisIds)->avg('score') ?? 0);
                $mahasiswaHadir = empty($allKuisIds) ? 0
                    : QuizAttempt::whereIn('quiz_id', $allKuisIds)->distinct('user_id')->count('user_id');

                // ── Grafik 1: % Kelulusan per kuis ──
                $kuisList = empty($courseIds) ? collect()
                    : Quiz::whereIn('course_id', $courseIds)
                        ->where('is_active', true)->latest()->limit(8)->get();

                $lulusLabels = [];
                $lulusData   = [];
                foreach ($kuisList as $k) {
                    $kId   = (string) $k->id;
                    $total = QuizAttempt::where('quiz_id', $kId)->count();
                    if ($total === 0) continue;
                    $lulus = QuizAttempt::where('quiz_id', $kId)->where('is_passed', true)->count();
                    $pct   = round(($lulus / $total) * 100, 1);
                    $judul = mb_strlen($k->title) > 22 ? mb_substr($k->title, 0, 20) . '…' : $k->title;
                    $lulusLabels[] = $judul;
                    $lulusData[]   = $pct;
                }

                // ── Grafik 2: Passed vs Failed keseluruhan ──
                $totalAttempts = empty($allKuisIds) ? 0 : QuizAttempt::whereIn('quiz_id', $allKuisIds)->count();
                $totalLulus    = empty($allKuisIds) ? 0 : QuizAttempt::whereIn('quiz_id', $allKuisIds)->where('is_passed', true)->count();
                $totalGagal    = $totalAttempts - $totalLulus;

                // ── Leaderboard mahasiswa ──
                $attempts = empty($allKuisIds) ? collect()
                    : QuizAttempt::whereIn('quiz_id', $allKuisIds)->get();

                $byUser = $attempts->groupBy('user_id');
                $leaderboard = $byUser->map(function ($userAttempts, $userId) use ($allKuisIds) {
                    $u = User::find($userId);
                    if (!$u) return null;

                    $avgScore   = round($userAttempts->avg('score'), 1);
                    $totalKuis  = $userAttempts->count();
                    $lulusCount = $userAttempts->where('is_passed', true)->count();
                    $duelWon    = Duel::where('winner_id', (string) $userId)
                        ->whereIn('quiz_id', $allKuisIds)->count();

                    return [
                        'name'        => $u->name,
                        'nim'         => $u->nim ?? '-',
                        'avg_score'   => $avgScore,
                        'total_kuis'  => $totalKuis,
                        'lulus_count' => $lulusCount,
                        'duel_won'    => $duelWon,
                        'skor'        => ($avgScore * 0.5) + ($totalKuis * 3) + ($duelWon * 5),
                    ];
                })->filter()->sortByDesc('skor')->values()->take(10)->toArray();

            } catch (\Exception $e) {
                $kuisAktif = $kuisNonaktif = $rataNilai = $mahasiswaHadir = 0;
                $lulusLabels = $lulusData = [];
                $totalLulus = $totalGagal = 0;
                $leaderboard = [];
            }

            return response()->json([
                'success' => true,
                'role'    => 'dosen',
                'cards'   => [
                    'materi_saya'     => $materiSaya,
                    'kuis_aktif'      => $kuisAktif,
                    'kuis_nonaktif'   => $kuisNonaktif,
                    'rata_nilai'      => round($rataNilai, 1),
                    'mahasiswa_hadir' => $mahasiswaHadir,
                ],
                'charts' => [
                    'kelulusan_per_kuis' => [
                        'labels' => $lulusLabels,
                        'data'   => $lulusData,
                    ],
                    'passed_failed' => [
                        'lulus' => $totalLulus,
                        'gagal' => $totalGagal,
                    ],
                ],
                'leaderboard' => $leaderboard,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Role tidak valid'], 403);
    }
}