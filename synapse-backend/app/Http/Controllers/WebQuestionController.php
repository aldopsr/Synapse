<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\Question;

class WebQuestionController extends Controller
{
    // Buka halaman kelola soal untuk materi tertentu
    public function index($material_id)
    {
        $material = Material::findOrFail($material_id);
        $questions = Question::where('material_id', $material_id)->get();
        
        return view('questions.index', compact('material', 'questions'));
    }

    // Simpan soal baru ke database
    public function store(Request $request, $material_id)
    {
        $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d',
        ]);

        Question::create([
            'material_id' => $material_id,
            'question_text' => $request->question_text,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_answer' => $request->correct_answer,
        ]);

        return redirect()->back()->with('success', 'Soal berhasil ditambahkan!');
    }

    // Hapus soal
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return redirect()->back()->with('success', 'Soal berhasil dihapus!');
    }
}