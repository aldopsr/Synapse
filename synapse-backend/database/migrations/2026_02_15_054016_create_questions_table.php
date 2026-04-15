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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // Soal ini milik materi yang mana?
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            
            // Isi pertanyaan dan pilihan ganda
            $table->text('question_text');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            
            // Kunci jawaban (isinya cuma 'a', 'b', 'c', atau 'd')
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
