<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Material;
use Illuminate\Support\Facades\DB; // Untuk query ke tabel yang mungkin belum ada Model-nya
use Carbon\Carbon; // Untuk mainan tanggal dan bulan

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = $request->user();

        // =======================================================
        // 👑 JIKA YANG LOGIN ADMIN / SUPERADMIN
        // =======================================================
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            $totalDosen = User::where('role', 'dosen')->count();
            $totalMahasiswa = User::where('role', 'mahasiswa')->count();
            $totalMateri = Material::count();
            $totalAR = 0; // Sesuaikan jika ada kolom file_3d

            // LOGIKA GRAFIK 2: Aktivitas Unggah Materi 5 Bulan Terakhir
            $barLabels = [];
            $barData = [];
            for ($i = 4; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $barLabels[] = $month->translatedFormat('M'); // Menghasilkan Jan, Feb, dst
                
                // Hitung materi yang diunggah di bulan tersebut
                $countMateri = Material::whereMonth('created_at', $month->month)
                                       ->whereYear('created_at', $month->year)
                                       ->count();
                $barData[] = $countMateri;
            }

            return response()->json([
                'success' => true,
                'role' => 'admin',
                'cards' => [
                    'total_dosen' => $totalDosen,
                    'total_mahasiswa' => $totalMahasiswa,
                    'total_materi' => $totalMateri,
                    'total_ar' => $totalAR
                ],
                'charts' => [
                    'pie' => [
                        'labels' => ['Mahasiswa', 'Dosen'],
                        'data' => [$totalMahasiswa, $totalDosen] // Ini sudah data asli!
                    ],
                    'bar' => [
                        'labels' => $barLabels,
                        'data' => $barData // Ini sudah data asli per bulan!
                    ]
                ]
            ]);
        }

        // =======================================================
        // 👨‍🏫 JIKA YANG LOGIN DOSEN
        // =======================================================
        if ($user->role === 'dosen') {
            // Asumsi tabel materi ada kolom 'user_id' untuk menandai pembuatnya
            // Jika tidak ada, ganti jadi Material::count();
            $materiSaya = Material::where('user_id', $user->id)->count(); 
            
            // ⚠️ PERHATIAN KAPTEN: Sesuaikan 'quiz_attempts' dengan nama tabel nilai Kapten!
            // Jika tabelnya belum ada, ini akan error. Ubah jadi 0 dulu kalau belum ada.
            try {
                $kuisAktif = DB::table('quizzes')->where('user_id', $user->id)->count();
                $rataNilai = DB::table('quiz_attempts')->avg('score') ?? 0; 
                $mahasiswaHadir = DB::table('quiz_attempts')->distinct('user_id')->count('user_id');

                // LOGIKA GRAFIK 1: Pesebaran Nilai
                $nilaiA = DB::table('quiz_attempts')->whereBetween('score', [90, 100])->count();
                $nilaiB = DB::table('quiz_attempts')->whereBetween('score', [80, 89])->count();
                $nilaiC = DB::table('quiz_attempts')->whereBetween('score', [70, 79])->count();
                $nilaiD = DB::table('quiz_attempts')->whereBetween('score', [60, 69])->count();
                $nilaiE = DB::table('quiz_attempts')->where('score', '<', 60)->count();

                $gradeData = [$nilaiA, $nilaiB, $nilaiC, $nilaiD, $nilaiE];
            } catch (\Exception $e) {
                // Fallback (Penyelamat) kalau tabel quiz_attempts belum dibuat
                $kuisAktif = 0; $rataNilai = 0; $mahasiswaHadir = 0;
                $gradeData = [0, 0, 0, 0, 0];
            }

            return response()->json([
                'success' => true,
                'role' => 'dosen',
                'cards' => [
                    'materi_saya' => $materiSaya,
                    'kuis_aktif' => $kuisAktif,
                    'rata_nilai' => round($rataNilai, 2),
                    'mahasiswa_hadir' => $mahasiswaHadir
                ],
                'charts' => [
                    'bar' => [
                        'labels' => ['A (90-100)', 'B (80-89)', 'C (70-79)', 'D (60-69)', 'E (<60)'],
                        'data' => $gradeData
                    ],
                    'line' => [
                        'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                        'data' => [0, 0, 0, 0] // Ini kita biarkan 0 dulu sampai tabel riwayat kuisnya jelas
                    ]
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Role tidak valid'], 403);
    }
}