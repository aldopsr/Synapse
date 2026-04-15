<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('quiz_attempts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Mahasiswa yang ngerjain
        $table->foreignId('quiz_id')->constrained()->onDelete('cascade'); // Quiz yang dikerjain
        $table->integer('score'); // Nilai akhirnya
        $table->integer('time_taken_seconds'); // Waktu pengerjaan (detik) untuk Tie-Breaker
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
