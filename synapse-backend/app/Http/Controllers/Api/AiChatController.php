<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Fitur bawaan Laravel untuk nembak API luar

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        // Pastikan Flutter mengirim pesan teks
        $request->validate([
            'message' => 'required|string'
        ]);

        // Ambil kunci rahasia dari file .env
        $apiKey = env('GEMINI_API_KEY');
        
        // Alamat server Google Gemini (Kita pakai model gemini-1.5-flash yang super cepat & gratis)
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        // Kita beri AI "Karakter" agar dia tahu tugasnya di aplikasi SYNAPSE
        $systemPrompt = "Kamu adalah asisten AI pintar dan ramah bernama SYNAPSE. Tugasmu membantu mahasiswa IPB belajar. Jawablah dengan bahasa Indonesia yang santai, mudah dimengerti, dan langsung ke intinya. Pertanyaan user: " . $request->message;

        // Tembak server Google
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ]
            ]
        ]);

        // Jika berhasil dibalas Google
        if ($response->successful()) {
            $responseData = $response->json();
            
            // Ekstrak teks balasan dari tumpukan data JSON Google
            $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, otak AI saya sedang nge-blank.';
            
            return response()->json([
                'message' => 'Berhasil mendapat balasan AI',
                'reply' => $aiText
            ], 200);
        }

        // Jika error (misal kuota habis atau internet putus)
        return response()->json([
            'message' => 'Gagal terhubung ke AI server',
            'google_error' => $response->json(), // Ini akan membocorkan alasan asli Google menolak
            'status_code' => $response->status()
        ], 500);
    }

    public function analyzeScore(Request $request)
    {
        // 1. Validasi sekarang meminta quiz_id
        $request->validate([
            'quiz_id' => 'required|string', // Pastikan Flutter mengirim ini!
            'quiz_title' => 'required|string',
            'score' => 'required|numeric'
        ]);

        $apiKey = env('GEMINI_API_KEY');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $title = $request->quiz_title;
        $score = $request->score;
        $quizId = $request->quiz_id;

        // 2. AMBIL ISI SOAL DARI DATABASE
        // Pastikan model QuizQuestion sudah di-import di atas: use App\Models\QuizQuestion;
        $questions = \App\Models\QuizQuestion::where('quiz_id', $quizId)->get();

        // 3. RANGKUM SOAL UNTUK AI
        $soalTeks = "";
        foreach ($questions as $index => $q) {
            $nomor = $index + 1;
            $soalTeks .= "Soal $nomor: {$q->question}\n";
        }

        // Jika soal kosong (misal kuis belum ada soalnya)
        if (empty($soalTeks)) {
            $soalTeks = "Data soal tidak tersedia di database.";
        }

        // 4. PROMPT SUPER UNTUK AI
        $systemPrompt = "Kamu adalah SYNAPSE, asisten pintar mahasiswa. Seorang mahasiswa baru saja mengerjakan kuis '$title' dan mendapat nilai $score dari 100.\n\n"
                      . "Berikut adalah isi soal-soal dari kuis tersebut:\n"
                      . "```\n" . $soalTeks . "\n```\n\n"
                      . "Tugasmu:\n"
                      . "1. Berikan evaluasi singkat (1-2 kalimat) menyemangati berdasarkan nilai $score-nya.\n"
                      . "2. Baca isi soal-soal di atas. Berdasarkan topik dan konsep dari soal-soal tersebut, sarankan 3 sub-materi spesifik yang harus dia pelajari agar nilainya membaik. Jangan beri saran general, tapi kaitkan langsung dengan isi soalnya!\n"
                      . "3. Gunakan bahasa yang santai, menyemangati, dan gunakan format Markdown (bullet points).";

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                ['parts' => [['text' => $systemPrompt]]]
            ]
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, gagal menganalisa.';
            
            return response()->json([
                'message' => 'Berhasil dianalisa',
                'advice' => $aiText
            ], 200);
        }

        return response()->json(['message' => 'Gagal terhubung ke AI server'], 500);
    }

    // FUNGSI BARU: Untuk menjelaskan satu soal spesifik
    public function explainQuestion(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'correct_answer' => 'required|string', // Kunci jawaban benarnya
        ]);

        $apiKey = env('GEMINI_API_KEY');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $question = $request->question_text;
        $answer = $request->correct_answer;

        // Prompt khusus agar AI menjadi guru les
        $systemPrompt = "Kamu adalah SYNAPSE, asisten pintar mahasiswa. Ada soal pilihan ganda seperti ini:\n"
                      . "\"$question\"\n\n"
                      . "Kunci jawaban yang benar adalah: \"$answer\".\n\n"
                      . "Tugasmu: Tolong jelaskan secara logis, singkat, dan mudah dipahami MENGAPA jawaban tersebut benar. Gunakan bahasa Indonesia yang santai tapi edukatif.";

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                ['parts' => [['text' => $systemPrompt]]]
            ]
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya bingung menjelaskannya.';
            
            return response()->json([
                'message' => 'Penjelasan berhasil dibuat',
                'explanation' => $aiText
            ], 200);
        }

        return response()->json(['message' => 'Gagal terhubung ke AI server'], 500);
    }
}