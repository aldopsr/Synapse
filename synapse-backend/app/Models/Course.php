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

    // 🌟 TAMBAHAN: Relasi agar Course tahu siapa Dosen pengampunya
    public function dosen()
    {
        // Menyambungkan 'dosen_id' di tabel courses dengan '_id' di tabel users
        return $this->belongsTo(User::class, 'dosen_id', '_id');
    }

    // (Opsional) Relasi untuk tahu siapa Admin yang membuat matkul ini
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', '_id');
    }
}