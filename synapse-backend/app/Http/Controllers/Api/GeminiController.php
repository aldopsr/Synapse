<?php
// app/Http/Controllers/Api/GeminiController.php
// Endpoint AI untuk generate soal dan deskripsi materi menggunakan Gemini API
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Quiz;
use App\Models\Material;

class GeminiController extends Controller
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    private QuotaService $quota;

    public function __construct(QuotaService $quota)
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->quota  = $quota;
    }

    // ============================================================
    // POST /api/ai/generate-questions
    // Generate soal kuis otomatis
    // Body: {
    //   quiz_id: string (opsional),
    //   topic: string (opsional),
    //   material_id: string (opsional),
    //   counts: { multiple_choice: 3, true_false: 2, multiple_answer: 2 },
    //   difficulty: 'mudah'|'sedang'|'sulit' (opsional, default 'sedang')
    // }
    // ============================================================
    public function generateQuestions(Request $request)
    {
        $request->validate([
            'counts'                      => 'required|array',
            'counts.multiple_choice'      => 'nullable|integer|min:0|max:20',
            'counts.true_false'           => 'nullable|integer|min:0|max:20',
            'counts.multiple_answer'      => 'nullable|integer|min:0|max:20',
            'topic'                       => 'nullable|string|max:500',
            'quiz_id'                     => 'nullable|string',
            'material_id'                 => 'nullable|string',
            'difficulty'                  => 'nullable|in:mudah,sedang,sulit',
        ]);

        $user = $request->user();

        // Check daily generate quota
        if ($this->quota->isExhausted($user, 'generate_questions')) {
            $info = $this->quota->info($user, 'generate_questions');
            return response()->json([
                'message'      => 'Kuota generate soal harian habis. Reset esok hari pukul 00.00 WIB.',
                'remaining'    => 0,
                'limit'        => $info['limit'],
                'notification' => $info['notification'],
            ], 429);
        }

        $counts = $request->counts;
        $mcCount  = (int) ($counts['multiple_choice'] ?? 0);
        $tfCount  = (int) ($counts['true_false']      ?? 0);
        $maCount  = (int) ($counts['multiple_answer'] ?? 0);
        $total    = $mcCount + $tfCount + $maCount;

        if ($total === 0) {
            return response()->json(['message' => 'Jumlah soal minimal 1.'], 422);
        }

        // Cap at remaining quota if limited
        $remaining = $this->quota->getRemaining($user, 'generate_questions');
        if ($remaining !== null && $total > $remaining) {
            return response()->json([
                'message'      => "Sisa kuota kamu hanya {$remaining} soal hari ini.",
                'remaining'    => $remaining,
                'limit'        => $this->quota->getLimit($user->role, 'generate_questions'),
                'notification' => $this->quota->buildNotification($remaining, 'generate_questions'),
            ], 422);
        }

        if ($total > 30) {
            return response()->json(['message' => 'Maksimal 30 soal per generate.'], 422);
        }

        // ── Kumpulkan konteks ────────────────────────────────
        $context = '';

        // Dari topic manual
        if ($request->filled('topic')) {
            $context .= "Topik: {$request->topic}\n";
        }

        // Dari judul quiz
        if ($request->filled('quiz_id')) {
            $quiz = Quiz::find($request->quiz_id);
            if ($quiz) {
                $context .= "Nama kuis: {$quiz->title}\n";
                if ($quiz->description) {
                    $context .= "Deskripsi kuis: {$quiz->description}\n";
                }
            }
        }

        // Dari konten materi
        if ($request->filled('material_id')) {
            $material = Material::find($request->material_id);
            if ($material) {
                $context .= "Judul materi: {$material->title}\n";
                if ($material->description) {
                    $context .= "Deskripsi: {$material->description}\n";
                }
                if ($material->content) {
                    // Strip HTML dan ambil max 2000 karakter
                    $plainContent = strip_tags($material->content);
                    $plainContent = mb_substr($plainContent, 0, 2000);
                    $context .= "Isi materi:\n{$plainContent}\n";
                }
            }
        }

        if (empty(trim($context))) {
            return response()->json([
                'message' => 'Berikan minimal satu konteks: topik, quiz_id, atau material_id.'
            ], 422);
        }

        $difficulty = $request->difficulty ?? 'sedang';
        $diffLabel  = match($difficulty) {
            'mudah' => 'mudah (cocok untuk pemula)',
            'sulit' => 'sulit (membutuhkan pemahaman mendalam)',
            default => 'sedang (membutuhkan pemahaman konsep)',
        };

        // ── Build prompt ─────────────────────────────────────
        $parts = [];
        if ($mcCount > 0) $parts[] = "{$mcCount} soal pilihan ganda (multiple_choice)";
        if ($tfCount > 0) $parts[] = "{$tfCount} soal benar/salah (true_false)";
        if ($maCount > 0) $parts[] = "{$maCount} soal pilihan ganda majemuk (multiple_answer, jawaban benar lebih dari 1)";

        $soalList = implode(', ', $parts);

        $prompt = <<<PROMPT
Kamu adalah asisten pembuat soal ujian untuk perguruan tinggi. Buat soal dalam bahasa Indonesia yang baik dan benar.

KONTEKS MATERI:
{$context}

TUGAS:
Buat {$total} soal dengan tingkat kesulitan {$diffLabel}, terdiri dari:
{$soalList}

FORMAT RESPONS (JSON array, HANYA JSON, tanpa teks lain, tanpa markdown):
[
  {
    "question_type": "multiple_choice",
    "question": "Pertanyaan di sini?",
    "option_a": "Pilihan A",
    "option_b": "Pilihan B",
    "option_c": "Pilihan C",
    "option_d": "Pilihan D",
    "correct_answer": "A",
    "correct_answers": [],
    "explanation": "Penjelasan jawaban yang benar",
    "difficulty": "{$difficulty}",
    "points": 10
  },
  {
    "question_type": "true_false",
    "question": "Pernyataan yang harus dinilai benar atau salah?",
    "option_a": "Benar",
    "option_b": "Salah",
    "option_c": "",
    "option_d": "",
    "correct_answer": "A",
    "correct_answers": [],
    "explanation": "Penjelasan mengapa benar/salah",
    "difficulty": "{$difficulty}",
    "points": 10
  },
  {
    "question_type": "multiple_answer",
    "question": "Pilih semua yang benar?",
    "option_a": "Pilihan A",
    "option_b": "Pilihan B",
    "option_c": "Pilihan C",
    "option_d": "Pilihan D",
    "correct_answer": "A",
    "correct_answers": ["A", "C"],
    "explanation": "Penjelasan jawaban",
    "difficulty": "{$difficulty}",
    "points": 15
  }
]

ATURAN PENTING:
- Hanya kembalikan JSON array, TIDAK ada teks lain
- Soal harus relevan dengan konteks materi
- Pilihan jawaban harus masuk akal dan tidak terlalu mudah ditebak
- Untuk multiple_answer, pastikan ada 2-3 jawaban benar
- Untuk true_false, correct_answer adalah "A" (Benar) atau "B" (Salah)
- Explanation harus informatif dan membantu mahasiswa memahami
PROMPT;

        // ── Hit Gemini API ───────────────────────────────────
        try {
            $response = Http::timeout(30)->post(
                $this->apiUrl . '?key=' . $this->apiKey,
                [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 4096,
                    ],
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal menghubungi Gemini API: ' . $response->status(),
                ], 502);
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');

            // Strip markdown kalau ada
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```\s*/i', '', $text);
            $text = trim($text);

            $questions = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
                return response()->json([
                    'message' => 'AI mengembalikan format yang tidak valid. Coba lagi.',
                    'raw'     => mb_substr($text, 0, 500),
                ], 422);
            }

            // Sanitasi dan validasi setiap soal
            $questions = array_map(function ($q) use ($difficulty) {
                return [
                    'question_type'   => in_array($q['question_type'] ?? '', ['multiple_choice', 'true_false', 'multiple_answer'])
                        ? $q['question_type'] : 'multiple_choice',
                    'question'        => $q['question'] ?? '',
                    'option_a'        => $q['option_a'] ?? '',
                    'option_b'        => $q['option_b'] ?? '',
                    'option_c'        => $q['option_c'] ?? '',
                    'option_d'        => $q['option_d'] ?? '',
                    'correct_answer'  => strtoupper($q['correct_answer'] ?? 'A'),
                    'correct_answers' => array_map('strtoupper', $q['correct_answers'] ?? []),
                    'explanation'     => $q['explanation'] ?? '',
                    'difficulty'      => $q['difficulty'] ?? $difficulty,
                    'points'          => (int) ($q['points'] ?? 10),
                ];
            }, $questions);

            // Consume $total slots from daily quota
            $remainingAfter = null;
            for ($i = 0; $i < $total; $i++) {
                $remainingAfter = $this->quota->consume($user, 'generate_questions');
            }
            $quotaLimit   = $this->quota->getLimit($user->role, 'generate_questions');
            $notification = $this->quota->buildNotification($remainingAfter, 'generate_questions');

            return response()->json([
                'message'         => 'Soal berhasil di-generate!',
                'count'           => count($questions),
                'questions'       => $questions,
                'quota_remaining' => $remainingAfter,
                'quota_limit'     => $quotaLimit,
                'notification'    => $notification,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // POST /api/ai/generate-description
    // Generate deskripsi materi otomatis
    // Body: { title: string, content?: string }
    // ============================================================
    public function generateDescription(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $title   = $request->title;
        $content = $request->content
            ? mb_substr(strip_tags($request->content), 0, 1500)
            : '';

        $contentPart = $content
            ? "Isi materi (ringkasan):\n{$content}"
            : "Belum ada isi materi.";

        $prompt = <<<PROMPT
Kamu adalah asisten akademik platform e-learning perguruan tinggi Indonesia.
Tugas: buat satu deskripsi singkat untuk materi kuliah berikut.

Judul: {$title}
{$contentPart}

Aturan:
- 2-3 kalimat (maks 250 karakter)
- Bahasa Indonesia formal dan informatif
- Sebutkan topik utama yang dipelajari

Balas HANYA dengan teks deskripsinya saja, tanpa JSON, tanpa tanda kutip, tanpa penjelasan tambahan.
PROMPT;

        try {
            $response = Http::timeout(20)->post(
                $this->apiUrl . '?key=' . $this->apiKey,
                [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 1024,
                    ],
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal menghubungi Gemini API.',
                ], 502);
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');

            // Bersihkan markdown
            $text = preg_replace('/```[a-zA-Z]*/', '', $text);
            $text = trim($text, " \n\r\t\"'");

            if (empty($text)) {
                return response()->json(['message' => 'AI tidak menghasilkan deskripsi.'], 422);
            }

            return response()->json([
                'message'     => 'Deskripsi berhasil di-generate!',
                'description' => $text,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── GET /api/ai/generate-quota ────────────────────────
    public function generateQuota(Request $request)
    {
        return response()->json(
            $this->quota->info($request->user(), 'generate_questions'),
            200
        );
    }

    // ── POST /api/ai/fyp-advice ────────────────────────────
    // Terima data performa matkul, return saran belajar dari Gemini
    // Hasilnya disimpan di chat_history user (sama dengan chatbot)
    public function fypAdvice(Request $request)
    {
        $request->validate([
            'course_title'  => 'required|string',
            'status_label'  => 'required|string',
            'avg_score'     => 'nullable|numeric',
            'passing_score' => 'nullable|numeric',
            'done_quizzes'  => 'nullable|integer',
            'total_quizzes' => 'nullable|integer',
            'insight'       => 'nullable|string',
            'materials'     => 'nullable|array',
        ]);

        $courseTitle  = $request->course_title;
        $statusLabel  = $request->status_label;
        $avgScore     = $request->avg_score ?? '-';
        $passingScore = $request->passing_score ?? 70;
        $done         = $request->done_quizzes ?? 0;
        $total        = $request->total_quizzes ?? 0;
        $insight      = $request->insight ?? '';

        // Format daftar materi kalau ada
        $materialsText = '';
        if (!empty($request->materials)) {
            $list = collect($request->materials)
                ->map(fn($m) => '- ' . ($m['title'] ?? '') .
                    (!empty($m['description']) ? ': ' . $m['description'] : ''))
                ->join("\n");
            $materialsText = "\n\nMateri yang tersedia di matkul ini:\n{$list}";
        }

        $prompt = <<<PROMPT
Kamu adalah tutor akademik senior yang jujur, peduli, dan analitis untuk mahasiswa Indonesia di SYNAPSE.

DATA PERFORMA MAHASISWA:
- Mata kuliah: {$courseTitle}
- Status sistem: {$statusLabel}
- Rata-rata skor kuis: {$avgScore} (KKM: {$passingScore})
- Kuis yang sudah dikerjakan: {$done} dari {$total} kuis
- Analisis sistem: {$insight}{$materialsText}

TUGASMU:
Analisa data di atas secara mendalam dan beri saran belajar yang KONKRET dan PERSONAL.

ATURAN KETAT:
- DILARANG saran generik seperti "belajar lebih giat", "jangan menyerah", "tetap semangat"
- HARUS sebut angka spesifik dari data (skor, jumlah kuis, dll)
- HARUS identifikasi masalah utama berdasarkan data (bukan asumsi)
- HARUS beri langkah konkret yang bisa dilakukan SEKARANG
- Jika skor 0 atau belum mengerjakan kuis → fokus pada cara memulai dan mengatasi hambatan
- Jika skor rendah → analisa kemungkinan penyebab dan cara memperbaiki spesifik di matkul ini
- Jika skor menengah → identifikasi gap dan cara mencapai KKM dengan langkah terukur
- Gunakan bahasa Indonesia yang natural, seperti kakak kelas yang peduli — tidak kaku, tidak berlebihan

FORMAT RESPONS (ikuti persis):
**Analisis:** [1-2 kalimat diagnosis jujur berdasarkan data]

**Yang perlu kamu lakukan:**
1. [Langkah konkret pertama — spesifik, bisa dilakukan hari ini]
2. [Langkah konkret kedua — spesifik, bisa dilakukan minggu ini]
3. [Langkah konkret ketiga — spesifik, strategi jangka pendek]

**Target realistis:** [Berapa skor yang bisa dicapai dan dalam berapa waktu jika langkah di atas dijalankan]
PROMPT;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [[
                    'parts' => [['text' => $prompt]]
                ]],
                'generationConfig' => [
                    'temperature'     => 0.75,
                    'maxOutputTokens' => 1500,
                ],
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal menghubungi Gemini API: ' . $response->status(),
                ], 502);
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');
            $text = trim($text);
            // Strip markdown code block kalau ada
            $text = preg_replace('/```[a-zA-Z]*/', '', $text);
            $text = trim($text);

            if (empty($text)) {
                return response()->json(['message' => 'AI tidak menghasilkan saran.'], 422);
            }

            // Simpan ke chat_history di DB/session agar muncul di chatbot
            // Format identik dengan ChatMessage di Flutter
            $userMsg = [
                'text'      => "Berikan saran belajar untuk mata kuliah **{$courseTitle}** (skor rata-rata: {$avgScore}, status: {$statusLabel})",
                'isUser'    => true,
                'timestamp' => now()->toIso8601String(),
            ];
            $aiMsg = [
                'text'      => $text,
                'isUser'    => false,
                'timestamp' => now()->toIso8601String(),
            ];

            return response()->json([
                'message'   => 'Saran berhasil dibuat!',
                'advice'    => $text,
                'chat_messages' => [$userMsg, $aiMsg],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

    