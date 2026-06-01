<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MaterialController extends Controller
{
    // 1. MELIHAT SEMUA DAFTAR MATERI
    public function index()
    {
        $materials = Material::select('id', 'title', 'description', 'content', 'image', 'model_3d_path', 'has_ar', 'has_practice', 'created_at', 'user_id', 'course_id')
            ->with('user:id,name')
            ->with('ar_assets')
            ->with('questions')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar materi',
            'data' => $materials
        ], 200);
    }

    // 2. MEMBACA SATU MATERI SECARA DETAIL
    public function show($id)
    {
        $material = Material::with('user:id,name')
            ->with('ar_assets')
            ->with('questions')
            ->find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan!'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail materi',
            'data' => $material
        ], 200);
    }

    // 3. UPDATE MATERI
    public function update(Request $request, $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $updateData = $request->only(['title', 'description', 'content', 'visibility']);

        if ($request->hasFile('image')) {
            $uploaded = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'materials']
            );
            $updateData['image'] = $uploaded->getSecurePath();
        }

        $material->update($updateData);

        return response()->json([
            'message' => 'Materi berhasil diperbarui',
            'data' => $material
        ], 200);
    }

    // 4. HAPUS MATERI
    public function destroy($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $material->delete();

        return response()->json(['message' => 'Materi berhasil dihapus'], 200);
    }

    // 5. STORE MATERI (ADMIN)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'model_3d' => 'nullable|file|max:20480',
        ]);

        $imagePath = null;
        $model_3d_path = null;
        $has_ar = false;

        if ($request->hasFile('image')) {
            $uploaded = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'materials']
            );
            $imagePath = $uploaded->getSecurePath();
        }

        if ($request->hasFile('model_3d')) {
            $uploaded = Cloudinary::uploadFile(
                $request->file('model_3d')->getRealPath(),
                ['folder' => 'models', 'resource_type' => 'raw']
            );
            $model_3d_path = $uploaded->getSecurePath();
            $has_ar = true;
        }

        $material = Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'user_id' => $request->user()->id,
            'image' => $imagePath,
            'model_3d_path' => $model_3d_path,
            'has_ar' => $has_ar,
            'visibility' => $request->input('visibility', 'mahasiswa'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan!',
            'data' => $material
        ]);
    }

    // 6. GET QUESTIONS
    public function getQuestions($material_id)
    {
        $material = Material::with('questions')->find($material_id);
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }
        return response()->json(['data' => $material->questions], 200);
    }

    // 7. STORE QUESTION
    public function storeQuestion(Request $request, $material_id)
    {
        $request->validate([
            'question' => 'required|string',
            'options'  => 'required|array|min:2',
            'answer'   => 'required|string',
        ]);

        $material = Material::find($material_id);
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $question = \App\Models\Question::create([
            'material_id' => $material_id,
            'question'    => $request->question,
            'options'     => $request->options,
            'answer'      => $request->answer,
            'explanation' => $request->explanation,
        ]);

        return response()->json(['message' => 'Soal berhasil ditambahkan', 'data' => $question], 201);
    }

    // 8. DESTROY QUESTION
    public function destroyQuestion($id)
    {
        $question = \App\Models\Question::find($id);
        if (!$question) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        $question->delete();
        return response()->json(['message' => 'Soal berhasil dihapus'], 200);
    }

    // 9. ATTACH AR
    public function attachAr(Request $request, $material_id)
    {
        $material = Material::find($material_id);
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }
        $material->update(['has_ar' => true]);
        return response()->json(['message' => 'AR berhasil dilampirkan', 'data' => $material]);
    }

    // 10. AR GALLERY
    public function arGallery(Request $request)
    {
        $user = $request->user();
        $query = Material::where('has_ar', true)->select('id', 'title', 'course_id', 'model_3d_path');
        if ($user && $user->role === 'dosen') {
            $query->where('course_id', $user->course_id);
        }
        $arAssets = $query->latest()->get();
        return response()->json(['data' => $arAssets], 200);
    }

    // 11. AMBIL MATERI BERDASARKAN ID MATKUL
    public function getByCourse(Request $request, $course_id)
    {
        try {
            $user = $request->user();
            $query = Material::where('course_id', $course_id)
                ->with('ar_assets')
                ->with('questions');
            if (!$user || $user->role === 'public') {
                $query->where('visibility', 'public');
            }
            $materials = $query->get();
            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 12. SIMPAN MATERI KE MATKUL TERTENTU
    public function storeByCourse(Request $request, $course_id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'model_3d' => 'nullable|file|max:20480',
        ]);

        $has_ar = false;
        $model_3d_path = null;
        $imagePath = null;

        if ($request->hasFile('image')) {
            $uploaded = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'materials']
            );
            $imagePath = $uploaded->getSecurePath();
        }

        if ($request->hasFile('model_3d')) {
            $uploaded = Cloudinary::uploadFile(
                $request->file('model_3d')->getRealPath(),
                ['folder' => 'models', 'resource_type' => 'raw']
            );
            $model_3d_path = $uploaded->getSecurePath();
            $has_ar = true;
        }

        $material = Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'course_id' => $course_id,
            'created_by' => $request->user()->id,
            'user_id' => $request->user()->id,
            'image' => $imagePath,
            'model_3d_path' => $model_3d_path,
            'has_ar' => $has_ar,
            'visibility' => $request->input('visibility', 'mahasiswa'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan!',
            'data' => $material
        ]);
    }

    // 13. UPLOAD GAMBAR DARI CKEDITOR
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $uploaded = Cloudinary::upload(
                $request->file('upload')->getRealPath(),
                ['folder' => 'ckeditor']
            );
            return response()->json(['url' => $uploaded->getSecurePath()]);
        }
        return response()->json(['error' => ['message' => 'Gagal upload gambar']], 400);
    }

    // 14. EXPLAIN QUESTION
    public function explainQuestion(Request $request)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}