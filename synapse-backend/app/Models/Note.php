<?php
// app/Models/Note.php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Note extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notes';

    protected $fillable = [
        'user_id',
        'content',
        'color_index',
    ];

    protected $casts = [
        'color_index' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}