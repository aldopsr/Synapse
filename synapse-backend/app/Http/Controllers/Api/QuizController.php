<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use Carbon\Carbon;

class QuizController extends Controller
{
    // ============================================================
    // ADMIN: LIST QUIZ
    // ============================================================
    public function adminIndex(Request $request)
    {
        try {
            $user = $request->user();
            $query = Quiz::query();

            if ($user->role === 'dosen' && isset($user->course_id)) {
                $query->where('course_id', $user->course_id);
            }
            if ($request->has('course_id') && $request->course_id) {
                $query->where('course_id', $request->course_id);
            }

            $quizzes = $query->latest()->get();

            $quizzes->transform(function ($quiz) {
                $quiz->questions_count = QuizQuestion::where('quiz_id', $quiz->id)->count();
                if ($quiz->course_id) {
                    $course = \App\Models\Course::find($quiz->course_id);
                    $quiz->course = $course ? ['id' => $course->id, 'title' => $course->title ?? $course->name ?? '-'] : null;
                } else {
                    $quiz->course = null;
                }
                return $quiz;
            });

            return response()->json(['message' => 'Berhasil', 'data' => $quizzes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }

    // ============================================================
    // ADMIN: DETAIL QUIZ
    // ============================================================
    public function adminShow($id)
    {
        try {
            $quiz = Quiz::find($id);
            if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

            if ($quiz->course_id) {
                $course = \App\Models\Course::find($quiz->course_id);
                $quiz->course = $course ? ['id' => $course->id, 'title' => $course->title ?? $course->name ?? '-'] : null;
            }
            $quiz->questions_count = QuizQuestion::where('quiz_id', $quiz->id)->count();

            return response()->json(['message' => 'Berhasil', 'data' => $quiz], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // CRUD QUIZ
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'is_active' => 'nullable|boolean',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'passing_score' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            $quiz = Quiz::create([
                'title' => $request->title,
                'description' => $request->description,
                'course_id' => $request->course_id,
                'created_by' => auth()->id(),
                'duration_minutes' => (int) $request->duration_minutes,
                'is_active' => $request->boolean('is_active', true),
                'start_at' => $request->start_at ? Carbon::parse($request->start_at) : null,
                'end_at' => $request->end_at ? Carbon::parse($request->end_at) : null,
                'passing_score' => (int) ($request->passing_score ?? 70),
            ]);
            return response()->json(['message' => 'Quiz dibuat', 'data' => $quiz], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'sometimes|string',
            'duration_minutes' => 'sometimes|integer|min:1|max:300',
            'is_active' => 'nullable|boolean',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'passing_score' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            $updateData = $request->only(['title', 'description', 'course_id', 'duration_minutes', 'passing_score']);
            if ($request->has('is_active')) $updateData['is_active'] = $request->boolean('is_active');
            if ($request->has('start_at')) $updateData['start_at'] = $request->start_at ? Carbon::parse($request->start_at) : null;
            if ($request->has('end_at')) $updateData['end_at'] = $request->end_at ? Carbon::parse($request->end_at) : null;
            $quiz->update($updateData);
            return response()->json(['message' => 'Quiz diupdate', 'data' => $quiz->fresh()], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    public function toggleActive($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);
        $quiz->update(['is_active' => !$quiz->is_active]);
        return response()->json(['message' => $quiz->is_active ? 'Aktif' : 'Nonaktif', 'data' => $quiz->fresh()], 200);
    }

    public function destroy($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        // Hapus gambar dari soal-soal terkait
        $questions = QuizQuestion::where('quiz_id', $id)->get();
        foreach ($questions as $q) {
            if ($q->image && Storage::disk('public')->exists($q->image)) {
                Storage::disk('public')->delete($q->image);
            }
        }

        QuizQuestion::where('quiz_id', $id)->delete();
        $quiz->delete();
        return response()->json(['message' => 'Quiz dihapus'], 200);
    }

    // ============================================================
    // 🌟 KELOLA SOAL QUIZ - DENGAN FITUR BARU
    // ============================================================

    // GET /api/admin/quizzes/{id}/questions
    public function getQuizQuestions($id)
    {
        try {
            $quiz = Quiz::find($id);
            if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

            $questions = QuizQuestion::where('quiz_id', $id)
                ->get()
                ->map(function ($q) {
                    $arr = $q->toArray();
                    // Expose correct_answer & correct_answers untuk admin
                    $arr['correct_answer'] = $q->correct_answer;
                    $arr['correct_answers'] = $q->correct_answers ?? [];
                    return $arr;
                });

            return response()->json([
                'message' => 'Berhasil',
                'quiz' => $quiz,
                'data' => $questions
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    // 🌟 POST /api/admin/quizzes/{id}/questions - Support multipart untuk upload gambar
    public function storeQuizQuestion(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string',
            'question_type' => 'nullable|in:multiple_choice,true_false,multiple_answer',
            'option_a' => 'nullable|string',
            'option_b' => 'nullable|string',
            'option_c' => 'nullable|string',
            'option_d' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'correct_answers' => 'nullable', // bisa string JSON atau array
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'points' => 'nullable|integer|min:1|max:100',
            'difficulty' => 'nullable|in:mudah,sedang,sulit',
            'explanation' => 'nullable|string',
        ]);

        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        try {
            $questionType = $request->question_type ?? 'multiple_choice';

            $data = [
                'quiz_id' => $id,
                'question' => $request->question,
                'question_type' => $questionType,
                'points' => (int) ($request->points ?? 10),
                'difficulty' => $request->difficulty ?? 'sedang',
                'explanation' => $request->explanation,
            ];

            // Handle berdasarkan tipe soal
            if ($questionType === 'true_false') {
                // True/False: cuma butuh correct_answer = "true" atau "false"
                $data['option_a'] = 'Benar';
                $data['option_b'] = 'Salah';
                $data['option_c'] = null;
                $data['option_d'] = null;
                $data['correct_answer'] = strtoupper($request->correct_answer ?? 'A'); // A=Benar, B=Salah
            } elseif ($questionType === 'multiple_answer') {
                // Multiple Answer: butuh correct_answers (array)
                $correctAnswers = $request->correct_answers;
                if (is_string($correctAnswers)) {
                    $correctAnswers = json_decode($correctAnswers, true) ?? [];
                }
                if (!is_array($correctAnswers)) {
                    $correctAnswers = [];
                }
                $correctAnswers = array_map('strtoupper', $correctAnswers);
                $data['option_a'] = $request->option_a;
                $data['option_b'] = $request->option_b;
                $data['option_c'] = $request->option_c;
                $data['option_d'] = $request->option_d;
                $data['correct_answer'] = null;
                $data['correct_answers'] = $correctAnswers;
            } else {
                // Multiple Choice (default)
                $data['option_a'] = $request->option_a;
                $data['option_b'] = $request->option_b;
                $data['option_c'] = $request->option_c;
                $data['option_d'] = $request->option_d;
                $data['correct_answer'] = strtoupper($request->correct_answer ?? 'A');
            }

            // Handle upload gambar
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('quiz_images', 'public');
            }

            $question = QuizQuestion::create($data);

            return response()->json(['message' => 'Soal ditambahkan', 'data' => $question], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/admin/quiz-questions/{id}
    public function destroyQuizQuestion($id)
    {
        $question = QuizQuestion::find($id);
        if (!$question) return response()->json(['message' => 'Soal tidak ditemukan'], 404);

        // Hapus file gambar
        if ($question->image && Storage::disk('public')->exists($question->image)) {
            Storage::disk('public')->delete($question->image);
        }

        $question->delete();
        return response()->json(['message' => 'Soal dihapus'], 200);
    }

    // ============================================================
    // ENDPOINT MAHASISWA
    // ============================================================
    public function index()
    {
        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json(['message' => 'Akses ditolak', 'data' => []], 200);
        }

        $attemptedQuizIds = QuizAttempt::where('user_id', $userId)->pluck('quiz_id')->toArray();
        $now = Carbon::now();

        $quizzes = Quiz::where('is_active', true)
            ->whereNotIn('_id', $attemptedQuizIds)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->get();

        $quizzes->transform(function ($quiz) {
            if ($quiz->course_id) {
                $course = \App\Models\Course::find($quiz->course_id);
                $quiz->course = $course ? ['id' => $course->id, 'title' => $course->title ?? $course->name ?? '-'] : null;
            }
            return $quiz;
        });

        return response()->json(['message' => 'Berhasil', 'data' => $quizzes], 200);
    }

    // 🌟 GET QUESTIONS (Mahasiswa) - sudah hide kunci jawaban
    public function getQuestions($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        if (!$quiz->is_accessible) {
            $msg = match ($quiz->status) {
                'nonaktif' => 'Quiz ini sedang dinonaktifkan oleh dosen.',
                'belum_mulai' => 'Quiz ini belum dimulai. Silakan kembali pada ' . ($quiz->start_at ? $quiz->start_at->format('d M Y H:i') : 'jadwal yang ditentukan') . '.',
                'sudah_selesai' => 'Quiz ini sudah berakhir.',
                default => 'Quiz tidak dapat diakses saat ini.',
            };
            return response()->json(['message' => $msg], 403);
        }

        $questions = QuizQuestion::where('quiz_id', $id)->get();

        // 🌟 Bersihkan kunci jawaban sebelum kirim ke mahasiswa
        $questions = $questions->map(function ($q) {
            $arr = $q->toArray();
            unset($arr['correct_answer'], $arr['correct_answers'], $arr['explanation']);
            return $arr;
        });

        // Acak urutan
        $questions = $questions->shuffle()->values();

        return response()->json([
            'message' => 'Berhasil',
            'quiz_title' => $quiz->title,
            'duration' => $quiz->duration_minutes,
            'data' => $questions
        ], 200);
    }

    // 🌟 SUBMIT QUIZ - Skoring baru dengan partial multi-answer
    public function submitQuiz(Request $request, $id)
    {
        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json(['message' => 'Identitas tidak ditemukan.'], 401);
        }

        $request->validate([
            'time_taken_seconds' => 'required|integer',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|string',
            'answers.*.answer' => 'nullable', // bisa string atau array
        ]);

        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        if (!$quiz->is_accessible) {
            return response()->json(['message' => 'Quiz sudah tidak bisa di-submit.'], 403);
        }

        $cekAttempt = QuizAttempt::where('user_id', $userId)->where('quiz_id', $id)->first();
        if ($cekAttempt) {
            return response()->json(['message' => 'Anda sudah pernah mengerjakan quiz ini!'], 403);
        }

        $allQuestions = QuizQuestion::where('quiz_id', $id)->get()->keyBy('_id');

        // 🌟 SKORING BARU: Hitung total poin maksimum & poin didapat
        $maxPoints = 0;
        $earnedPoints = 0;
        $correctCount = 0;
        $reviewData = []; // Data untuk halaman review setelah submit

        // Index jawaban user by question_id untuk lookup cepat
        $userAnswers = collect($request->answers)->keyBy('question_id');

        foreach ($allQuestions as $qid => $question) {
            $questionPoints = (int) ($question->points ?? 10);
            $maxPoints += $questionPoints;

            $userAns = $userAnswers->get($qid);
            $userAnswer = $userAns ? $userAns['answer'] : null;
            $type = $question->question_type ?? 'multiple_choice';

            $isCorrect = false;
            $earnedThisQuestion = 0;

            if ($type === 'multiple_answer') {
                // 🌟 PARTIAL SCORING untuk Multiple Answer
                $correctSet = collect($question->correct_answers ?? [])->map(fn($a) => strtoupper($a))->sort()->values()->toArray();
                $userSet = is_array($userAnswer)
                    ? collect($userAnswer)->map(fn($a) => strtoupper($a))->sort()->values()->toArray()
                    : [];

                if (count($correctSet) > 0) {
                    // Hitung berapa yang benar dipilih, berapa yang salah dipilih
                    $rightlyChosen = count(array_intersect($userSet, $correctSet));
                    $wronglyChosen = count(array_diff($userSet, $correctSet));
                    $missed = count(array_diff($correctSet, $userSet));

                    // Formula partial: (benar dipilih - salah dipilih) / total benar
                    // Minimum 0, max 1
                    $score = ($rightlyChosen - $wronglyChosen) / count($correctSet);
                    $score = max(0, min(1, $score));

                    $earnedThisQuestion = round($questionPoints * $score);
                    $isCorrect = $score >= 1.0; // Dianggap benar kalau score 100%

                    if ($isCorrect) $correctCount++;
                }
            } else {
                // Multiple Choice atau True/False (single answer)
                $correctAns = strtoupper($question->correct_answer ?? '');
                $userAnsStr = is_string($userAnswer) ? strtoupper($userAnswer) : '';

                if ($correctAns && $userAnsStr === $correctAns) {
                    $isCorrect = true;
                    $earnedThisQuestion = $questionPoints;
                    $correctCount++;
                }
            }

            $earnedPoints += $earnedThisQuestion;

            // 🌟 Build review data per soal (untuk dikirim balik ke Flutter)
            $reviewData[] = [
                'question_id' => $qid,
                'question' => $question->question,
                'question_type' => $type,
                'image_url' => $question->image_url,
                'option_a' => $question->option_a,
                'option_b' => $question->option_b,
                'option_c' => $question->option_c,
                'option_d' => $question->option_d,
                'correct_answer' => $question->correct_answer,
                'correct_answers' => $question->correct_answers ?? [],
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'points_earned' => $earnedThisQuestion,
                'points_max' => $questionPoints,
                'explanation' => $question->explanation,
                'difficulty' => $question->difficulty,
            ];
        }

        // Skor akhir dalam persen
        $score = $maxPoints > 0 ? round(($earnedPoints / $maxPoints) * 100) : 0;
        $passingScore = (int) ($quiz->passing_score ?? 70);
        $isPassed = $score >= $passingScore;

        $attempt = QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $id,
            'score' => $score,
            'earned_points' => $earnedPoints,
            'max_points' => $maxPoints,
            'correct_count' => $correctCount,
            'total_questions' => $allQuestions->count(),
            'time_taken_seconds' => $request->time_taken_seconds,
            'is_passed' => $isPassed,
        ]);

        return response()->json([
            'message' => 'Quiz berhasil disubmit!',
            'score' => $score,
            'earned_points' => $earnedPoints,
            'max_points' => $maxPoints,
            'correct_answers' => $correctCount,
            'total_questions' => $allQuestions->count(),
            'is_passed' => $isPassed,
            'passing_score' => $passingScore,
            'review' => $reviewData, // 🌟 Data lengkap per soal untuk halaman hasil
        ], 200);
    }

    // ============================================================
    // LEADERBOARD & HISTORY (tidak banyak berubah)
    // ============================================================
    public function leaderboard($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        $leaderboard = QuizAttempt::where('quiz_id', $id)
            ->with('user:id,name,kelas,nim')
            ->orderBy('score', 'desc')
            ->orderBy('time_taken_seconds', 'asc')
            ->get();

        return response()->json([
            'message' => 'Berhasil',
            'quiz_title' => $quiz->title,
            'data' => $leaderboard
        ], 200);
    }

    public function getHistory(Request $request)
    {
        $userId = $request->user()->id;
        $attempts = QuizAttempt::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        $quizIds = $attempts->pluck('quiz_id')->toArray();
        $quizzes = Quiz::whereIn('_id', $quizIds)->get()->keyBy('_id');
        $allQuestions = QuizQuestion::whereIn('quiz_id', $quizIds)->get()->groupBy('quiz_id');

        $history = $attempts->map(function ($attempt) use ($quizzes, $allQuestions) {
            $quizData = $quizzes->get($attempt->quiz_id);
            $quizQuestions = $allQuestions->get($attempt->quiz_id, collect());

            $formattedQuestions = $quizQuestions->map(function($q) {
                return [
                    'question' => $q->question,
                    'correct_answer' => $q->correct_answer,
                    'correct_answers' => $q->correct_answers ?? [],
                    'question_type' => $q->question_type ?? 'multiple_choice',
                    'image_url' => $q->image_url,
                    'explanation' => $q->explanation,
                ];
            })->toArray();

            return [
                'quiz_id' => $attempt->quiz_id,
                'title' => $quizData ? $quizData->title : 'Kuis Dihapus',
                'score' => $attempt->score,
                'created_at' => $attempt->created_at,
                'questions' => $formattedQuestions
            ];
        });

        return response()->json(['message' => 'Berhasil', 'data' => $history], 200);
    }
}