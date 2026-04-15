<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    protected $guarded = [];

    // TAMBAHKAN 3 BARIS INI: Relasi 1 Materi punya Banyak Soal (Latihan)
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
