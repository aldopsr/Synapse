<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Models\ArAsset;

class Material extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['model_3d_url', 'image_url'];

    protected $attributes = [
        'visibility' => 'mahasiswa',
    ];

    public function getModel3dUrlAttribute()
    {
        if (!isset($this->attributes['model_3d_path']) || !$this->attributes['model_3d_path']) {
            return null;
        }
        $path = $this->attributes['model_3d_path'];
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        return asset('storage/' . $path);
    }

    public function getImageUrlAttribute()
    {
        if (!isset($this->attributes['image']) || !$this->attributes['image']) {
            return null;
        }
        $image = $this->attributes['image'];
        if (str_starts_with($image, 'http')) {
            return $image;
        }
        return asset('storage/' . $image);
    }

    public function isAccessibleBy(string $role): bool
    {
        $visibility = $this->attributes['visibility'] ?? 'mahasiswa';
        return match ($role) {
            'mahasiswa' => true,
            'public'    => $visibility === 'umum',
            'guest'     => $visibility === 'umum',
            default     => true,
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