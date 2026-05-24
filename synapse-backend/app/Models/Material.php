<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Models\ArAsset;

class Material extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['model_3d_url'];

    /**
     * Default values — visibility default 'mahasiswa'
     * Nilai: 'mahasiswa' | 'umum'
     */
    protected $attributes = [
        'visibility' => 'mahasiswa',
    ];

    public function getModel3dUrlAttribute()
    {
        if (isset($this->attributes['model_3d_path']) && $this->attributes['model_3d_path']) {
            return asset('storage/' . $this->attributes['model_3d_path']);
        }
        return null;
    }

    // ── Helper: apakah materi ini bisa diakses role tertentu? ──
    public function isAccessibleBy(string $role): bool
    {
        $visibility = $this->attributes['visibility'] ?? 'mahasiswa';

        return match ($role) {
            'mahasiswa'  => true,                          // mahasiswa akses semua
            'public'     => $visibility === 'umum',        // public hanya yang umum
            'guest'      => $visibility === 'umum',        // tamu hanya yang umum
            default      => true,                          // admin/dosen akses semua
        };
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class, 'material_id', '_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', '_id');
    }

    public function ar_assets()
    {
        return $this->hasMany(\App\Models\ArAsset::class, 'material_id', '_id');
    }
}