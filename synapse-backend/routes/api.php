<?php

// Handle CORS preflight untuk semua route
Route::options('/{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', request()->header('Origin'))
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
})->where('any', '.*');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\ArAssetController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\StudentDataController;
use Illuminate\Support\Facades\File;

// =================================================================
// 1. JALUR PUBLIK (Tanpa perlu Login / Token - Tamu Bebas Masuk)
// =================================================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class , 'register']);
    Route::post('/login', [AuthController::class , 'login']);
    Route::post('/verify-otp', [AuthController::class , 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class , 'resendOtp']);
});

Route::post('/forgot-password/send-otp', [AuthController::class, 'sendResetOtp']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword']);

// --- RUTE BACA MATERI & LATIHAN (Tamu & Mahasiswa) ---
Route::get('/materials', [MaterialController::class, 'index']);
Route::get('/materials/{id}', [MaterialController::class, 'show']);
Route::get('/materials/{id}/questions', [MaterialController::class, 'getQuestions']);

// --- RUTE DAFTAR MATKUL (Flutter - Tamu & Mahasiswa) ---
Route::get('/public/courses', [\App\Http\Controllers\Api\StudentCourseController::class, 'index']);
Route::get('/public/courses/{course_id}/materials', [\App\Http\Controllers\Api\StudentCourseController::class, 'getMaterials']);

// --- JALUR UNDUH MODEL 3D ---
Route::get('/download-model/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!File::exists($fullPath)) {
        return response()->json(['message' => 'File 3D tidak ditemukan'], 404);
    }
    return response()->file($fullPath, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*');

Route::get('/ar-gallery', [MaterialController::class, 'arGallery']);
Route::get('/ar-assets', [ArAssetController::class, 'index']); 
Route::get('/ar-assets/{id}', [ArAssetController::class, 'show']); 
Route::get('/materials/{materialId}/ar-assets', [ArAssetController::class, 'getByMaterial']); 


// =================================================================
// 2. JALUR UMUM (Wajib Login, Semua Role Boleh Akses)
// =================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class , 'getUser']);
    Route::post('/auth/logout', [AuthController::class , 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('/quizzes/{id}/leaderboard', [QuizController::class , 'leaderboard']);
    // Kuota chatbot untuk public
    Route::get('/chat/quota', [AiChatController::class, 'chatQuota']);
});


// =================================================================
// 3. AREA KHUSUS DOSEN & SUPERADMIN (Web Dashboard)
// =================================================================
Route::middleware(['auth:sanctum', 'role:dosen,admin,superadmin'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::get('/courses/{course_id}/materials', [MaterialController::class, 'getByCourse']);
    Route::post('/courses/{course_id}/materials', [MaterialController::class, 'storeByCourse']);
    Route::post('/upload-image', [MaterialController::class, 'uploadImage']);
    
    Route::get('/materials/{id}', [MaterialController::class, 'show']);
    Route::put('/materials/{id}', [MaterialController::class, 'update']);
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

    Route::get('materials/{material_id}/questions', [MaterialController::class, 'getQuestions']);
    Route::post('materials/{material_id}/questions', [MaterialController::class, 'storeQuestion']);
    Route::delete('questions/{id}', [MaterialController::class, 'destroyQuestion']);
    Route::post('materials/{material_id}/ar', [MaterialController::class, 'attachAr']);
    Route::post('/admin/materials', [MaterialController::class , 'store']);

    Route::get('/dosen', [DosenController::class, 'index']);
    Route::post('/dosen', [DosenController::class, 'store']);
    Route::delete('/dosen/{id}', [DosenController::class, 'destroy']);
    Route::put('/dosen/{id}', [DosenController::class, 'update']);

    Route::post('/materials/{materialId}/ar-assets', [ArAssetController::class, 'store']);
    Route::put('/ar-assets/{id}', [ArAssetController::class, 'update']);
    Route::post('/ar-assets/{id}', [ArAssetController::class, 'update']);
    Route::delete('/ar-assets/{id}', [ArAssetController::class, 'destroy']);

    Route::get('/admin/quizzes', [QuizController::class, 'adminIndex']);
    Route::get('/admin/quizzes/{id}', [QuizController::class, 'adminShow']);
    Route::post('/admin/quizzes', [QuizController::class, 'store']);
    Route::put('/admin/quizzes/{id}', [QuizController::class, 'update']);
    Route::delete('/admin/quizzes/{id}', [QuizController::class, 'destroy']);
    Route::post('/admin/quizzes/{id}/toggle', [QuizController::class, 'toggleActive']);
    Route::get('/admin/quizzes/{id}/questions', [QuizController::class, 'getQuizQuestions']);
    Route::post('/admin/quizzes/{id}/questions', [QuizController::class, 'storeQuizQuestion']);
    Route::delete('/admin/quiz-questions/{id}', [QuizController::class, 'destroyQuizQuestion']);

    // Data mahasiswa (baru)
    Route::get('/student-data/quiz-participants', [StudentDataController::class, 'quizParticipants']);
    Route::get('/student-data/all', [StudentDataController::class, 'allStudents']);
    Route::get('/student-data/{userId}/detail', [StudentDataController::class, 'studentDetail']);
});


// =================================================================
// 4. MAHASISWA & PUBLIC — quiz, history, chat
// =================================================================
Route::middleware(['auth:sanctum', 'role:mahasiswa,public'])->group(function () {
    Route::get('/quizzes',                  [QuizController::class, 'index']);
    Route::get('/quizzes/{id}/questions',   [QuizController::class, 'getQuestions']);
    Route::post('/quizzes/{id}/submit',     [QuizController::class, 'submitQuiz']);
    Route::get('/quiz-history',             [QuizController::class, 'getHistory']);
    Route::post('/chat',                    [AiChatController::class, 'chat']);

    // Khusus mahasiswa saja
    Route::middleware('role:mahasiswa')->group(function () {
        Route::post('/analyze-score',     [AiChatController::class, 'analyzeScore']);
        Route::post('/explain-question',  [AiChatController::class, 'explainQuestion']);
    });
});