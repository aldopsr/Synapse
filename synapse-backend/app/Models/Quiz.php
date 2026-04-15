<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = []; // Mengizinkan semua kolom diisi

    // 1 Quiz punya Banyak Soal
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    // 1 Quiz punya Banyak Riwayat Pengerjaan (Attempts)
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class , 'quiz_id');
    }
}