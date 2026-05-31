<?php
// app/Http/Controllers/Api/DuelController.php (versi lengkap dengan duel_code + cancel + waiting room)
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Duel;
use App\Models\DuelAnswer;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class DuelController extends Controller
{
    public function __construct(
        private readonly NotificationService $notif
    ) {}

    // ── Helper generate kode unik ──────────────────────────────
    private function _generateDuelCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (User::where('duel_code', $code)->exists());
        return $code;
    }

    // ============================================================
    // GET /api/user/duel-code — lihat kode duel sendiri
    // ============================================================
    public function myDuelCode(Request $request)
    {
        $user = $request->user();

        // Generate kalau belum punya
        if (empty($user->duel_code)) {
            $code = $this->_generateDuelCode();
            $user->update(['duel_code' => $code]);
            $user = $user->fresh();
        }

        return response()->json([
            'message'   => 'Berhasil',
            'duel_code' => $user->duel_code,
            'role'      => $user->role,
        ]);
    }

    // POST /api/user/regenerate-duel-code
    public function regenerateDuelCode(Request $request)
    {
        $user = $request->user();
        $code = $this->_generateDuelCode();
        $user->update(['duel_code' => $code]);
        return response()->json([
            'message'   => 'Kode duel diperbarui',
            'duel_code' => $code,
        ]);
    }

    // ============================================================
    // POST /api/duels/challenge
    // Body: { quiz_id, opponent_identifier, search_by: 'nim'|'duel_code' }
    // ============================================================
    public function challenge(Request $request)
    {
        $request->validate([
            'quiz_id'             => 'required|string',
            'opponent_identifier' => 'required|string',
            'search_by'           => 'required|in:nim,duel_code',
        ]);

        $challenger = $request->user();

        // Cari lawan by NIM atau duel_code
        $opponent = null;
        if ($request->search_by === 'nim') {
            $opponent = User::where('nim', $request->opponent_identifier)
                ->where('role', 'mahasiswa')
                ->first();
        } else {
            $opponent = User::where('duel_code',
                strtoupper($request->opponent_identifier))->first();
        }

        if (!$opponent) {
            $msg = $request->search_by === 'nim'
                ? 'Mahasiswa dengan NIM tersebut tidak ditemukan.'
                : 'Pengguna dengan Kode Duel tersebut tidak ditemukan.';
            return response()->json(['message' => $msg], 404);
        }

        if ((string) $opponent->id === (string) $challenger->id) {
            return response()->json([
                'message' => 'Kamu tidak bisa menantang dirimu sendiri.'], 422);
        }

        $quiz = Quiz::find($request->quiz_id);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz tidak ditemukan.'], 404);
        }

        // Cek sudah ada duel aktif/pending
        $existing = Duel::where('quiz_id', $request->quiz_id)
            ->where(function ($q) use ($challenger, $opponent) {
                $q->where(function ($i) use ($challenger, $opponent) {
                    $i->where('challenger_id', (string) $challenger->id)
                      ->where('opponent_id',   (string) $opponent->id);
                })->orWhere(function ($i) use ($challenger, $opponent) {
                    $i->where('challenger_id', (string) $opponent->id)
                      ->where('opponent_id',   (string) $challenger->id);
                });
            })
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Sudah ada duel aktif dengan lawan ini.'], 422);
        }

        $duel = Duel::create([
            'quiz_id'        => (string) $quiz->id,
            'challenger_id'  => (string) $challenger->id,
            'opponent_id'    => (string) $opponent->id,
            'status'         => 'pending',
            'challenger_score' => null,
            'opponent_score'   => null,
            'challenger_time'  => null,
            'opponent_time'    => null,
            'winner_id'        => null,
            'expires_at'       => Carbon::now()->addMinutes(5)->toISOString(),
        ]);

        $this->notif->sendToUser(
            (string) $opponent->id,
            '⚔️ Tantangan Duel!',
            "{$challenger->name} menantangmu untuk duel \"{$quiz->title}\"!",
            [
                'type'       => 'duel_challenge',
                'duel_id'    => (string) $duel->id,
                'quiz_title' => $quiz->title,
                'challenger_name' => $challenger->name,
            ]
        );

        return response()->json([
            'message' => 'Tantangan berhasil dikirim!',
            'data'    => $this->_formatDuel($duel, $challenger),
        ], 201);
    }

    // ============================================================
    // DELETE /api/duels/{id}/cancel — batalkan tantangan
    // ============================================================
    public function cancel(Request $request, string $id)
    {
        $user = $request->user();
        $duel = Duel::find($id);

        if (!$duel) {
            return response()->json(['message' => 'Duel tidak ditemukan.'], 404);
        }

        if ((string) $duel->challenger_id !== (string) $user->id) {
            return response()->json([
                'message' => 'Hanya challenger yang bisa membatalkan.'], 403);
        }

        if (!$duel->isPending()) {
            return response()->json([
                'message' => 'Duel sudah tidak bisa dibatalkan.'], 422);
        }

        $duel->update(['status' => 'cancelled']);

        $this->notif->sendToUser(
            (string) $duel->opponent_id,
            '❌ Tantangan Dibatalkan',
            "{$user->name} membatalkan tantangan duel.",
            ['type' => 'duel_cancelled', 'duel_id' => (string) $duel->id]
        );

        return response()->json(['message' => 'Tantangan berhasil dibatalkan.']);
    }

    // ============================================================
    // POST /api/duels/{id}/respond
    // Body: { action: 'accept'|'decline' }
    // ============================================================
    public function respond(Request $request, string $id)
    {
        $request->validate(['action' => 'required|in:accept,decline']);

        $user = $request->user();
        $duel = Duel::find($id);

        if (!$duel) return response()->json(['message' => 'Duel tidak ditemukan.'], 404);

        if ((string) $duel->opponent_id !== (string) $user->id) {
            return response()->json(['message' => 'Kamu bukan lawan di duel ini.'], 403);
        }

        if (!$duel->isPending()) {
            return response()->json(['message' => 'Duel tidak bisa direspons.'], 422);
        }

        if ($duel->expires_at && Carbon::parse($duel->expires_at)->isPast()) {
            $duel->update(['status' => 'expired']);
            return response()->json(['message' => 'Waktu accept sudah habis.'], 422);
        }

        if ($request->action === 'decline') {
            $duel->update(['status' => 'declined']);
            $this->notif->sendToUser(
                (string) $duel->challenger_id,
                '❌ Tantangan Ditolak',
                "{$user->name} menolak tantangan duelmu.",
                ['type' => 'duel_declined', 'duel_id' => (string) $duel->id]
            );
            return response()->json(['message' => 'Tantangan ditolak.']);
        }

        // Accept — aktifkan duel, expires 30 menit untuk pengerjaan
        $duel->update([
            'status'     => 'active',
            'expires_at' => Carbon::now()->addMinutes(30)->toISOString(),
        ]);

        $this->notif->sendToUser(
            (string) $duel->challenger_id,
            '✅ Tantangan Diterima!',
            "{$user->name} menerima tantanganmu! Segera kerjakan.",
            [
                'type'    => 'duel_accepted',
                'duel_id' => (string) $duel->id,
            ]
        );

        return response()->json([
            'message' => 'Tantangan diterima! Duel dimulai.',
            'data'    => $this->_formatDuel($duel->fresh(), $user),
        ]);
    }

    // ============================================================
    // GET /api/duels/{id}/questions
    // ============================================================
    public function getQuestions(string $id, Request $request)
    {
        $user = $request->user();
        $duel = Duel::find($id);

        if (!$duel) return response()->json(['message' => 'Duel tidak ditemukan.'], 404);

        $myId = (string) $user->id;
        if ($myId !== (string) $duel->challenger_id &&
            $myId !== (string) $duel->opponent_id) {
            return response()->json(['message' => 'Bukan peserta duel ini.'], 403);
        }

        if (!$duel->isActive()) {
            return response()->json(['message' => 'Duel belum aktif.'], 422);
        }

        $alreadyAnswered = DuelAnswer::where('duel_id', (string) $duel->id)
            ->where('user_id', $myId)->exists();

        if ($alreadyAnswered) {
            return response()->json(['message' => 'Sudah mengerjakan.'], 422);
        }

        $questions = QuizQuestion::where('quiz_id', $duel->quiz_id)
            ->get()->map(function ($q) {
                $arr = $q->toArray();
                unset($arr['correct_answer'], $arr['correct_answers'],
                      $arr['explanation']);
                return $arr;
            })->shuffle()->values();

        $quiz = Quiz::find($duel->quiz_id);

        return response()->json([
            'message'         => 'Berhasil',
            'duel_id'         => (string) $duel->id,
            'quiz_title'      => $quiz?->title ?? 'Duel Quiz',
            'duration'        => $quiz?->duration_minutes ?? 15,
            'total_questions' => $questions->count(),
            'data'            => $questions,
        ]);
    }

    // ============================================================
    // POST /api/duels/{id}/submit
    // ============================================================
    public function submit(Request $request, string $id)
    {
        $request->validate([
            'time_taken_seconds'        => 'required|integer|min:0',
            'answers'                   => 'required|array',
            'answers.*.question_id'     => 'required|string',
            'answers.*.answer'          => 'nullable',
        ]);

        $user = $request->user();
        $duel = Duel::find($id);

        if (!$duel) return response()->json(['message' => 'Duel tidak ditemukan.'], 404);

        $myId         = (string) $user->id;
        $isChallenger = $myId === (string) $duel->challenger_id;
        $isOpponent   = $myId === (string) $duel->opponent_id;

        if (!$isChallenger && !$isOpponent) {
            return response()->json(['message' => 'Bukan peserta duel ini.'], 403);
        }

        if (!$duel->isActive()) {
            return response()->json(['message' => 'Duel tidak aktif.'], 422);
        }

        $alreadyAnswered = DuelAnswer::where('duel_id', (string) $duel->id)
            ->where('user_id', $myId)->exists();

        if ($alreadyAnswered) {
            return response()->json(['message' => 'Sudah mengerjakan.'], 422);
        }

        $questions    = QuizQuestion::where('quiz_id', $duel->quiz_id)
            ->get()->keyBy('_id');
        $totalQ       = $questions->count();
        $correctCount = 0;
        $answersData  = [];

        foreach ($request->answers as $ans) {
            $qid      = $ans['question_id'];
            $question = $questions->get($qid);
            if (!$question) continue;

            $type = $question->question_type ?? 'multiple_choice';
            if ($type === 'multiple_answer') {
                $userList    = is_array($ans['answer']) ? $ans['answer'] : [];
                $userList    = array_map(fn($a) => strtoupper(trim($a)), $userList);
                $correctList = array_map('strtoupper', $question->correct_answers ?? []);
                sort($userList);
                sort($correctList);
                $isCorrect = !empty($userList) && $userList === $correctList;
            } else {
                $raw        = is_array($ans['answer']) ? '' : ($ans['answer'] ?? '');
                $userAns    = strtoupper(trim($raw));
                $correctAns = strtoupper(trim($question->correct_answer ?? ''));
                $isCorrect  = $userAns === $correctAns && $userAns !== '';
            }

            if ($isCorrect) $correctCount++;

            $answersData[] = [
                'duel_id'     => (string) $duel->id,
                'user_id'     => $myId,
                'question_id' => $qid,
                'answer'      => $ans['answer'],
                'is_correct'  => $isCorrect,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        if (!empty($answersData)) DuelAnswer::insert($answersData);

        $score     = $totalQ > 0 ? round(($correctCount / $totalQ) * 100, 1) : 0;
        $timeTaken = (int) $request->time_taken_seconds;
        $now       = now()->toISOString();

        if ($isChallenger) {
            $duel->update([
                'challenger_score'       => $score,
                'challenger_time'        => $timeTaken,
                'challenger_answered_at' => $now,
            ]);
        } else {
            $duel->update([
                'opponent_score'       => $score,
                'opponent_time'        => $timeTaken,
                'opponent_answered_at' => $now,
            ]);
        }

        $duel     = $duel->fresh();
        $bothDone = $duel->challenger_score !== null &&
                    $duel->opponent_score   !== null;

        if ($bothDone) {
            $this->_finalizeDuel($duel);
            $duel = $duel->fresh();
        }

        return response()->json([
            'message'          => 'Jawaban berhasil dikirim!',
            'your_score'       => $score,
            'correct_count'    => $correctCount,
            'total'            => $totalQ,
            'duel_status'      => $duel->status,
            'waiting_opponent' => !$bothDone,
        ]);
    }

    // ============================================================
    // GET /api/duels/{id}/status — polling
    // ============================================================
    public function status(Request $request, string $id)
    {
        $user = $request->user();
        $duel = Duel::find($id);

        if (!$duel) return response()->json(['message' => 'Duel tidak ditemukan.'], 404);

        $myId = (string) $user->id;
        if ($myId !== (string) $duel->challenger_id &&
            $myId !== (string) $duel->opponent_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($duel->isActive() && $duel->expires_at &&
            Carbon::parse($duel->expires_at)->isPast()) {
            $this->_expireDuel($duel);
            $duel = $duel->fresh();
        }

        return response()->json([
            'message' => 'Berhasil',
            'data'    => $this->_formatDuel($duel, $user),
        ]);
    }

    // ============================================================
    // GET /api/duels — list aktif+pending
    // ============================================================
    public function index(Request $request)
    {
        $myId  = (string) $request->user()->id;
        $duels = Duel::where(function ($q) use ($myId) {
            $q->where('challenger_id', $myId)
              ->orWhere('opponent_id', $myId);
        })->whereIn('status', ['pending', 'active'])
          ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil',
            'data'    => $duels->map(fn($d) =>
                $this->_formatDuel($d, $request->user())),
        ]);
    }

    // ============================================================
    // GET /api/duels/history
    // ============================================================
    public function history(Request $request)
    {
        $myId  = (string) $request->user()->id;
        $duels = Duel::where(function ($q) use ($myId) {
            $q->where('challenger_id', $myId)
              ->orWhere('opponent_id', $myId);
        })->whereIn('status', ['completed', 'expired', 'declined', 'cancelled'])
          ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil',
            'data'    => $duels->map(fn($d) =>
                $this->_formatDuel($d, $request->user())),
        ]);
    }

    // ── Private helpers ────────────────────────────────────────

    private function _finalizeDuel(Duel $duel): void
    {
        $cScore = (float) $duel->challenger_score;
        $oScore = (float) $duel->opponent_score;
        $cTime  = (int)   $duel->challenger_time;
        $oTime  = (int)   $duel->opponent_time;

        $winnerId = $cScore > $oScore ? $duel->challenger_id
            : ($oScore > $cScore ? $duel->opponent_id
            : ($cTime <= $oTime ? $duel->challenger_id : $duel->opponent_id));

        $duel->update(['status' => 'completed', 'winner_id' => $winnerId]);

        $winner = User::find($winnerId);
        $winnerName = $winner?->name ?? 'Seseorang';

        $this->notif->sendToUser((string) $duel->challenger_id,
            '🏆 Duel Selesai!',
            "Pemenang: {$winnerName} | Skormu: {$cScore} vs {$oScore}",
            ['type' => 'duel_completed', 'duel_id' => (string) $duel->id]);

        $this->notif->sendToUser((string) $duel->opponent_id,
            '🏆 Duel Selesai!',
            "Pemenang: {$winnerName} | Skormu: {$oScore} vs {$cScore}",
            ['type' => 'duel_completed', 'duel_id' => (string) $duel->id]);
    }

    private function _expireDuel(Duel $duel): void
    {
        $duel->update(['status' => 'expired']);
        foreach ([$duel->challenger_id, $duel->opponent_id] as $uid) {
            $this->notif->sendToUser((string) $uid,
                '⏱️ Duel Kedaluwarsa',
                'Salah satu peserta tidak menyelesaikan duel.',
                ['type' => 'duel_expired', 'duel_id' => (string) $duel->id]);
        }
    }

    private function _formatDuel(Duel $duel, User $currentUser): array
    {
        $myId         = (string) $currentUser->id;
        $isChallenger = $myId === (string) $duel->challenger_id;
        $challenger   = User::find($duel->challenger_id);
        $opponent     = User::find($duel->opponent_id);
        $quiz         = Quiz::find($duel->quiz_id);
        $winner       = $duel->winner_id ? User::find($duel->winner_id) : null;
    
        $challengerReady = (bool) ($duel->challenger_ready ?? false);
        $opponentReady   = (bool) ($duel->opponent_ready   ?? false);
    
        // my_ready = apakah saya sudah ready
        // opponent_ready_status = apakah lawan sudah ready
        $myReady   = $isChallenger ? $challengerReady : $opponentReady;
        $oppReady  = $isChallenger ? $opponentReady   : $challengerReady;
    
        return [
            'id'                     => (string) $duel->id,
            'quiz_id'                => $duel->quiz_id,
            'quiz_title'             => $quiz?->title ?? 'Quiz',
            'status'                 => $duel->status,
            'is_my_turn'             => $this->_isMyTurn($duel, $myId),
            'i_am'                   => $isChallenger ? 'challenger' : 'opponent',
            'my_score'               => $isChallenger
                ? $duel->challenger_score : $duel->opponent_score,
            'opponent_score'         => $isChallenger
                ? $duel->opponent_score  : $duel->challenger_score,
            'my_time'                => $isChallenger
                ? $duel->challenger_time : $duel->opponent_time,
            'winner_id'              => $duel->winner_id,
            'winner_name'            => $winner?->name,
            'i_won'                  => $duel->winner_id
                ? ($myId === (string) $duel->winner_id) : null,
    
            // ── Ready system ──────────────────────────────────
            'my_ready'               => $myReady,
            'opponent_ready_status'  => $oppReady,
            'both_ready'             => $myReady && $oppReady,
            'battle_starts_at'       => $duel->battle_starts_at ?? null,
    
            // ── Players ───────────────────────────────────────
            'challenger' => $challenger ? [
                'id'        => (string) $challenger->id,
                'name'      => $challenger->name,
                'nim'       => $challenger->nim,
                'duel_code' => $challenger->duel_code,
            ] : null,
            'opponent' => $opponent ? [
                'id'        => (string) $opponent->id,
                'name'      => $opponent->name,
                'nim'       => $opponent->nim,
                'duel_code' => $opponent->duel_code,
            ] : null,
    
            'expires_at'  => $duel->expires_at,
            'created_at'  => $duel->created_at,
        ];
    }

    private function _isMyTurn(Duel $duel, string $myId): bool
    {
        if (!$duel->isActive()) return false;
        $isChallenger = $myId === (string) $duel->challenger_id;
        return $isChallenger
            ? $duel->challenger_score === null
            : $duel->opponent_score === null;
    }

    // ============================================================
    // POST /api/duels/{id}/ready
    // Menandai user sudah siap — countdown mulai saat keduanya ready
    // ============================================================
    public function ready(Request $request, string $id)
    {
        $user = $request->user();
        $duel = Duel::find($id);
 
        if (!$duel) {
            return response()->json(['message' => 'Duel tidak ditemukan.'], 404);
        }
 
        $myId         = (string) $user->id;
        $isChallenger = $myId === (string) $duel->challenger_id;
        $isOpponent   = $myId === (string) $duel->opponent_id;
 
        if (!$isChallenger && !$isOpponent) {
            return response()->json(['message' => 'Bukan peserta duel ini.'], 403);
        }
 
        if ($duel->status !== 'active') {
            return response()->json(['message' => 'Duel belum aktif.'], 422);
        }
 
        // Set ready flag
        if ($isChallenger) {
            $duel->update(['challenger_ready' => true]);
        } else {
            $duel->update(['opponent_ready' => true]);
        }
 
        $duel = $duel->fresh();
 
        // Cek apakah keduanya sudah ready
        $bothReady = $duel->challenger_ready && $duel->opponent_ready;
 
        if ($bothReady) {
            // Set battle_starts_at = 3 detik dari sekarang
            $duel->update([
                'battle_starts_at' => Carbon::now()->addSeconds(3)->toISOString(),
            ]);
        }
 
        return response()->json([
            'message'          => 'Ready!',
            'challenger_ready' => (bool) $duel->challenger_ready,
            'opponent_ready'   => (bool) $duel->opponent_ready,
            'both_ready'       => $bothReady,
            'battle_starts_at' => $duel->battle_starts_at,
        ]);
    }
}