<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;

class QuizController extends Controller
{
    // ============================================================
    // ADMIN: LIST QUIZ
    // ============================================================
    public function adminIndex(Request $request)
    {
        try {
            $user  = $request->user();
            $query = Quiz::query();

            if ($user->role === 'dosen') {
            // Ambil semua course_id yang dosen_id-nya match dengan user ini
            $courseIds = \App\Models\Course::where('dosen_id', (string) $user->id)
                ->get()
                ->map(fn($c) => (string) $c->id)
                ->toArray();

            // Fallback ke course_id di user jika tidak ada di Course collection
            if (empty($courseIds) && $user->course_id) {
                $courseIds = [$user->course_id];
            }

            if (!empty($courseIds)) {
                $query->whereIn('course_id', $courseIds);
            }
        }
            if ($request->has('course_id') && $request->course_id) {
                $query->where('course_id', $request->course_id);
            }

            $quizzes = $query->latest()->get();

            $quizzes->transform(function ($quiz) {
                $quiz->questions_count = QuizQuestion::where('quiz_id', $quiz->id)->count();
                if ($quiz->course_id) {
                    $course = \App\Models\Course::find($quiz->course_id);
                    $quiz->course = $course
                        ? ['id' => $course->id, 'title' => $course->title ?? $course->name ?? '-']
                        : null;
                } else {
                    $quiz->course = null;
                }
                return $quiz;
            });

            return response()->json(['message' => 'Berhasil', 'data' => $quizzes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
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
                $quiz->course = $course
                    ? ['id' => $course->id, 'title' => $course->title ?? $course->name ?? '-']
                    : null;
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
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'course_id'       => 'required|string',
            'duration_minutes'=> 'required|integer|min:1|max:300',
            'is_active'       => 'nullable|boolean',
            'start_at'        => 'nullable|date',
            'end_at'          => 'nullable|date|after_or_equal:start_at',
            'passing_score'   => 'nullable|integer|min:0|max:100',
            'visibility'      => 'nullable|in:mahasiswa,umum',
        ]);

        try {
            $quiz = Quiz::create([
                'title'            => $request->title,
                'description'      => $request->description,
                'course_id'        => $request->course_id,
                'created_by'       => auth()->id(),
                'duration_minutes' => (int) $request->duration_minutes,
                'is_active'        => $request->boolean('is_active', true),
                'start_at'         => $request->start_at ? Carbon::parse($request->start_at) : null,
                'end_at'           => $request->end_at   ? Carbon::parse($request->end_at)   : null,
                'passing_score'    => (int) ($request->passing_score ?? 70),
                'visibility'       => $request->input('visibility', 'mahasiswa'),
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
            'title'            => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'course_id'        => 'sometimes|string',
            'duration_minutes' => 'sometimes|integer|min:1|max:300',
            'is_active'        => 'nullable|boolean',
            'start_at'         => 'nullable|date',
            'end_at'           => 'nullable|date|after_or_equal:start_at',
            'passing_score'    => 'nullable|integer|min:0|max:100',
            'visibility'       => 'nullable|in:mahasiswa,umum',
        ]);

        try {
            $updateData = $request->only([
                'title', 'description', 'course_id',
                'duration_minutes', 'passing_score', 'visibility',
            ]);
            if ($request->has('is_active'))
                $updateData['is_active'] = $request->boolean('is_active');
            if ($request->has('start_at'))
                $updateData['start_at'] = $request->start_at ? Carbon::parse($request->start_at) : null;
            if ($request->has('end_at'))
                $updateData['end_at'] = $request->end_at ? Carbon::parse($request->end_at) : null;

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
        return response()->json([
            'message' => $quiz->is_active ? 'Aktif' : 'Nonaktif',
            'data'    => $quiz->fresh(),
        ], 200);
    }

    public function destroy($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        QuizQuestion::where('quiz_id', $id)->each(function ($q) {
            if ($q->image && Storage::disk('public')->exists($q->image)) {
                Storage::disk('public')->delete($q->image);
            }
        });

        QuizQuestion::where('quiz_id', $id)->delete();
        $quiz->delete();
        return response()->json(['message' => 'Quiz dihapus'], 200);
    }

    // ============================================================
    // KELOLA SOAL QUIZ
    // ============================================================
    public function getQuizQuestions($id)
    {
        try {
            $quiz = Quiz::find($id);
            if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

            $questions = QuizQuestion::where('quiz_id', $id)->get()->map(function ($q) {
                $arr = $q->toArray();
                $arr['correct_answer']  = $q->correct_answer;
                $arr['correct_answers'] = $q->correct_answers ?? [];
                $arr['explanation']     = $q->explanation ?? '';
                return $arr;
            });

            return response()->json(['message' => 'Berhasil', 'data' => $questions], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    public function storeQuizQuestion(Request $request, $id)
    {
        // (tidak berubah dari versi asli)
        try {
            $request->validate([
                'question'        => 'required|string',
                'question_type'   => 'required|in:multiple_choice,true_false,multiple_answer',
                'option_a'        => 'required_unless:question_type,true_false|nullable|string',
                'option_b'        => 'required_unless:question_type,true_false|nullable|string',
                'option_c'        => 'nullable|string',
                'option_d'        => 'nullable|string',
                'correct_answer'  => 'nullable|string',
                'correct_answers' => 'nullable',
                'explanation'     => 'nullable|string',
                'points'          => 'nullable|integer|min:1|max:100',
                'difficulty'      => 'nullable|in:mudah,sedang,sulit',
            ]);

            $quiz = Quiz::find($id);
            if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

            $data = [
                'quiz_id'        => $id,
                'question'       => $request->question,
                'question_type'  => $request->question_type,
                'option_a'       => $request->option_a,
                'option_b'       => $request->option_b,
                'option_c'       => $request->option_c ?? '',
                'option_d'       => $request->option_d ?? '',
                'explanation'    => $request->explanation ?? '',
                'points'         => (int) ($request->points ?? 10),
                'difficulty'     => $request->difficulty ?? 'sedang',
            ];

            if ($request->question_type === 'multiple_answer') {
                $correctAnswers = $request->correct_answers;
                if (is_string($correctAnswers)) {
                    $correctAnswers = json_decode($correctAnswers, true) ?? [];
                }
                $data['correct_answers'] = $correctAnswers ?? [];
                $data['correct_answer']  = $correctAnswers[0] ?? 'A';

            } elseif ($request->question_type === 'true_false') {
                $data['option_a']       = 'Benar';
                $data['option_b']       = 'Salah';
                $data['option_c']       = '';
                $data['option_d']       = '';
                $data['correct_answer'] = strtoupper($request->correct_answer ?? 'A');

            } else {
                $data['correct_answer'] = strtoupper($request->correct_answer ?? 'A');
            }

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('quiz_images', 'public');
            }

            $question = QuizQuestion::create($data);
            return response()->json(['message' => 'Soal ditambahkan', 'data' => $question], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyQuizQuestion($id)
    {
        $question = QuizQuestion::find($id);
        if (!$question) return response()->json(['message' => 'Soal tidak ditemukan'], 404);

        if ($question->image && Storage::disk('public')->exists($question->image)) {
            Storage::disk('public')->delete($question->image);
        }
        $question->delete();
        return response()->json(['message' => 'Soal dihapus'], 200);
    }

    // ============================================================
    // ENDPOINT MAHASISWA & PUBLIC
    // ============================================================
    public function index(Request $request)
    {
        $user   = $request->user();
        $userId = $user->id;
        $role   = $user->role ?? 'public';
        $now    = Carbon::now();

        $attemptedQuizIds = QuizAttempt::where('user_id', $userId)->pluck('quiz_id')->toArray();

        $query = Quiz::where('is_active', true)
            ->whereNotIn('_id', $attemptedQuizIds)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });

        if ($request->has('course_id') && $request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        // Public hanya lihat quiz visibility 'umum'
        if ($role === 'public') {
            $query->where('visibility', 'umum');
        }

        $quizzes = $query->get();

        $quizzes->transform(function ($quiz) {
            if ($quiz->course_id) {
                $course = \App\Models\Course::find($quiz->course_id);
                $quiz->course = $course
                    ? ['id' => $course->id, 'title' => $course->title ?? $course->name ?? '-']
                    : null;
            }
            return $quiz;
        });

        return response()->json(['message' => 'Berhasil', 'data' => $quizzes], 200);
    }

    public function getQuestions(Request $request, $id)
    {
        $user = $request->user();
        $role = $user->role ?? 'public';

        $quiz = Quiz::find($id);
        if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

        // Public cek visibility
        if ($role === 'public') {
            $visibility = $quiz->attributes['visibility'] ?? 'mahasiswa';
            if ($visibility !== 'umum') {
                return response()->json(['message' => 'Quiz ini hanya untuk mahasiswa.'], 403);
            }
        }

        if (!$quiz->is_accessible) {
            $msg = match ($quiz->status) {
                'nonaktif'      => 'Quiz ini sedang dinonaktifkan oleh dosen.',
                'belum_mulai'   => 'Quiz ini belum dimulai. Silakan kembali pada ' .
                    ($quiz->start_at ? $quiz->start_at->format('d M Y H:i') : 'jadwal yang ditentukan') . '.',
                'sudah_selesai' => 'Quiz ini sudah berakhir.',
                default         => 'Quiz tidak dapat diakses saat ini.',
            };
            return response()->json(['message' => $msg], 403);
        }

        $questions = QuizQuestion::where('quiz_id', $id)->get();
        $questions  = $questions->map(function ($q) {
            $arr = $q->toArray();
            unset($arr['correct_answer'], $arr['correct_answers'], $arr['explanation']);
            return $arr;
        });
        $questions = $questions->shuffle()->values();

        return response()->json([
            'message'    => 'Berhasil',
            'quiz_title' => $quiz->title,
            'duration'   => $quiz->duration_minutes,
            'data'       => $questions,
        ], 200);
    }

    public function submitQuiz(Request $request, $id)
    {
        $userId = auth('sanctum')->id();
        if (!$userId) return response()->json(['message' => 'Identitas tidak ditemukan.'], 401);

        $request->validate([
            'time_taken_seconds' => 'required|integer',
            'answers'            => 'required|array',
            'answers.*.question_id' => 'required|string',
            'answers.*.answer'      => 'nullable',
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
        $maxPoints    = 0;
        $earnedPoints = 0;
        $correctCount = 0;
        $reviewData   = [];
        $userAnswers  = collect($request->answers)->keyBy('question_id');

        foreach ($allQuestions as $qid => $question) {
            $questionPoints = (int) ($question->points ?? 10);
            $maxPoints     += $questionPoints;

            $userAns    = $userAnswers->get($qid);
            $userAnswer = $userAns ? $userAns['answer'] : null;
            $type       = $question->question_type ?? 'multiple_choice';

            $isCorrect      = false;
            $pointsEarned   = 0;

            if ($type === 'multiple_answer') {
                $correctList = array_map('strtoupper', $question->correct_answers ?? []);
                $userList    = array_map('strtoupper', is_array($userAnswer) ? $userAnswer : []);
                sort($correctList);
                sort($userList);

                if (!empty($userList)) {
                    $correctHits = count(array_intersect($userList, $correctList));
                    $wrongHits   = count(array_diff($userList, $correctList));
                    $partial     = max(0, $correctHits - $wrongHits) / max(1, count($correctList));
                    $pointsEarned = (int) round($questionPoints * $partial);
                    $isCorrect    = $correctList === $userList;
                }
            } else {
                $correctAns = strtoupper($question->correct_answer ?? '');
                $userAnsUC  = strtoupper(is_string($userAnswer) ? $userAnswer : '');
                $isCorrect  = ($userAnsUC === $correctAns && $userAnsUC !== '');
                $pointsEarned = $isCorrect ? $questionPoints : 0;
            }

            if ($isCorrect) $correctCount++;
            $earnedPoints += $pointsEarned;

            $reviewData[] = [
                'question'       => $question->question,
                'question_type'  => $type,
                'image_url'      => $question->image_url,
                'option_a'       => $question->option_a,
                'option_b'       => $question->option_b,
                'option_c'       => $question->option_c,
                'option_d'       => $question->option_d,
                'correct_answer' => $question->correct_answer,
                'correct_answers'=> $question->correct_answers ?? [],
                'explanation'    => $question->explanation ?? '',
                'user_answer'    => $userAnswer,
                'is_correct'     => $isCorrect,
                'points_earned'  => $pointsEarned,
                'points_max'     => $questionPoints,
            ];
        }

        $score        = $maxPoints > 0 ? round(($earnedPoints / $maxPoints) * 100) : 0;
        $passingScore = (int) ($quiz->passing_score ?? 70);
        $isPassed     = $score >= $passingScore;

        $attempt = QuizAttempt::create([
            'user_id'          => $userId,
            'quiz_id'          => $id,
            'score'            => $score,
            'earned_points'    => $earnedPoints,
            'max_points'       => $maxPoints,
            'correct_count'    => $correctCount,
            'total_questions'  => $allQuestions->count(),
            'time_taken_seconds' => $request->time_taken_seconds,
            'is_passed'        => $isPassed,
        ]);

        return response()->json([
            'message'          => 'Quiz berhasil disubmit!',
            'score'            => $score,
            'earned_points'    => $earnedPoints,
            'max_points'       => $maxPoints,
            'correct_answers'  => $correctCount,
            'total_questions'  => $allQuestions->count(),
            'is_passed'        => $isPassed,
            'passing_score'    => $passingScore,
            'review'           => $reviewData,
        ], 200);
    }

    // ============================================================
    // LEADERBOARD — tab mahasiswa vs umum
    // ============================================================
    public function leaderboard(Request $request, $id)
{
    $quiz = Quiz::find($id);
    if (!$quiz) return response()->json(['message' => 'Quiz tidak ditemukan'], 404);

    $tab = $request->query('tab', 'mahasiswa');

    // Ambil semua attempt untuk quiz ini
    $attempts = QuizAttempt::where('quiz_id', $id)
        ->orderBy('score', 'desc')
        ->orderBy('time_taken_seconds', 'asc')
        ->get();

    if ($attempts->isEmpty()) {
        return response()->json([
            'message'    => 'Berhasil',
            'quiz_title' => $quiz->title,
            'tab'        => $tab,
            'data'       => [],
        ], 200);
    }

    // Manual lookup user — MongoDB tidak support with() cross-collection
    $userIds = $attempts->pluck('user_id')->unique()->toArray();
    $users   = User::whereIn('_id', $userIds)->get()
                   ->keyBy(fn($u) => (string) $u->_id);

    // Filter berdasarkan tab
    $filtered = $attempts->filter(function ($attempt) use ($users, $tab) {
        $user = $users->get((string) $attempt->user_id);
        if (!$user) return false;
        if ($tab === 'mahasiswa') return $user->role === 'mahasiswa';
        if ($tab === 'umum')      return $user->role === 'public';
        return true;
    });

    // Gabungkan data user ke attempt
    $result = $filtered->values()->map(function ($attempt) use ($users) {
        $user = $users->get((string) $attempt->user_id);
        return [
            'user_id'            => $attempt->user_id,
            'score'              => $attempt->score,
            'earned_points'      => $attempt->earned_points ?? 0,
            'max_points'         => $attempt->max_points ?? 0,
            'correct_count'      => $attempt->correct_count ?? 0,
            'total_questions'    => $attempt->total_questions ?? 0,
            'time_taken_seconds' => $attempt->time_taken_seconds ?? 0,
            'is_passed'          => $attempt->is_passed ?? false,
            'created_at'         => $attempt->created_at,
            'user' => $user ? [
                'id'    => (string) $user->_id,
                'name'  => $user->name,
                'nim'   => $user->nim ?? null,
                'kelas' => $user->kelas ?? null,
                'role'  => $user->role,
            ] : null,
        ];
    });

    return response()->json([
        'message'    => 'Berhasil',
        'quiz_title' => $quiz->title,
        'tab'        => $tab,
        'data'       => $result,
    ], 200);
}

    // ============================================================
    // HISTORY QUIZ USER
    // ============================================================
    public function getHistory(Request $request)
    {
        $userId   = $request->user()->id;
        $attempts = QuizAttempt::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        $quizIds  = $attempts->pluck('quiz_id')->toArray();
        $quizzes  = Quiz::whereIn('_id', $quizIds)->get()->keyBy('_id');
        $allQs    = QuizQuestion::whereIn('quiz_id', $quizIds)->get()->groupBy('quiz_id');

        $history = $attempts->map(function ($attempt) use ($quizzes, $allQs) {
            $quizData  = $quizzes->get($attempt->quiz_id);
            $quizQs    = $allQs->get($attempt->quiz_id, collect());

            $formatted = $quizQs->map(fn($q) => [
                'question'       => $q->question,
                'correct_answer' => $q->correct_answer,
                'correct_answers'=> $q->correct_answers ?? [],
                'question_type'  => $q->question_type ?? 'multiple_choice',
                'image_url'      => $q->image_url,
                'explanation'    => $q->explanation,
            ])->toArray();

            return [
                'quiz_id'          => $attempt->quiz_id,
                'title'            => $quizData ? ($quizData->title ?? 'Quiz') : 'Quiz Dihapus',
                'score'            => $attempt->score,
                'earned_points'    => $attempt->earned_points ?? 0,
                'max_points'       => $attempt->max_points ?? 0,
                'correct_count'    => $attempt->correct_count ?? 0,
                'total_questions'  => $attempt->total_questions ?? 0,
                'is_passed'        => $attempt->is_passed ?? false,
                'time_taken_seconds' => $attempt->time_taken_seconds ?? 0,
                'created_at'       => $attempt->created_at,
                'questions'        => $formatted,
            ];
        });

        return response()->json(['message' => 'Berhasil', 'data' => $history], 200);
    }

    public function duelQuizList(Request $request)
    {
        $now  = Carbon::now();
        $role = $request->user()->role ?? 'public';

        $query = Quiz::where('is_active', true)
            ->where('is_duel_enabled', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });

        if ($role === 'public') {
            $query->where('visibility', 'umum');
        }

        $quizzes = $query->get()->map(function ($quiz) {
            $course = $quiz->course_id
                ? \App\Models\Course::find($quiz->course_id)
                : null;
            return [
                'id'    => (string) $quiz->id,
                'title' => $quiz->title,
                'course'=> $course
                    ? ['id' => (string) $course->id,
                    'title' => $course->title ?? $course->name ?? '-']
                    : null,
                'duration_minutes' => $quiz->duration_minutes,
                'passing_score'    => $quiz->passing_score ?? 70,
                'questions_count'  => \App\Models\QuizQuestion::where(
                    'quiz_id', (string) $quiz->id)->count(),
            ];
        });

        return response()->json(['message' => 'Berhasil', 'data' => $quizzes]);
    }

    // ── 1. Toggle is_duel_enabled ────────────────────────────────
    // Tambah setelah method toggleActive():
    
    public function toggleDuel($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz tidak ditemukan'], 404);
        }
    
        // Pastikan hanya dosen yang mengampu matkul ini yang bisa toggle
        $user = auth('sanctum')->user();
        if ($user->role === 'dosen') {
            $courseIds = \App\Models\Course::where('dosen_ids', (string) $user->id)
                ->get()->map(fn($c) => (string) $c->id)->toArray();
            if (!in_array((string) $quiz->course_id, $courseIds)) {
                return response()->json(['message' => 'Akses ditolak'], 403);
            }
        }
    
        $newVal = !($quiz->is_duel_enabled ?? false);
        $quiz->update(['is_duel_enabled' => $newVal]);
    
        return response()->json([
            'message'          => $newVal ? 'Kuis diaktifkan untuk duel' : 'Kuis dinonaktifkan dari duel',
            'is_duel_enabled'  => $newVal,
            'data'             => $quiz->fresh(),
        ]);
    }
    
    public function duelHistory(Request $request)
    {
        $user    = $request->user();
        $isAdmin = in_array($user->role, ['admin', 'superadmin']);

        // Tentukan quiz yang boleh dilihat
        if ($isAdmin) {
            // Admin: semua quiz
            $quizzes  = \App\Models\Quiz::all();
            $quizIds  = $quizzes->map(fn($q) => (string) $q->id)->toArray();
            $courseIds = $quizzes->pluck('course_id')->unique()->filter()->values()->toArray();
        } else {
            // Dosen: hanya courses yang dosen_ids-nya mengandung ID dosen ini
            $courses = \App\Models\Course::where('dosen_ids', (string) $user->id)->get();

            // Fallback ke course_id singular jika belum migrasi
            if ($courses->isEmpty() && $user->course_id) {
                $courses = \App\Models\Course::whereIn('_id', [$user->course_id])->get();
            }

            $courseIds = $courses->map(fn($c) => (string) $c->id)->toArray();

            if (empty($courseIds)) {
                return response()->json(['message' => 'Berhasil', 'data' => [], 'courses' => []]);
            }

            $quizzes = \App\Models\Quiz::whereIn('course_id', $courseIds)->get();
            $quizIds = $quizzes->map(fn($q) => (string) $q->id)->toArray();

            if (empty($quizIds)) {
                return response()->json(['message' => 'Berhasil', 'data' => [], 'courses' => []]);
            }
        }

        $duels = \App\Models\Duel::whereIn('quiz_id', $quizIds)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        // Build lookup maps
        $userIds = $duels->pluck('challenger_id')->merge($duels->pluck('opponent_id'))
            ->push($duels->pluck('winner_id'))->flatten()->unique()->filter()->toArray();
        $users   = \App\Models\User::whereIn('_id', $userIds)->get()
            ->keyBy(fn($u) => (string) $u->id);
        $quizMap = $quizzes->keyBy(fn($q) => (string) $q->id);

        // Build course map for the response
        $courseMap = \App\Models\Course::whereIn('_id', $courseIds)->get()
            ->keyBy(fn($c) => (string) $c->id);

        $result = $duels->map(function ($duel) use ($users, $quizMap, $courseMap) {
            $challenger = $users->get((string) $duel->challenger_id);
            $opponent   = $users->get((string) $duel->opponent_id);
            $quiz       = $quizMap->get((string) $duel->quiz_id);
            $winner     = $duel->winner_id ? $users->get((string) $duel->winner_id) : null;
            $courseId   = $quiz?->course_id ? (string) $quiz->course_id : null;
            $course     = $courseId ? $courseMap->get($courseId) : null;

            return [
                'id'               => (string) $duel->id,
                'quiz_title'       => $quiz?->title ?? 'Quiz Dihapus',
                'course_id'        => $courseId,
                'course_title'     => $course?->title ?? '—',
                'status'           => $duel->status,
                'challenger_name'  => $challenger?->name ?? '-',
                'challenger_nim'   => $challenger?->nim ?? '-',
                'opponent_name'    => $opponent?->name ?? '-',
                'opponent_nim'     => $opponent?->nim ?? '-',
                'challenger_score' => $duel->challenger_score,
                'opponent_score'   => $duel->opponent_score,
                'winner_name'      => $winner?->name,
                'created_at'       => $duel->created_at,
            ];
        });

        // Kirim daftar course untuk dropdown filter
        $coursesForFilter = $courseMap->values()->map(fn($c) => [
            'id'    => (string) $c->id,
            'title' => $c->title,
        ])->values();

        return response()->json([
            'message' => 'Berhasil',
            'data'    => $result,
            'courses' => $coursesForFilter,
        ]);
    }
}