<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Quiz;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    private QuotaService $quota;

    public function __construct(QuotaService $quota)
    {
        $this->quota = $quota;
    }

    private function buildSynapseContext(): string
    {
        $lines = [];

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

        try {
            $materials = Material::with('questions')->get(['id', 'title', 'description', 'content', 'course_id']);
            if ($materials->isNotEmpty()) {
                $lines[] = "=== MATERI & SOAL LATIHAN ===";
                foreach ($materials as $mat) {
                    $lines[] = "📘 Materi: {$mat->title}";
                    if ($mat->description) {
                        $lines[] = "   Deskripsi: {$mat->description}";
                    }
                    if ($mat->content) {
                        $plain = strip_tags($mat->content);
                        $plain = preg_replace('/\s+/', ' ', trim($plain));
                        if (strlen($plain) > 800) {
                            $plain = mb_substr($plain, 0, 800) . '...';
                        }
                        $lines[] = "   Ringkasan isi: {$plain}";
                    }
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
            'message'        => 'required|string|max:2000',
            'history'        => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|string|in:user,model',
            'history.*.text' => 'required_with:history|string|max:2000',
        ]);

        $user = $request->user();

        // Enforce daily quota for public and mahasiswa
        if ($this->quota->getLimit($user->role, 'chat') !== null) {
            if ($this->quota->isExhausted($user, 'chat')) {
                $info = $this->quota->info($user, 'chat');
                return response()->json([
                    'message'      => 'Kuota chat harian habis. Reset esok hari pukul 00.00 WIB.',
                    'remaining'    => 0,
                    'limit'        => $info['limit'],
                    'notification' => $info['notification'],
                ], 429);
            }
        }

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
PROMPT;

        $contents = [];
        foreach ($request->input('history', []) as $turn) {
            $contents[] = [
                'role'  => $turn['role'],
                'parts' => [['text' => $turn['text']]],
            ];
        }
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $request->message]],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => $contents,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $candidates   = $responseData['candidates'] ?? [];
                $aiText = !empty($candidates)
                    ? ($candidates[0]['content']['parts'][0]['text'] ?? 'Maaf, otak AI saya sedang nge-blank. Coba tanya lagi!')
                    : 'Maaf, otak AI saya sedang nge-blank. Coba tanya lagi!';

                // Consume quota after successful response
                $remaining    = $this->quota->consume($user, 'chat');
                $limit        = $this->quota->getLimit($user->role, 'chat');
                $notification = $this->quota->buildNotification($remaining, 'chat');

                return response()->json([
                    'message'      => 'Berhasil',
                    'reply'        => $aiText,
                    'remaining'    => $remaining,
                    'limit'        => $limit,
                    'notification' => $notification,
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

    public function chatQuota(Request $request)
    {
        $user = $request->user();
        return response()->json($this->quota->info($user, 'chat'), 200);
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
            return response()->json(['message' => 'Fitur AI sedang tidak tersedia.'], 503);
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
                $candidates   = $responseData['candidates'] ?? [];
                $aiText = !empty($candidates)
                    ? ($candidates[0]['content']['parts'][0]['text'] ?? 'Analisis tidak tersedia saat ini.')
                    : 'Analisis tidak tersedia saat ini.';

                return response()->json([
                    'message'  => 'Berhasil',
                    'analysis' => $aiText
                ], 200);
            }

            Log::error('Gemini analyzeScore error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json(['message' => 'Analisis AI tidak tersedia saat ini.'], 503);

        } catch (\Exception $e) {
            Log::error('Gemini analyzeScore error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menganalisis skor.'], 500);
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
            return response()->json(['message' => 'Fitur AI sedang tidak tersedia.'], 503);
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
                $candidates   = $responseData['candidates'] ?? [];
                $aiText = !empty($candidates)
                    ? ($candidates[0]['content']['parts'][0]['text'] ?? 'Penjelasan tidak tersedia saat ini.')
                    : 'Penjelasan tidak tersedia saat ini.';

                return response()->json([
                    'message'     => 'Berhasil',
                    'explanation' => $aiText
                ], 200);
            }

            Log::error('Gemini explainQuestion error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json(['message' => 'Penjelasan AI tidak tersedia saat ini.'], 503);

        } catch (\Exception $e) {
            Log::error('Gemini explainQuestion error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghubungi AI.'], 500);
        }
    }
}
