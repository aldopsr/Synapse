<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;


class QuizController extends Controller
{
    // 1. Ambil Daftar Kuis Utama
    public function index()
    {
        // 1. Ambil ID User yang sedang login
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return response()->json([
                'message' => 'Akses ditolak atau Tamu',
                'data' => []
            ], 200);
        }

        $attemptedQuizIds = QuizAttempt::where('user_id', $userId)
            ->pluck('quiz_id')
            ->toArray();

        $quizzes = Quiz::select('id', 'title', 'duration_minutes', 'created_at')
            ->whereNotIn('_id', $attemptedQuizIds)
            ->get();

        return response()->json([
            'message' => 'Berhasil ambil daftar quiz yang belum dikerjakan',
            'data' => $quizzes
        ], 200);
    }

    // 2. AMBIL SOAL QUIZ (Diacak & Tanpa Kunci Jawaban)
    public function getQuestions($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz tidak ditemukan'], 404);
        }

        $questions = QuizQuestion::where('quiz_id', $id)
            ->get()
            ->shuffle()
            ->values();

        return response()->json([
            'message' => 'Berhasil ambil soal',
            'quiz_title' => $quiz->title,
            'duration' => $quiz->duration_minutes,
            'data' => $questions
        ], 200);
    }

    // 3. SUBMIT JAWABAN & HITUNG NILAI
    public function submitQuiz(Request $request, $id)
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return response()->json(['message' => 'Gagal: Token terkirim, tapi identitas Mahasiswa tidak ditemukan di database.'], 401);
        }

        $request->validate([
            'time_taken_seconds' => 'required|integer',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|string',
            'answers.*.answer' => 'required|in:A,B,C,D,a,b,c,d'
        ]);

        $quiz = Quiz::find($id);
        if (!$quiz)
            return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        // CEK APAKAH SUDAH PERNAH MENGERJAKAN
        $cekAttempt = QuizAttempt::where('user_id', $userId)->where('quiz_id', $id)->first();
        if ($cekAttempt) {
            return response()->json(['message' => 'Anda sudah pernah mengerjakan quiz ini!'], 403);
        }

        // LOGIKA HITUNG SKOR & JAWABAN BENAR
        $totalQuestions = QuizQuestion::where('quiz_id', $id)->count();
        $correctAnswers = 0;

        foreach ($request->answers as $ans) {
            $question = QuizQuestion::find($ans['question_id']);

            // Cek apakah jawaban cocok (huruf besar disamakan)
            if ($question && strtoupper($question->correct_answer) === strtoupper($ans['answer'])) {
                $correctAnswers++;
            }
        }

        if ($totalQuestions > 0) {
            $score = round(($correctAnswers / $totalQuestions) * 100);
        }
        else {
            $score = 0;
        }

        // SIMPAN KE DATABASE 
        $attempt = QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $id,
            'score' => $score,
            'time_taken_seconds' => $request->time_taken_seconds
        ]);

        return response()->json([
            'message' => 'Quiz berhasil disubmit!',
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions
        ], 200);
    }

    // 4. LIHAT LEADERBOARD (Peringkat Mahasiswa)
    public function leaderboard($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz)
            return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        $leaderboard = QuizAttempt::where('quiz_id', $id)
            ->with('user:id,name,kelas,nim')
            ->orderBy('score', 'desc')
            ->orderBy('time_taken_seconds', 'asc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil data Leaderboard',
            'quiz_title' => $quiz->title,
            'data' => $leaderboard
        ], 200);
    }

    // 5. RIWAYAT KUIS (LOG EVALUASI)
    // 5. RIWAYAT KUIS (LOG EVALUASI)
    public function getHistory(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Ambil daftar riwayat pengerjaan mahasiswa
        $attempts = QuizAttempt::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $quizIds = $attempts->pluck('quiz_id')->toArray();

        // 2. Ambil detail kuisnya
        $quizzes = Quiz::whereIn('_id', $quizIds)->get()->keyBy('_id');

        // 3. 👇 TAMBAHAN BARU: Ambil semua soal dari kuis-kuis tersebut dan kelompokkan per quiz_id
        $allQuestions = QuizQuestion::whereIn('quiz_id', $quizIds)->get()->groupBy('quiz_id');

        // 4. Petakan dan gabungkan datanya
        $history = $attempts->map(function ($attempt) use ($quizzes, $allQuestions) {
            $quizData = $quizzes->get($attempt->quiz_id);
            
            // Ambil kumpulan soal khusus untuk kuis ini
            $quizQuestions = $allQuestions->get($attempt->quiz_id, collect());
            
            // Format soal agar rapi untuk dikirim ke Flutter
            $formattedQuestions = $quizQuestions->map(function($q) {
                return [
                    // Catatan: Jika di database Kapten nama kolomnya bahasa Indonesia, 
                    // ubah menjadi $q->pertanyaan dan $q->jawaban_benar
                    'question' => $q->question, 
                    'correct_answer' => $q->correct_answer,
                ];
            })->toArray();

            return [
                'quiz_id' => $attempt->quiz_id, 
                'title' => $quizData ? $quizData->title : 'Kuis Dihapus/Tidak Ditemukan',
                'score' => $attempt->score,
                'created_at' => $attempt->created_at,
                'questions' => $formattedQuestions // 👈 SEKARANG DATA SOALNYA IKUT DIKIRIM!
            ];
        });

        return response()->json([
            'message' => 'Berhasil mengambil riwayat evaluasi',
            'data' => $history
        ], 200);
    }
}