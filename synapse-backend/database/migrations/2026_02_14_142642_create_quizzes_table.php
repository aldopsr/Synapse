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
    Schema::create('quizzes', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Judul quiz (Contoh: Kuis Subnetting)
        $table->text('description')->nullable();
        $table->integer('duration_minutes'); // Waktu pengerjaan dalam menit
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // ID Dosen pembuat
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
