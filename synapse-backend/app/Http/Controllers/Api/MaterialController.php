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
    // 🌟 UPDATE: Tambahkan 'content' di select
    $materials = Material::select('id', 'title', 'description', 'content', 'image', 'model_3d_path', 'has_ar','has_practice', 'created_at', 'user_id')
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

    // 2. FUNGSI UNTUK MENYIMPAN HASIL EDIT
    public function update(Request $request, $id)
    {
        $material = Material::find($id);
        
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        // Update data sesuai request dari frontend
        $material->update([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Materi berhasil diperbarui',
            'data' => $material
        ], 200);
    }

    // 3. FUNGSI UNTUK MENGHAPUS MATERI
    public function destroy($id)
    {
        $material = Material::find($id);
        
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $material->delete();

        return response()->json(['message' => 'Materi berhasil dihapus'], 200);
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

    // 5. MENYIMPAN SOAL LATIHAN BARU
    public function storeQuestion(Request $request, $material_id)
{
    $request->validate([
        'question_text' => 'required|string',
        'option_a' => 'required|string',
        'option_b' => 'required|string',
        'option_c' => 'required|string',
        'option_d' => 'required|string',
        'correct_answer' => 'required|in:a,b,c,d',
    ]);

    $question = \App\Models\Question::create([
        'material_id' => $material_id,
        'question_text' => $request->question_text,
        'option_a' => $request->option_a,
        'option_b' => $request->option_b,
        'option_c' => $request->option_c,
        'option_d' => $request->option_d,
        'correct_answer' => $request->correct_answer,
    ]);

    // 🌟 TAMBAHKAN INI: Update status has_practice di materi terkait
    $material = Material::find($material_id);
    if ($material) {
        $material->update(['has_practice' => true]);
    }

    return response()->json([
        'message' => 'Soal berhasil ditambahkan',
        'data' => $question
    ], 201);
}

    // 6. MENGHAPUS SOAL LATIHAN
    public function destroyQuestion($id)
{
    $question = \App\Models\Question::find($id);
    
    if (!$question) {
        return response()->json(['message' => 'Soal tidak ditemukan'], 404);
    }

    $materialId = $question->material_id;
    $question->delete();

    // 🌟 CEK: Jika sudah tidak ada soal lagi, ubah has_practice jadi false
    $remainingQuestions = \App\Models\Question::where('material_id', $materialId)->count();
    if ($remainingQuestions == 0) {
        Material::where('id', $materialId)->update(['has_practice' => false]);
    }

    return response()->json(['message' => 'Soal berhasil dihapus'], 200);
}

    // FUNGSI KHUSUS MENGUPLOAD AR KE MATERI YANG SUDAH ADA
    public function attachAr(Request $request, $material_id)
    {
        $request->validate([
            'model_3d' => 'required|file|max:20480'
        ]);

        $material = Material::find($material_id);
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $path = $request->file('model_3d')->store('models', 'public');
        
        $material->update([
            'model_3d_path' => $path,
            'has_ar' => true
        ]);

        return response()->json(['message' => 'Aset AR berhasil ditambahkan ke materi!']);
    }

    // UPDATE FUNGSI AR GALLERY (Bisa filter Admin vs Dosen)
    public function arGallery(Request $request)
    {
        $user = $request->user();
        
        // Mulai query cari materi yang ada AR-nya
        $query = Material::where('has_ar', true)->select('id', 'title', 'course_id', 'model_3d_path');

        // Jika dia dosen, hanya tampilkan AR miliknya (berdasarkan course_id dia)
        if ($user->role === 'dosen') {
            $query->where('course_id', $user->course_id);
        }

        $arAssets = $query->latest()->get();

        return response()->json(['data' => $arAssets], 200);
    }

    // 1. AMBIL MATERI BERDASARKAN ID MATKUL
    public function getByCourse($course_id)
    {
        // Cari materi yang course_id-nya sesuai
        $materials = Material::where('course_id', $course_id)->get();
        
        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    // 2. SIMPAN MATERI KE MATKUL TERTENTU
    public function storeByCourse(Request $request, $course_id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'model_3d' => 'nullable|file|max:20480', // Validasi file 3D
        ]);

        $has_ar = false;
        $model_3d_path = null;

        // Tangkap file AR jika ada
        if ($request->hasFile('model_3d')) {
            $model_3d_path = $request->file('model_3d')->store('models', 'public');
            $has_ar = true;
        }

        $material = Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'course_id' => $course_id,
            'created_by' => $request->user()->id,
            'model_3d_path' => $model_3d_path, // Masukkan path
            'has_ar' => $has_ar, // Masukkan status
        ]);

        return response()->json(['success' => true, 'message' => 'Materi berhasil ditambahkan!', 'data' => $material]);
    }

    // FUNGSI UPLOAD GAMBAR DARI CKEDITOR
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            // Buat nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();
            // Simpan ke folder storage/app/public/ckeditor
            $path = $file->storeAs('ckeditor', $filename, 'public');
            
            // Buat URL lengkapnya
            $url = asset('storage/' . $path);

            return response()->json([
                'url' => $url // Format yang diminta oleh CKEditor
            ]);
        }

        return response()->json(['error' => ['message' => 'Gagal upload gambar']], 400);
    }
}