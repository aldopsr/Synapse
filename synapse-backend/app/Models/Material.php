<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Tambahkan appends agar model_3d_url otomatis muncul di JSON
    protected $appends = ['model_3d_url'];

    // Accessor: Mengubah path storage menjadi URL publik yang utuh
    public function getModel3dUrlAttribute()
    {
        if (isset($this->attributes['model_3d_path']) && $this->attributes['model_3d_path']) {
            return asset('storage/' . $this->attributes['model_3d_path']);
        }
        return null;
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}