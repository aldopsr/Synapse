<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MateriController extends Controller
{
    public function create($course_id)
    {
        return view('tambah_materi', compact('course_id'));
    }
}