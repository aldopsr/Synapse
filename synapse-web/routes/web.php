<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MateriController;

Route::get('/', function () {
    return view('login');
});

Route::get('/buatAkunDosen', function () {
    return view('buatAkunDosen'); 
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/mata-kuliah', function () {
    return view('courses'); 
});

Route::get('/mata-kuliah', function () {
    return view('courses'); 
});

Route::get('/mata-kuliah/{id}/materi', function ($id) {
    return view('materi_matkul', ['course_id' => $id]); 
});

Route::get('/mata-kuliah/{course_id}/materi', [MateriController::class, 'index'])->name('materials.index');
Route::get('/mata-kuliah/{course_id}/tambah-materi', [MateriController::class, 'create'])->name('materials.create');

// Tambahkan di dalam routes/web.php (Frontend)
Route::get('/mata-kuliah/{course_id}/edit-materi/{materi_id}', function ($course_id, $materi_id) {
    // Pastikan nama view ('edit-materi') sesuai dengan nama file .blade.php yang Kapten buat
    return view('edit-materi', [
        'course_id' => $course_id, 
        'materi_id' => $materi_id
    ]);
});

// Tambahkan di Frontend routes/web.php
Route::get('/mata-kuliah/{course_id}/materi/{materi_id}/practice', function ($course_id, $materi_id) {
    return view('practice', [ // Ganti 'practice' sesuai dengan nama file blade yang Kapten buat sebelumnya
        'course_id' => $course_id,
        'materi_id' => $materi_id
    ]);
});

Route::get('/kelola-ar', function () {
    return view('kelola-ar'); // Sesuaikan nama filenya
});

Route::get('/kelolaAkunDosen', function () {
    return view('kelolaAkunDosen');
});

// ============================================
// 🌟 BARU: HALAMAN KELOLA KUIS
// ============================================
 
// 1. Halaman list semua quiz
Route::get('/kuis', function () {
    return view('kuis.index');
});
 
// 2. Halaman buat quiz baru
Route::get('/kuis/buat', function () {
    return view('kuis.form', ['mode' => 'create', 'quiz_id' => null]);
});
 
// 3. Halaman edit quiz
Route::get('/kuis/{quiz_id}/edit', function ($quiz_id) {
    return view('kuis.form', ['mode' => 'edit', 'quiz_id' => $quiz_id]);
});
 
// 4. Halaman kelola soal quiz
Route::get('/kuis/{quiz_id}/soal', function ($quiz_id) {
    return view('kuis.kelola-soal', ['quiz_id' => $quiz_id]);
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/lupaSandi', function () {
    return view('lupaSandi');
});

Route::get('/lupaSandiOTP', function () {
    return view('lupaSandiOTP');
});

Route::get('/pengaturan', function () {
    return view('pengaturan');
});

Route::get('/ubahIdentitas', function () {
    return view('ubahIdentitas');
});

Route::get('/ubahSandi', function () {
    return view('ubahSandi');
});