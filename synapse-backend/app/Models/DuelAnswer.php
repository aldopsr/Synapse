<?php
// app/Models/DuelAnswer.php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DuelAnswer extends Model
{
    use HasFactory;

    protected $collection = 'duel_answers';
    protected $guarded    = [];

    public function duel() { return $this->belongsTo(Duel::class, 'duel_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}