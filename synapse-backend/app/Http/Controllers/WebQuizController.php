<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion; // Tambahkan ini agar lebih rapi
use Illuminate\Http\Request;

class WebQuizController extends Controller
{
    // 1. Menampilkan Daftar Kuis Utama
    public function index()
    {
        $quizzes = Quiz::orderBy('created_at', 'desc')->get();
        return view('quizzes.index', compact('quizzes')); 
    }

    // 2. Menampilkan Form Tambah Kuis
    public function create()
    {
        return view('quizzes.create');
    }

    // 3. Menyimpan Data Kuis ke Database
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        Quiz::create([
            'title' => $request->title,
            'duration_minutes' => $request->duration_minutes,
            'created_by' => auth()->id(), 
        ]);

        return redirect()->route('quizzes.index')->with('success', 'Kuis Utama berhasil dibuat! 🚀');
    }

    // 4. Menampilkan halaman kelola soal untuk kuis tertentu
    public function showQuestions($id)
    {
        $quiz = Quiz::findOrFail($id);
        $questions = QuizQuestion::where('quiz_id', $id)->get(); 
        
        return view('quizzes.questions', compact('quiz', 'questions'));
    }

    // 5. Menyimpan soal baru ke dalam kuis
    public function storeQuestion(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);

        QuizQuestion::create([
            'quiz_id' => $id,
            'question' => $request->question,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_answer' => $request->correct_answer,
        ]);

        return redirect()->back()->with('success', 'Berhasil! Amunisi soal ditambahkan! 🎯');
    }

    // 👇 FITUR BARU DI BAWAH INI 👇

    // 6. Form Edit Kuis
    public function edit($id)
    {
        $quiz = Quiz::findOrFail($id);
        return view('quizzes.edit', compact('quiz'));
    }

    // 7. Simpan Perubahan Kuis
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $quiz = Quiz::findOrFail($id);
        $quiz->update([
            'title' => $request->title,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return redirect()->route('quizzes.index')->with('success', 'Judul/Waktu Kuis berhasil diperbarui! ✏️');
    }

    // 8. Hapus Kuis (Beserta Soal-soalnya)
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        // Hapus semua soal yang menempel di kuis ini
        QuizQuestion::where('quiz_id', $id)->delete();
        // Hapus Kuisnya
        $quiz->delete();

        return redirect()->route('quizzes.index')->with('success', 'Kuis beserta soalnya berhasil dihapus! 🗑️');
    }

    // 9. Hapus Soal Tertentu
    public function destroyQuestion($quiz_id, $question_id)
    {
        $question = QuizQuestion::where('quiz_id', $quiz_id)->where('id', $question_id)->firstOrFail();
        $question->delete();

        return redirect()->back()->with('success', 'Satu soal berhasil dihapus! 🗑️');
    }
}