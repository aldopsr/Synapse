<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use Cloudinary\Cloudinary;

class MaterialController extends Controller
{
    private function cloudinary()
    {
        return new Cloudinary(config('cloudinary.cloud_url'));
    }

    private function uploadToCloudinary($file, string $folder, string $resourceType = 'image')
    {
        $uploaded = $this->cloudinary()->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => $folder,
                'resource_type' => $resourceType,
            ]
        );

        return $uploaded['secure_url'];
    }

    public function index()
    {
        $materials = Material::select('id', 'title', 'description', 'content', 'image', 'model_3d_path', 'has_ar', 'has_practice', 'created_at', 'user_id', 'course_id')
            ->with('user:id,name')
            ->with('ar_assets')
            ->with('questions')
            ->latest()
            ->get();

        return response()->json(['message' => 'Berhasil mengambil daftar materi', 'data' => $materials], 200);
    }

    public function show($id)
    {
        $material = Material::with('user:id,name')->with('ar_assets')->with('questions')->find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan!'], 404);
        }

        return response()->json(['message' => 'Berhasil mengambil detail materi', 'data' => $material], 200);
    }

    public function update(Request $request, $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $updateData = $request->only(['title', 'description', 'content', 'visibility']);

        if ($request->hasFile('image')) {
            $updateData['image'] = $this->uploadToCloudinary(
                $request->file('image'),
                'materials',
                'image'
            );
        }

        $material->update($updateData);

        return response()->json(['message' => 'Materi berhasil diperbarui', 'data' => $material], 200);
    }

    public function destroy($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $material->delete();

        return response()->json(['message' => 'Materi berhasil dihapus'], 200);
    }

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
            $imagePath = $this->uploadToCloudinary(
                $request->file('image'),
                'materials',
                'image'
            );
        }

        if ($request->hasFile('model_3d')) {
            $model_3d_path = $this->uploadToCloudinary(
                $request->file('model_3d'),
                'models',
                'raw'
            );

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

        return response()->json(['success' => true, 'message' => 'Materi berhasil ditambahkan!', 'data' => $material]);
    }

    public function getQuestions($material_id)
    {
        $material = Material::with('questions')->find($material_id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        return response()->json(['data' => $material->questions], 200);
    }

    public function storeQuestion(Request $request, $material_id)
    {
        $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'answer' => 'required|string'
        ]);

        $material = Material::find($material_id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $question = \App\Models\Question::create([
            'material_id' => $material_id,
            'question' => $request->question,
            'options' => $request->options,
            'answer' => $request->answer,
            'explanation' => $request->explanation,
        ]);

        return response()->json(['message' => 'Soal berhasil ditambahkan', 'data' => $question], 201);
    }

    public function destroyQuestion($id)
    {
        $question = \App\Models\Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Soal berhasil dihapus'], 200);
    }

    public function attachAr(Request $request, $material_id)
    {
        $material = Material::find($material_id);

        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $material->update(['has_ar' => true]);

        return response()->json(['message' => 'AR berhasil dilampirkan', 'data' => $material]);
    }

    public function arGallery(Request $request)
    {
        $user = $request->user();

        $query = Material::where('has_ar', true)->select('id', 'title', 'course_id', 'model_3d_path');

        if ($user && $user->role === 'dosen') {
            $query->where('course_id', $user->course_id);
        }

        return response()->json(['data' => $query->latest()->get()], 200);
    }

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

            return response()->json($query->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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
            $imagePath = $this->uploadToCloudinary(
                $request->file('image'),
                'materials',
                'image'
            );
        }

        if ($request->hasFile('model_3d')) {
            $model_3d_path = $this->uploadToCloudinary(
                $request->file('model_3d'),
                'models',
                'raw'
            );

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

        return response()->json(['success' => true, 'message' => 'Materi berhasil ditambahkan!', 'data' => $material]);
    }

    public function uploadImage(Request $request)
    {
        if (!$request->hasFile('upload')) {
            return response()->json([
                'error' => [
                    'message' => 'Tidak ada file yang diupload'
                ]
            ], 400);
        }

        $url = $this->uploadToCloudinary(
            $request->file('upload'),
            'ckeditor',
            'image'
        );

        return response()->json([
            'url' => $url
        ]);
    }

    public function explainQuestion(Request $request)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}