<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
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

        $systemPrompt = "Kamu adalah asisten AI pintar dan ramah bernama SYNAPSE. Tugasmu membantu mahasiswa IPB belajar. Jawablah dengan bahasa Indonesia yang santai, mudah dimengerti, dan langsung ke intinya. Pertanyaan user: " . $request->message;

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
                    'message' => 'Berhasil',
                    'reply'   => $aiText
                ], 200);
            }

            // Error dicatat di server, TIDAK dikirim ke user
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
}