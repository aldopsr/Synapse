<?php

namespace App\Models;

// 1. Kita warisi DNA asli dari Sanctum agar tidak kena TypeError
use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

// 2. Kita panggil sihir dari MongoDB
use MongoDB\Laravel\Eloquent\DocumentModel;

class PersonalAccessToken extends SanctumToken
{
    // 3. Masukkan sihirnya ke dalam class ini!
    use DocumentModel;

    // 4. Beritahu tempat penyimpanannya di MongoDB
    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';
    protected $primaryKey = '_id'; // Kunci utama di MongoDB selalu _id
    protected $keyType = 'string';
}