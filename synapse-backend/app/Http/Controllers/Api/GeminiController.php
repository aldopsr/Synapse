<?php
// app/Http/Controllers/Api/GeminiController.php
// Endpoint AI untuk generate soal dan deskripsi materi menggunakan Gemini API
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Quiz;
use App\Models\Material;

class GeminiController extends Controller
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key_ai', env('GEMINI_API_KEY_AI', ''));
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

        $counts = $request->counts;
        $mcCount  = (int) ($counts['multiple_choice'] ?? 0);
        $tfCount  = (int) ($counts['true_false']      ?? 0);
        $maCount  = (int) ($counts['multiple_answer'] ?? 0);
        $total    = $mcCount + $tfCount + $maCount;

        if ($total === 0) {
            return response()->json(['message' => 'Jumlah soal minimal 1.'], 422);
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
                    'correct_answers' => $q['correct_answers'] ?? [],
                    'explanation'     => $q['explanation'] ?? '',
                    'difficulty'      => $q['difficulty'] ?? $difficulty,
                    'points'          => (int) ($q['points'] ?? 10),
                ];
            }, $questions);

            return response()->json([
                'message'   => 'Soal berhasil di-generate!',
                'count'     => count($questions),
                'questions' => $questions,
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
Kamu adalah asisten akademik untuk platform e-learning perguruan tinggi.

Buatkan deskripsi singkat untuk materi kuliah berikut dalam bahasa Indonesia yang formal, informatif, dan menarik.

Judul materi: {$title}
{$contentPart}

Buat 3 versi deskripsi dengan panjang berbeda:
1. Sangat singkat (1 kalimat, maks 100 karakter)
2. Singkat (2-3 kalimat, maks 250 karakter)
3. Menengah (4-5 kalimat, maks 500 karakter)

Format respons (JSON only, tanpa markdown):
{
  "short": "Deskripsi 1 kalimat",
  "medium": "Deskripsi 2-3 kalimat",
  "long": "Deskripsi 4-5 kalimat"
}
PROMPT;

        try {
            $response = Http::timeout(20)->post(
                $this->apiUrl . '?key=' . $this->apiKey,
                [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]],
                    'generationConfig' => [
                        'temperature'     => 0.8,
                        'maxOutputTokens' => 512,
                    ],
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal menghubungi Gemini API.',
                ], 502);
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```\s*/i', '', $text);
            $text = trim($text);

            $result = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback: return raw text sebagai short description
                return response()->json([
                    'message' => 'Berhasil',
                    'descriptions' => [
                        'short'  => mb_substr($text, 0, 100),
                        'medium' => mb_substr($text, 0, 250),
                        'long'   => mb_substr($text, 0, 500),
                    ],
                ]);
            }

            return response()->json([
                'message'      => 'Deskripsi berhasil di-generate!',
                'descriptions' => [
                    'short'  => $result['short']  ?? '',
                    'medium' => $result['medium'] ?? '',
                    'long'   => $result['long']   ?? '',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}