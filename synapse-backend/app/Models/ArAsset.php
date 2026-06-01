<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Models\ArAsset;

class ArAsset extends Model
{
    use HasFactory;

    protected $collection = 'ar_assets';
    protected $guarded = [];
    protected $appends = ['model_3d_url', 'image_url'];

    public function getModel3dUrlAttribute()
    {
        if (!isset($this->attributes['model_3d_path']) || !$this->attributes['model_3d_path']) {
            return null;
        }
        $path = $this->attributes['model_3d_path'];
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        return url('/api/download-model/' . $path);
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

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', '_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}