<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    // 1. MELIHAT SEMUA DAFTAR MATERI
    public function index()
    {
        // 🌟 UPDATE: Tambahkan 'model_3d_path' dan 'has_ar' di select
        $materials = Material::select('id', 'title', 'description', 'image', 'model_3d_path', 'has_ar', 'created_at', 'user_id')
            ->with('user:id,name')
            ->latest()
            ->get();

        $materials->transform(function ($item) {
            if ($item->image) {
                $item->image = asset('storage/' . $item->image); 
            }
            return $item;
        });

        return response()->json([
            'message' => 'Berhasil mengambil daftar materi',
            'data' => $materials
        ], 200);
    }

    // 2. MEMBACA SATU MATERI SECARA DETAIL
    public function show($id)
    {
        $material = Material::with('user:id,name')->find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan!'], 404);
        }

        if ($material->image) {
            $material->image = asset('storage/' . $material->image);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail materi',
            'data' => $material
        ], 200);
    }

    // 3. MEMBUAT MATERI BARU (Dengan Dukungan Upload AR)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'model_3d' => 'nullable|file|max:20480', // Max 20MB untuk file .glb/.gltf
        ]);

        $data = $request->only(['title', 'description', 'content']);
        $data['user_id'] = auth()->id();
        $data['has_ar'] = false; // Default false

        // Handle Upload Gambar Thumbnail
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('materials', 'public');
        }

        // 🌟 UPDATE: Handle Upload File Model 3D untuk AR
        if ($request->hasFile('model_3d')) {
            $path = $request->file('model_3d')->store('models', 'public');
            $data['model_3d_path'] = $path;
            $data['has_ar'] = true; // Tandai materi ini punya AR
        }

        $material = Material::create($data);

        return response()->json([
            'message' => 'Materi berhasil ditambahkan!',
            'data' => $material
        ], 201);
    }

    // 4. MENGAMBIL SOAL LATIHAN
    public function getQuestions($id)
    {
        $questions = \App\Models\Question::where('material_id', $id)->get();

        return response()->json([
            'message' => 'Berhasil mengambil soal latihan',
            'data' => $questions ?? []
        ], 200);
    }

        // 🌟 FUNGSI BARU: Khusus mengambil aset untuk Galeri AR
    public function arGallery()
    {
        $arAssets = Material::where('has_ar', true)
            ->select('id', 'title', 'description', 'image', 'model_3d_path')
            ->latest()
            ->get();

        $arAssets->transform(function ($item) {
            if ($item->image) {
                $item->image = asset('storage/' . $item->image); 
            }
            // Pastikan URL model 3D juga lengkap jika diperlukan
            return $item;
        });

        return response()->json([
            'message' => 'Berhasil mengambil Galeri AR',
            'data' => $arAssets
        ], 200);
    }
}

