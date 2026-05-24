<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nim',
        'kelas',
        'course_id',
        'otp',
        'otp_expires_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relasi ──────────────────────────────────────────────────
    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    // ── NIM Parser ──────────────────────────────────────────────
    /**
     * Struktur NIM IPB:
     * Pos 0     = kode sekolah/fakultas (J = Vokasi, A/B/C/... = Fakultas)
     * Pos 1     = kode kampus (0 = Bogor)
     * Pos 2     = jenjang (4 = D4/Sarjana Terapan, 1 = S1)
     * Pos 3-4   = kode program studi
     * Pos 5-6   = tahun masuk (24 = 2024)
     * Pos 7     = semester masuk (1 = Gasal, 2 = Genap)
     * Pos 8-10  = nomor urut
     *
     * Contoh: J0409241025
     */

    public function getNimInfoAttribute(): array
    {
        $nim = $this->nim ?? '';
        if (strlen($nim) < 9) {
            return [
                'raw'       => $nim,
                'sekolah'   => 'Tidak diketahui',
                'jenjang'   => 'Tidak diketahui',
                'prodi_kode'=> '-',
                'angkatan'  => '-',
                'semester'  => '-',
            ];
        }

        $kodeSekolah = strtoupper($nim[0]);
        $jenjangKode = $nim[2] ?? '';
        $prodiKode   = substr($nim, 3, 2);
        $angkatan    = '20' . substr($nim, 5, 2);
        $semesterMasuk = ($nim[7] ?? '1') === '1' ? 'Gasal' : 'Genap';

        $sekolahMap = [
            'J' => 'Sekolah Vokasi',
            'A' => 'Fak. Pertanian',
            'B' => 'Fak. Kedokteran Hewan',
            'C' => 'Fak. Perikanan & Ilmu Kelautan',
            'D' => 'Fak. Peternakan',
            'E' => 'Fak. Kehutanan & Lingkungan',
            'F' => 'Fak. Teknologi Pertanian',
            'G' => 'Fak. MIPA',
            'H' => 'Fak. Ekonomi & Manajemen',
            'I' => 'Fak. Ekologi Manusia',
            'K' => 'Fak. Kedokteran',
        ];

        $jenjangMap = [
            '1' => 'S1 Reguler',
            '2' => 'S1 Alih Jenis',
            '3' => 'D3 Vokasi',
            '4' => 'D4 / Sarjana Terapan',
            '5' => 'S2',
            '6' => 'S3',
        ];

        return [
            'raw'        => $nim,
            'sekolah'    => $sekolahMap[$kodeSekolah] ?? "Kode '$kodeSekolah'",
            'jenjang'    => $jenjangMap[$jenjangKode] ?? "Kode '$jenjangKode'",
            'prodi_kode' => $prodiKode,
            'angkatan'   => $angkatan,
            'semester'   => $semesterMasuk,
        ];
    }

    /**
     * Angkatan dari NIM, misal "2024"
     */
    public function getAngkatanAttribute(): ?string
    {
        $nim = $this->nim ?? '';
        if (strlen($nim) < 8) return null;
        return '20' . substr($nim, 5, 2);
    }

    /**
     * Kode sekolah/fakultas dari NIM, misal "J"
     */
    public function getFakultasKodeAttribute(): ?string
    {
        $nim = $this->nim ?? '';
        return strlen($nim) > 0 ? strtoupper($nim[0]) : null;
    }
}