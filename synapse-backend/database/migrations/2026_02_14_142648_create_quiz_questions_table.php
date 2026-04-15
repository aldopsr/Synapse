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
    Schema::create('quiz_questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('quiz_id')->constrained()->onDelete('cascade'); // Nyambung ke tabel quizzes
        $table->text('question'); // Teks soal
        $table->string('option_a'); // Pilihan A
        $table->string('option_b'); // Pilihan B
        $table->string('option_c'); // Pilihan C
        $table->string('option_d'); // Pilihan D
        $table->enum('correct_answer', ['a', 'b', 'c', 'd']); // Kunci jawaban
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
