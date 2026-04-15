<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Menyembunyikan kunci jawaban saat data dikirim ke Flutter!
    protected $hidden = ['correct_answer'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}