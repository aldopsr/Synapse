<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizAttempt; 

class ReportController extends Controller
{
    public function index()
    {
        // Ganti Score dengan QuizAttempt
        $scores = QuizAttempt::with(['user', 'quiz'])
            ->whereHas('quiz', function($query) {
                $query->where('user_id', auth()->id()); // Filter khusus Dosen yg login
            })
            ->latest()
            ->get();

        return view('reports.index', compact('scores'));
    }
}