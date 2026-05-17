<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * 🌟 PENTING: Untuk MongoDB, JANGAN pakai $casts untuk datetime
     * karena MongoDB simpan tanggal sebagai UTCDateTime atau Unix
     * timestamp ms. Kita handle manual via accessor.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Auto-append di JSON output
     */
    protected $appends = ['status', 'is_accessible', 'start_at_iso', 'end_at_iso'];

    // ============================================================
    // RELASI
    // ============================================================
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

    // ============================================================
    // 🌟 HELPER: Konversi nilai apa pun → Carbon
    // Handle: UTCDateTime, Carbon, DateTime, timestamp ms, timestamp s, string
    // ============================================================
    private function toCarbon($value)
    {
        if (!$value) return null;

        try {
            // MongoDB UTCDateTime
            if ($value instanceof \MongoDB\BSON\UTCDateTime) {
                return Carbon::instance($value->toDateTime());
            }
            // Carbon sudah
            if ($value instanceof Carbon) {
                return $value;
            }
            // DateTime biasa
            if ($value instanceof \DateTime) {
                return Carbon::instance($value);
            }
            // Number (timestamp dari MongoDB)
            if (is_numeric($value)) {
                $val = (int) $value;
                // Lebih dari 10 digit = milidetik, bukan detik
                if ($val > 10000000000) {
                    return Carbon::createFromTimestampMs($val);
                }
                return Carbon::createFromTimestamp($val);
            }
            // String biasa
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 🌟 Accessor: start_at otomatis jadi Carbon (atau null)
     */
    public function getStartAtAttribute($value)
    {
        return $this->toCarbon($value);
    }

    /**
     * 🌟 Accessor: end_at otomatis jadi Carbon (atau null)
     */
    public function getEndAtAttribute($value)
    {
        return $this->toCarbon($value);
    }

    /**
     * 🌟 ISO format untuk frontend (lebih konsisten daripada Carbon serialize)
     */
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

    // ============================================================
    // 🌟 ACCESSOR: Status Quiz Otomatis Terhitung
    // 'aktif' | 'nonaktif' | 'belum_mulai' | 'sudah_selesai'
    // ============================================================
    public function getStatusAttribute()
    {
        // Kalau dosen matikan manual → langsung nonaktif
        if (isset($this->attributes['is_active']) && !$this->attributes['is_active']) {
            return 'nonaktif';
        }

        $now = Carbon::now();
        $startAt = $this->start_at; // pakai accessor
        $endAt = $this->end_at;     // pakai accessor

        if ($startAt && $now->lt($startAt)) {
            return 'belum_mulai';
        }

        if ($endAt && $now->gt($endAt)) {
            return 'sudah_selesai';
        }

        return 'aktif';
    }

    /**
     * Apakah mahasiswa boleh akses quiz sekarang?
     */
    public function getIsAccessibleAttribute()
    {
        return $this->status === 'aktif';
    }
}