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