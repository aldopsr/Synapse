<?php
// ============================================================
// STEP 1A: Model FcmToken
// Taruh di: synapse-backend/app/Models/FcmToken.php
// ============================================================
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FcmToken extends Model
{
    use HasFactory;

    protected $collection = 'fcm_tokens';
    protected $guarded    = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}