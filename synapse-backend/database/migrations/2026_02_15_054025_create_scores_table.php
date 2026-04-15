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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            // Nilai ini milik Mahasiswa siapa?
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Nilai ini dari Kuis Materi yang mana?
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            
            // Berapa nilainya? (Misal: 80, 100)
            $table->integer('score');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
