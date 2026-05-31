<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    /**
     * Build context string from all Synapse content (courses, materials, quizzes).
     * Queried fresh on every chat request so new content is always included.
     */
    private function buildSynapseContext(): string
    {
        $lines = [];

        // === MATA KULIAH ===
        try {
            $courses = Course::all(['title', 'description']);
            if ($courses->isNotEmpty()) {
                $lines[] = "=== MATA KULIAH YANG TERSEDIA ===";
                foreach ($courses as $course) {
                    $title = $course->title ?? '-';
                    $desc  = $course->description ? ' — ' . $course->description : '';
                    $lines[] = "• {$title}{$desc}";
                }
                $lines[] = '';
            }
        } catch (\Exception $e) {
            Log::warning('Context: gagal load courses — ' . $e->getMessage());
        }

        // === MATERI & SOAL LATIHAN ===
        try {
            $materials = Material::with('questions')->get(['id', 'title', 'description', 'content', 'course_id']);
            if ($materials->isNotEmpty()) {
                $lines[] = "=== MATERI & SOAL LATIHAN ===";
                foreach ($materials as $mat) {
                    $lines[] = "📘 Materi: {$mat->title}";
                    if ($mat->description) {
                        $lines[] = "   Deskripsi: {$mat->description}";
                    }
                    // Strip HTML and truncate content to avoid huge prompts
                    if ($mat->content) {
                        $plain = strip_tags($mat->content);
                        $plain = preg_replace('/\s+/', ' ', trim($plain));
                        if (strlen($plain) > 800) {
                            $plain = mb_substr($plain, 0, 800) . '...';
                        }
                        $lines[] = "   Ringkasan isi: {$plain}";
                    }
                    // Practice questions
                    if ($mat->questions && $mat->questions->isNotEmpty()) {
                        $lines[] = "   Soal latihan:";
                        foreach ($mat->questions as $q) {
                            $lines[] = "   - {$q->question_text}";
                        }
                    }
                    $lines[] = '';
                }
            }
        } catch (\Exception $e) {
            Log::warning('Context: gagal load materials — ' . $e->getMessage());
        }

        // === KUIS ===
        try {
            $quizzes = Quiz::with('questions')->get();
            if ($quizzes->isNotEmpty()) {
                $lines[] = "=== KUIS ===";
                foreach ($quizzes as $quiz) {
                    $lines[] = "📝 Kuis: {$quiz->title}";
                    if (!empty($quiz->description)) {
                        $lines[] = "   Deskripsi: {$quiz->description}";
                    }
                    if ($quiz->questions && $quiz->questions->isNotEmpty()) {
                        $lines[] = "   Soal kuis:";
                        foreach ($quiz->questions as $q) {
                            $lines[] = "   - {$q->question}";
                        }
                    }
                    $lines[] = '';
                }
            }
        } catch (\Exception $e) {
            Log::warning('Context: gagal load quizzes — ' . $e->getMessage());
        }

        return implode("\n", $lines);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            Log::error('GEMINI_API_KEY tidak ditemukan di .env');
            return response()->json([
                'message' => 'Fitur AI sedang tidak tersedia. Hubungi administrator.'
            ], 503);
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $synapseContext = $this->buildSynapseContext();

        $systemPrompt = <<<PROMPT
Kamu adalah asisten AI bernama SYNAPSE, asisten belajar resmi untuk platform Synapse IPB.

ATURAN WAJIB:
1. Kamu HANYA boleh menjawab pertanyaan yang berkaitan dengan mata kuliah, materi, soal latihan, dan kuis yang ada di platform Synapse di bawah ini.
2. Jika pertanyaan di luar konteks Synapse (misalnya olahraga, gosip, politik, coding umum yang tidak ada di materi, dll), tolak dengan sopan dan arahkan user untuk bertanya seputar konten Synapse yang tersedia.
3. Gunakan bahasa Indonesia yang santai, ramah, dan mudah dipahami mahasiswa.
4. Jawab langsung ke intinya, tidak perlu panjang lebar kecuali memang dibutuhkan.
5. Jika ditanya soal latihan atau kuis yang ada di daftar, bantu jelaskan konsepnya — jangan langsung kasih jawabannya saja.

KONTEN SYNAPSE YANG TERSEDIA:
{$synapseContext}

Pertanyaan mahasiswa: {$request->message}
PROMPT;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $aiText = $responseData['candidates'][0]['content']['parts'][0]['text']
                    ?? 'Maaf, otak AI saya sedang nge-blank. Coba tanya lagi!';

                return response()->json([
                    'message'   => 'Berhasil',
                    'reply'     => $aiText,
                    'remaining' => null,
                ], 200);
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'message' => 'Fitur AI sedang mengalami gangguan. Coba lagi dalam beberapa saat.'
            ], 503);

        } catch (\Exception $e) {
            Log::error('Gemini error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Koneksi ke AI server bermasalah. Coba lagi.'
            ], 500);
        }
    }

    // TAMBAHAN BARU: endpoint kuota untuk Flutter
    // Gemini gratis — tidak ada limit, selalu return unlimited
    public function chatQuota(Request $request)
    {
        return response()->json([
            'limited'   => false,
            'remaining' => null,
            'limit'     => null,
        ], 200);
    }

    public function analyzeScore(Request $request)
    {
        $request->validate([
            'quiz_id'    => 'required|string',
            'quiz_title' => 'required|string',
            'score'      => 'required|numeric|min:0|max:100'
        ]);

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'message' => 'Fitur AI sedang tidak tersedia.'
            ], 503);
        }

        $url   = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        $title = $request->quiz_title;
        $score = $request->score;

        $prompt = "Seorang mahasiswa baru menyelesaikan kuis berjudul '{$title}' dengan skor {$score}/100. "
                . "Berikan analisis singkat (3-4 kalimat) tentang performanya dan saran belajar yang memotivasi. "
                . "Gunakan bahasa Indonesia yang ramah dan semangat.";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $aiText = $responseData['candidates'][0]['content']['parts'][0]['text']
                    ?? 'Analisis tidak tersedia saat ini.';

                return response()->json([
                    'message'  => 'Berhasil',
                    'analysis' => $aiText
                ], 200);
            }

            Log::error('Gemini analyzeScore error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'message' => 'Analisis AI tidak tersedia saat ini.'
            ], 503);

        } catch (\Exception $e) {
            Log::error('Gemini analyzeScore error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menganalisis skor.'
            ], 500);
        }
    }
    public function explainQuestion(Request $request)
    {
        $request->validate([
            'question'       => 'required|string',
            'correct_answer' => 'nullable|string',
        ]);

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'message' => 'Fitur AI sedang tidak tersedia.'
            ], 503);
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $prompt = "Jelaskan konsep dari soal berikut dengan bahasa Indonesia yang mudah dipahami mahasiswa:\n\n"
                . "Soal: {$request->question}\n"
                . ($request->correct_answer ? "Jawaban benar: {$request->correct_answer}\n\n" : "\n")
                . "Berikan penjelasan singkat (3-5 kalimat) mengapa jawaban tersebut benar dan konsep apa yang perlu dipahami.";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $aiText = $responseData['candidates'][0]['content']['parts'][0]['text']
                    ?? 'Penjelasan tidak tersedia saat ini.';

                return response()->json([
                    'message'     => 'Berhasil',
                    'explanation' => $aiText
                ], 200);
            }

            \Log::error('Gemini explainQuestion error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'message' => 'Penjelasan AI tidak tersedia saat ini.'
            ], 503);

        } catch (\Exception $e) {
            \Log::error('Gemini explainQuestion error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menghubungi AI.'
            ], 500);
        }
    }
}