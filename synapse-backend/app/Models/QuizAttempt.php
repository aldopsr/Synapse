<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $guarded = []; // Atau isi $fillable sesuai kebutuhan Kapten

    public function quiz()
    {
        return $this->belongsTo(Quiz::class , 'quiz_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}