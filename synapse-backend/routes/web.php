<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebMaterialController;
use App\Http\Controllers\WebUserController;
use App\Http\Controllers\WebQuestionController;
use App\Http\Controllers\WebQuizController;
use App\Http\Controllers\ReportController;
use App\Models\Material; 
use App\Models\Score;
use App\Models\QuizAttempt;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $totalMaterials = Material::where('user_id', auth()->id())->count();
    
    $totalScores = QuizAttempt::whereHas('quiz', function($q) {
        $q->where('user_id', auth()->id()); 
    })->count();
    
    $avgScore = QuizAttempt::whereHas('quiz', function($q) {
        $q->where('user_id', auth()->id());
    })->avg('score') ?? 0;

    return view('dashboard', compact('totalMaterials', 'totalScores', 'avgScore'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profil bisa diakses semua user yang login
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 👇 ZONA EKSKLUSIF DOSEN & ADMIN (Mahasiswa tidak bisa masuk) 👇
Route::middleware(['auth', 'role:admin,dosen'])->group(function () {
    
    // --- MANAJEMEN MATERI ---
    Route::get('/materials', [WebMaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/create', [WebMaterialController::class, 'create'])->name('materials.create'); 
    Route::post('/materials', [WebMaterialController::class, 'store'])->name('materials.store'); 
    Route::get('/materials/{id}/edit', [WebMaterialController::class, 'edit'])->name('materials.edit'); 
    Route::put('/materials/{id}', [WebMaterialController::class, 'update'])->name('materials.update'); 
    Route::delete('/materials/{id}', [WebMaterialController::class, 'destroy'])->name('materials.destroy'); 
    Route::post('/upload-image', [WebMaterialController::class, 'uploadImage'])->name('ckeditor.upload');

    // --- MANAJEMEN SOAL KUIS (MENEMPEL DI MATERI) ---
    Route::get('/materials/{material_id}/questions', [WebQuestionController::class, 'index'])->name('questions.index');
    Route::post('/materials/{material_id}/questions', [WebQuestionController::class, 'store'])->name('questions.store');
    Route::delete('/questions/{id}', [WebQuestionController::class, 'destroy'])->name('questions.destroy');

    // --- MANAJEMEN KUIS UTAMA ---
    Route::get('/quizzes', [WebQuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [WebQuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [WebQuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{id}/questions', [WebQuizController::class, 'showQuestions'])->name('quizzes.questions');
    Route::post('/quizzes/{id}/questions', [WebQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');

    // Rute Edit & Hapus Kuis Utama
    Route::get('/quizzes/{id}/edit', [WebQuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{id}', [WebQuizController::class, 'update'])->name('quizzes.update');
    Route::delete('/quizzes/{id}', [WebQuizController::class, 'destroy'])->name('quizzes.destroy');

    // Rute Hapus Soal
    Route::delete('/quizzes/{quiz_id}/questions/{question_id}', [WebQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// 👇 ZONA SANGAT RAHASIA: HANYA SUPER ADMIN 👇
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [WebUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [WebUserController::class, 'create'])->name('users.create');
    Route::post('/users', [WebUserController::class, 'store'])->name('users.store');
    Route::delete('/users/{id}', [WebUserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';