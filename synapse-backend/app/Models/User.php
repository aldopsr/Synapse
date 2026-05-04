<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
# use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nim',
        'kelas',
        'course_id', // TAMBAHAN BARU: Wajib agar data matkul tidak ditolak Laravel
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELASI YANG SUDAH ADA
    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    // TAMBAHAN BARU: Relasi ke tabel Courses (Mata Kuliah)
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // TAMBAHAN BARU: Relasi ke tabel Quizzes (Agar statistik Quiz di Frontend jalan)
    public function quizzes()
    {
        return $this->hasMany(Quiz::class); // Sesuaikan 'Quiz::class' dengan nama Model Quiz Kapten
    }
}