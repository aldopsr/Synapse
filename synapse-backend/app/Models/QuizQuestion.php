<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * 🌟 Auto-append computed fields ke JSON output
     */
    protected $appends = ['image_url', 'difficulty_label', 'type_label'];

    /**
     * Default values untuk soal lama yang belum punya field baru
     */
    protected $attributes = [
        'question_type' => 'multiple_choice', // multiple_choice | true_false | multiple_answer
        'points' => 10,
        'difficulty' => 'sedang', // mudah | sedang | sulit
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * 🌟 Accessor: URL gambar lengkap
     */
    public function getImageUrlAttribute()
    {
        $image = $this->attributes['image'] ?? null;
        if (!$image) return null;
        if (str_starts_with($image, 'http')) return $image;
        return asset('storage/' . $image);
    }

    /**
     * 🌟 Label kesulitan dengan emoji
     */
    public function getDifficultyLabelAttribute()
    {
        $diff = $this->attributes['difficulty'] ?? 'sedang';
        return match($diff) {
            'mudah' => '🟢 Mudah',
            'sedang' => '🟡 Sedang',
            'sulit' => '🔴 Sulit',
            default => '🟡 Sedang',
        };
    }

    /**
     * 🌟 Label tipe soal
     */
    public function getTypeLabelAttribute()
    {
        $type = $this->attributes['question_type'] ?? 'multiple_choice';
        return match($type) {
            'multiple_choice' => 'Pilihan Ganda',
            'true_false' => 'True / False',
            'multiple_answer' => 'Multiple Answer',
            default => 'Pilihan Ganda',
        };
    }
}