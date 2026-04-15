<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi: 1 Nilai milik 1 User dan 1 Materi
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function material()
    {
        return $this->belongsTo(Material::class , 'material_id');
    }
}