<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $guarded = []; // Izinkan semua kolom diisi

    // Relasi: 1 Pertanyaan milik 1 Materi
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}