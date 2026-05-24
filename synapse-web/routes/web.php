<?php

use Illuminate\Support\Facades\Route;



// ============================================================
// HALAMAN PUBLIK (Tanpa Login)
// ============================================================

Route::get('/', function () {
    return view('login');
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

// ============================================================
// HALAMAN PROTECTED (Cek token dilakukan di sisi JS via app.blade.php)
// ============================================================

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/mata-kuliah', function () {
    return view('courses');
});

// DIPERBAIKI: Hanya 1 route untuk halaman materi per matkul
// (Dulu ada 2 route konflik: closure vs MateriController)
Route::get('/mata-kuliah/{course_id}/materi', function ($course_id) {
    return view('materi_matkul', ['course_id' => $course_id]);
})->name('materials.index');

Route::get('/mata-kuliah/{course_id}/tambah-materi', function ($course_id) {
    return view('tambah_materi', ['course_id' => $course_id]);
})->name('materials.create');

Route::get('/mata-kuliah/{course_id}/edit-materi/{materi_id}', function ($course_id, $materi_id) {
    return view('edit-materi', [
        'course_id'  => $course_id,
        'materi_id'  => $materi_id,
    ]);
});

Route::get('/mata-kuliah/{course_id}/materi/{materi_id}/practice', function ($course_id, $materi_id) {
    return view('practice', [
        'course_id'  => $course_id,
        'materi_id'  => $materi_id,
    ]);
});

Route::get('/kelola-ar', function () {
    return view('kelola-ar');
});

Route::get('/kelolaAkunDosen', function () {
    return view('kelolaAkunDosen');
});

Route::get('/buatAkunDosen', function () {
    return view('buatAkunDosen');
});

Route::get('/data-mahasiswa', function () {
    return view('data-mahasiswa');
});

// ============================================================
// HALAMAN KELOLA KUIS
// ============================================================

Route::get('/kuis', function () {
    return view('kuis.index');
});

Route::get('/kuis/buat', function () {
    return view('kuis.form', ['mode' => 'create', 'quiz_id' => null]);
});

Route::get('/kuis/{quiz_id}/edit', function ($quiz_id) {
    return view('kuis.form', ['mode' => 'edit', 'quiz_id' => $quiz_id]);
});

Route::get('/kuis/{quiz_id}/soal', function ($quiz_id) {
    return view('kuis.kelola-soal', ['quiz_id' => $quiz_id]);
});

Route::get('/kuis/{id}/leaderboard', function ($id) {
    return view('kuis.leaderboard', ['quiz_id' => $id]);
});

// ============================================================
// PENGATURAN AKUN
// ============================================================

Route::get('/pengaturan', function () {
    return view('pengaturan');
});

Route::get('/ubahIdentitas', function () {
    return view('ubahIdentitas');
});

Route::get('/ubahSandi', function () {
    return view('ubahSandi');
});