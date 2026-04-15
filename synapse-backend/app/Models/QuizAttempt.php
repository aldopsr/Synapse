<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $guarded = []; // Atau isi $fillable sesuai kebutuhan Kapten

    // 👇 TAMBAHKAN RELASI KE KUIS DI SINI 👇
    public function quiz()
    {
        // Asumsi: model untuk tabel quizzes bernama 'Quiz'
        // Jika modelnya 'Quizzes', ganti jadi Quizzes::class
        return $this->belongsTo(Quiz::class , 'quiz_id');
    }

    // 👇 TAMBAHKAN RELASI KE USER DI SINI JUGA 👇
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}