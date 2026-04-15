<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\AiChatController;

// =================================================================
// 1. JALUR PUBLIK (Tanpa perlu Login / Token)
// =================================================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class , 'register']);
    Route::post('/login', [AuthController::class , 'login']);
    Route::post('/verify-otp', [AuthController::class , 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class , 'resendOtp']);
});

// Baca Materi (Publik)
Route::get('/materials', function () {
    return response()->json(\App\Models\Material::all());
});
Route::get('/materials/{id}', [MaterialController::class , 'show']);


// =================================================================
// 2. JALUR UMUM (Wajib Login, Semua Role Boleh Akses)
// =================================================================
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profil
    Route::get('/auth/me', [AuthController::class , 'getUser']);
    Route::post('/auth/logout', [AuthController::class , 'logout']);

    // Papan Peringkat & Pertanyaan
    Route::get('/quizzes/{id}/leaderboard', [QuizController::class , 'leaderboard']);
    Route::get('/materials/{id}/questions', [MaterialController::class , 'getQuestions']);
});

// =================================================================
// 3. AREA KHUSUS DOSEN & SUPERADMIN
// =================================================================
Route::middleware(['auth:sanctum', 'role:dosen,superadmin'])->group(function () {
    Route::post('/admin/materials', [MaterialController::class , 'store']);
});

// =================================================================
// 4. AREA KHUSUS MAHASISWA
// =================================================================
Route::middleware(['auth:sanctum', 'role:mahasiswa'])->group(function () {
    Route::get('/quizzes', [QuizController::class , 'index']);
    Route::get('/quizzes/{id}/questions', [QuizController::class , 'getQuestions']);
    Route::post('/quizzes/{id}/submit', [QuizController::class , 'submitQuiz']);

    Route::post('/analyze-score', [AiChatController::class , 'analyzeScore']);
    Route::get('/quiz-history', [QuizController::class , 'getHistory']);
    Route::post('/explain-question', [AiChatController::class, 'explainQuestion']);
});

// =================================================================
// 5. AREA PUBLIK & MAHASISWA
// =================================================================
Route::middleware(['auth:sanctum', 'role:publik,mahasiswa'])->group(function () {
    Route::get('/mini-quizzes', function () {
            return response()->json(['message' => 'Ini daftar Mini Quiz untuk latihan.']);
        }
        );
        Route::post('/chat', [AiChatController::class , 'chat']);
    });