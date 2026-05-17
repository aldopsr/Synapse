<?php
// ============================================================
// PATCH: synapse-backend/app/Http/Controllers/Api/DashboardController.php
// Tambahkan data kuis_soal (jumlah soal per kuis) dan kuis_nonaktif
// untuk melengkapi grafik dosen yang lebih informatif.
//
// Ganti SELURUH method getStats() dengan kode di bawah ini.
// ============================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = $request->user();

        // ═══════════════════════════════════════════════════════
        // 👑 ADMIN / SUPERADMIN
        // ═══════════════════════════════════════════════════════
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            $totalDosen     = User::where('role', 'dosen')->count();
            $totalMahasiswa = User::where('role', 'mahasiswa')->count();
            $totalMateri    = Material::count();

            // Total AR assets
            try {
                $totalAR = DB::table('ar_assets')->count();
            } catch (\Exception $e) {
                $totalAR = 0;
            }

            // Grafik 1: Proporsi pengguna (pie)
            // Grafik 2: Materi baru per 5 bulan terakhir (bar)
            $barLabels = [];
            $barData   = [];
            for ($i = 4; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $barLabels[] = $month->translatedFormat('M Y');
                $barData[]   = Material::whereMonth('created_at', $month->month)
                                       ->whereYear('created_at',  $month->year)
                                       ->count();
            }

            return response()->json([
                'success' => true,
                'role'    => 'admin',
                'cards'   => [
                    'total_dosen'     => $totalDosen,
                    'total_mahasiswa' => $totalMahasiswa,
                    'total_materi'    => $totalMateri,
                    'total_ar'        => $totalAR,
                ],
                'charts' => [
                    'pie' => [
                        'labels' => ['Mahasiswa', 'Dosen'],
                        'data'   => [$totalMahasiswa, $totalDosen],
                    ],
                    'bar' => [
                        'labels' => $barLabels,
                        'data'   => $barData,
                    ],
                ],
            ]);
        }

        // ═══════════════════════════════════════════════════════
        // 👨‍🏫 DOSEN
        // ═══════════════════════════════════════════════════════
        if ($user->role === 'dosen') {

            // Stat cards
            $materiSaya = Material::where('user_id', $user->id)->count();

            try {
                // Kuis milik dosen ini (by course_id karena kuis dibuat per matkul)
                $kuisQuery  = DB::table('quizzes')->where('course_id', $user->course_id);
                $kuisAktif  = (clone $kuisQuery)->where('is_active', true)->count();
                $kuisNonaktif = (clone $kuisQuery)->where('is_active', false)->count();

                // Nilai dari quiz_attempts untuk kuis-kuis ini
                $kuisIds    = (clone $kuisQuery)->pluck('_id')->toArray();
                $rataNilai  = DB::table('quiz_attempts')
                                ->whereIn('quiz_id', $kuisIds)
                                ->avg('score') ?? 0;
                $mahasiswaHadir = DB::table('quiz_attempts')
                                ->whereIn('quiz_id', $kuisIds)
                                ->distinct('user_id')
                                ->count('user_id');

                // Grafik 1: Persebaran nilai grade A–E
                $gradeData = [
                    DB::table('quiz_attempts')->whereIn('quiz_id', $kuisIds)->whereBetween('score', [90, 100])->count(),
                    DB::table('quiz_attempts')->whereIn('quiz_id', $kuisIds)->whereBetween('score', [80, 89])->count(),
                    DB::table('quiz_attempts')->whereIn('quiz_id', $kuisIds)->whereBetween('score', [70, 79])->count(),
                    DB::table('quiz_attempts')->whereIn('quiz_id', $kuisIds)->whereBetween('score', [60, 69])->count(),
                    DB::table('quiz_attempts')->whereIn('quiz_id', $kuisIds)->where('score', '<', 60)->count(),
                ];

                // Grafik 2: Jumlah soal per kuis (horizontal bar)
                // Ambil max 8 kuis terakhir supaya chart tidak overflow
                $kuisList = DB::table('quizzes')
                    ->where('course_id', $user->course_id)
                    ->latest()
                    ->limit(8)
                    ->get(['_id', 'title']);

                $kuisSoalLabels = [];
                $kuisSoalData   = [];
                foreach ($kuisList as $k) {
                    $judul = strlen($k->title) > 25
                        ? substr($k->title, 0, 22) . '...'
                        : $k->title;
                    $kuisSoalLabels[] = $judul;
                    $kuisSoalData[]   = DB::table('quiz_questions')
                                          ->where('quiz_id', $k->_id ?? $k->id)
                                          ->count();
                }

            } catch (\Exception $e) {
                // Fallback aman kalau tabel belum ada
                $kuisAktif      = 0;
                $kuisNonaktif   = 0;
                $rataNilai      = 0;
                $mahasiswaHadir = 0;
                $gradeData      = [0, 0, 0, 0, 0];
                $kuisSoalLabels = [];
                $kuisSoalData   = [];
            }

            return response()->json([
                'success' => true,
                'role'    => 'dosen',
                'cards'   => [
                    'materi_saya'     => $materiSaya,
                    'kuis_aktif'      => $kuisAktif,
                    'kuis_nonaktif'   => $kuisNonaktif,   // ← BARU: untuk fallback doughnut
                    'rata_nilai'      => round($rataNilai, 2),
                    'mahasiswa_hadir' => $mahasiswaHadir,
                ],
                'charts' => [
                    // Grafik 1: persebaran nilai (sudah ada sebelumnya)
                    'bar' => [
                        'labels' => ['A (90–100)', 'B (80–89)', 'C (70–79)', 'D (60–69)', 'E (<60)'],
                        'data'   => $gradeData,
                    ],
                    // Grafik 2: jumlah soal per kuis (BARU — mengganti line partisipasi kosong)
                    'kuis_soal' => [
                        'labels' => $kuisSoalLabels,
                        'data'   => $kuisSoalData,
                    ],
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Role tidak valid'], 403);
    }
}