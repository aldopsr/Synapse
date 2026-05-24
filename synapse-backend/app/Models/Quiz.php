<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Default values
     * visibility: 'mahasiswa' | 'umum'
     */
    protected $attributes = [
        'visibility' => 'mahasiswa',
    ];

    protected $appends = ['status', 'is_accessible', 'start_at_iso', 'end_at_iso'];

    // ── Relasi ──────────────────────────────────────────────────
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', '_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', '_id');
    }

    // ── Helper: apakah quiz bisa diakses role tertentu? ─────────
    public function isAccessibleBy(string $role): bool
    {
        $visibility = $this->attributes['visibility'] ?? 'mahasiswa';

        return match ($role) {
            'mahasiswa'  => true,
            'public'     => $visibility === 'umum',
            default      => true, // admin/dosen
        };
    }

    // ── Carbon helper ───────────────────────────────────────────
    private function toCarbon($value)
    {
        if (!$value) return null;
        try {
            if ($value instanceof \MongoDB\BSON\UTCDateTime) {
                return Carbon::instance($value->toDateTime());
            }
            if ($value instanceof Carbon) return $value;
            if ($value instanceof \DateTime) return Carbon::instance($value);
            if (is_numeric($value)) {
                $val = (int) $value;
                return $val > 10000000000
                    ? Carbon::createFromTimestampMs($val)
                    : Carbon::createFromTimestamp($val);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getStartAtAttribute($value) { return $this->toCarbon($value); }
    public function getEndAtAttribute($value)   { return $this->toCarbon($value); }

    public function getStartAtIsoAttribute()
    {
        $val = $this->start_at;
        return $val ? $val->toIso8601String() : null;
    }

    public function getEndAtIsoAttribute()
    {
        $val = $this->end_at;
        return $val ? $val->toIso8601String() : null;
    }

    public function getStatusAttribute()
    {
        if (isset($this->attributes['is_active']) && !$this->attributes['is_active']) {
            return 'nonaktif';
        }
        $now     = Carbon::now();
        $startAt = $this->start_at;
        $endAt   = $this->end_at;

        if ($startAt && $now->lt($startAt)) return 'belum_mulai';
        if ($endAt   && $now->gt($endAt))   return 'sudah_selesai';
        return 'aktif';
    }

    public function getIsAccessibleAttribute()
    {
        return $this->status === 'aktif';
    }
}