<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    // 1. MELIHAT SEMUA DAFTAR MATERI (Untuk Mahasiswa & Publik)
    public function index()
    {
        // 🌟 UPDATE: Tambahkan 'description' dan 'image' di select()
        $materials = Material::select('id', 'title', 'description', 'image', 'created_at', 'user_id')
            ->with('user:id,name') // Ambil juga nama Dosen pembuatnya
            ->latest()
            ->get();

        // 🌟 UPDATE: Ubah path gambar menjadi URL utuh agar bisa dibaca Flutter
        $materials->transform(function ($item) {
            if ($item->image) {
                // asset() akan otomatis menambahkan nama domain web Kapten di depan nama file
                $item->image = asset('storage/' . $item->image); 
            }
            return $item;
        });

        return response()->json([
            'message' => 'Berhasil mengambil daftar materi',
            'data' => $materials
        ], 200);
    }

    // 2. MEMBACA SATU MATERI SECARA DETAIL (Klik dari daftar)
    public function show($id)
    {
        $material = Material::with('user:id,name')->find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan!'], 404);
        }

        // 🌟 UPDATE: Ubah path gambar menjadi URL utuh untuk halaman detail
        if ($material->image) {
            $material->image = asset('storage/' . $material->image);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail materi',
            'data' => $material
        ], 200);
    }

    // 3. MEMBUAT MATERI BARU (Dari API / Mobile)
    // Catatan: Biasanya dosen pakai Web Admin, tapi fungsi ini kita update juga buat jaga-jaga
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string', // Tambahan
            'content' => 'required|string',
        ]);

        $material = Material::create([
            'title' => $request->title,
            'description' => $request->description, // Tambahan
            'content' => $request->content,
            'user_id' => auth()->id(), 
        ]);

        return response()->json([
            'message' => 'Materi berhasil ditambahkan via API!',
            'data' => $material
        ], 201);
    }

    // 4. MENGAMBIL SOAL LATIHAN
    public function getQuestions($id)
    {
        $questions = \App\Models\Question::where('material_id', $id)->get();

        if ($questions->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada latihan untuk materi ini',
                'data' => []
            ], 200); 
        }

        return response()->json([
            'message' => 'Berhasil mengambil soal latihan',
            'data' => $questions
        ], 200);
    }
}