<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ArAsset extends Model
{
    use HasFactory;

    /**
     * Nama collection di MongoDB
     */
    protected $collection = 'ar_assets';

    /**
     * Field yang boleh diisi (semua kecuali _id)
     */
    protected $guarded = [];

    /**
     * Auto-append URL helper saat di-serialize ke JSON
     */
    protected $appends = ['model_3d_url', 'image_url'];

    /**
     * 🌟 Accessor: URL lengkap untuk file model 3D
     * Dilewatkan ke /api/download-model/ agar server mengirim header CORS yang dibutuhkan model-viewer.
     */
    public function getModel3dUrlAttribute()
    {
        if (isset($this->attributes['model_3d_path']) && $this->attributes['model_3d_path']) {
            return url('/api/download-model/' . $this->attributes['model_3d_path']);
        }
        return null;
    }

    /**
     * 🌟 Accessor: URL lengkap untuk thumbnail/image
     * Contoh hasil: http://192.168.1.12:8000/storage/ar_thumbnails/xxx.png
     */
    public function getImageUrlAttribute()
    {
        if (isset($this->attributes['image']) && $this->attributes['image']) {
            return asset('storage/' . $this->attributes['image']);
        }
        return null;
    }

    /**
     * Relasi: 1 AR Asset milik 1 Materi
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    /**
     * Relasi: 1 AR Asset milik 1 Mata Kuliah (untuk filter dosen)
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', '_id');
    }

    /**
     * Relasi: 1 AR Asset dibuat oleh 1 User (dosen)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}