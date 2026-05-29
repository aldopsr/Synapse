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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}