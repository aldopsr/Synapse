<?php
// app/Models/Duel.php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Duel extends Model
{
    use HasFactory;

    protected $collection = 'duels';
    protected $guarded    = [];

    protected $casts = [
        'challenger_score' => 'float',
        'opponent_score'   => 'float',
        'challenger_time'  => 'integer',
        'opponent_time'    => 'integer',
        'challenger_ready' => 'boolean',
        'opponent_ready'   => 'boolean',
        'battle_starts_at' => 'datetime',
    ];

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isActive(): bool    { return $this->status === 'active'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function quiz()       { return $this->belongsTo(Quiz::class, 'quiz_id'); }
    public function challenger() { return $this->belongsTo(User::class, 'challenger_id'); }
    public function opponent()   { return $this->belongsTo(User::class, 'opponent_id'); }
    public function winner()     { return $this->belongsTo(User::class, 'winner_id'); }
}