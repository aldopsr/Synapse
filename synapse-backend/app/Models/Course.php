<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'courses';
    protected $guarded = []; // Jurus ninja agar semua kolom bisa diisi

    // Relasi ke semua dosen pengampu (many via dosen_ids array)
    public function dosens()
    {
        $ids = $this->dosen_ids ?? [];
        return User::whereIn('_id', $ids)->get();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', '_id');
    }
    
    public function materials()
    {
        return $this->hasMany(Material::class, 'course_id');
    }
}