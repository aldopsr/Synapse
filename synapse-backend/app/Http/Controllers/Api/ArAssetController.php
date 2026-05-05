<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArAsset;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;

class ArAssetController extends Controller
{
    /**
     * 1. GALERI: Ambil SEMUA AR assets untuk ditampilkan di Galeri AR
     *    - Mahasiswa/Publik: lihat semua AR
     *    - Dosen: hanya AR untuk matkul yang dia pegang
     *    - Admin/Superadmin: lihat semua
     *    Endpoint: GET /api/ar-assets
     */
    public function index(Request $request)
    {
        $query = ArAsset::with(['material:id,title', 'course:id,title']);

        // 🌟 Jika user login dan dia dosen, filter by course_id-nya
        $user = $request->user();
        if ($user && $user->role === 'dosen' && isset($user->course_id)) {
            $query->where('course_id', $user->course_id);
        }

        $arAssets = $query->latest()->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar AR Assets',
            'data' => $arAssets
        ], 200);
    }

    /**
     * 2. LIST PER MATERI: Ambil semua AR assets untuk satu materi tertentu
     *    Endpoint: GET /api/materials/{materialId}/ar-assets
     */
    public function getByMaterial($materialId)
    {
        $material = Material::find($materialId);
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $arAssets = ArAsset::where('material_id', $materialId)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil AR Assets untuk materi ini',
            'material' => [
                'id'    => $material->id,
                'title' => $material->title,
            ],
            'data' => $arAssets
        ], 200);
    }

    /**
     * 3. DETAIL: Ambil 1 AR asset by ID
     *    Endpoint: GET /api/ar-assets/{id}
     */
    public function show($id)
    {
        $arAsset = ArAsset::with('material:id,title')->find($id);

        if (!$arAsset) {
            return response()->json(['message' => 'AR Asset tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail AR Asset',
            'data' => $arAsset
        ], 200);
    }

    /**
     * 4. CREATE: Upload AR baru (file 3D + thumbnail auto-generated)
     *    Endpoint: POST /api/materials/{materialId}/ar-assets
     *    Body (multipart/form-data):
     *      - title (required)
     *      - description (optional)
     *      - model_3d (required, file .glb/.gltf, max 20MB)
     *      - thumbnail (optional, file image, max 2MB) ← di-generate auto oleh JS
     */
    public function store(Request $request, $materialId)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'model_3d' => 'required|file|max:102400', // Max 100MB
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $material = Material::find($materialId);
        if (!$material) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        $data = [
            'material_id' => $materialId,
            'course_id'   => $material->course_id, // 🌟 Auto-isi course_id dari materi
            'user_id'     => auth()->id(),
            'title'       => $request->title,
            'description' => $request->description,
        ];

        // Upload file model 3D ke folder ar_models/
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('ar_models', 'public');
            $data['model_3d_path'] = $modelPath;
        }

        // Upload thumbnail (auto-generated dari Three.js) ke folder ar_thumbnails/
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('ar_thumbnails', 'public');
            $data['image'] = $thumbnailPath;
        }

        $arAsset = ArAsset::create($data);

        return response()->json([
            'message' => 'AR Asset berhasil ditambahkan!',
            'data' => $arAsset
        ], 201);
    }

    /**
     * 5. UPDATE: Edit AR asset (ganti title/description/file)
     *    Endpoint: PUT /api/ar-assets/{id}
     *    Note: pakai POST + _method=PUT karena multipart/form-data
     */
    public function update(Request $request, $id)
    {
        $arAsset = ArAsset::find($id);
        if (!$arAsset) {
            return response()->json(['message' => 'AR Asset tidak ditemukan'], 404);
        }

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'model_3d'    => 'nullable|file|max:20480',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $updateData = $request->only(['title', 'description']);

        // Kalau ada file model_3d baru, hapus yang lama lalu simpan yang baru
        if ($request->hasFile('model_3d')) {
            if ($arAsset->model_3d_path && Storage::disk('public')->exists($arAsset->model_3d_path)) {
                Storage::disk('public')->delete($arAsset->model_3d_path);
            }
            $updateData['model_3d_path'] = $request->file('model_3d')->store('ar_models', 'public');
        }

        // Kalau ada thumbnail baru, hapus yang lama lalu simpan yang baru
        if ($request->hasFile('thumbnail')) {
            if ($arAsset->image && Storage::disk('public')->exists($arAsset->image)) {
                Storage::disk('public')->delete($arAsset->image);
            }
            $updateData['image'] = $request->file('thumbnail')->store('ar_thumbnails', 'public');
        }

        $arAsset->update($updateData);

        return response()->json([
            'message' => 'AR Asset berhasil diupdate!',
            'data' => $arAsset->fresh()
        ], 200);
    }

    /**
     * 6. DELETE: Hapus AR asset (sekaligus hapus file fisiknya)
     *    Endpoint: DELETE /api/ar-assets/{id}
     */
    public function destroy($id)
    {
        $arAsset = ArAsset::find($id);
        if (!$arAsset) {
            return response()->json(['message' => 'AR Asset tidak ditemukan'], 404);
        }

        // Hapus file fisik dari storage
        if ($arAsset->model_3d_path && Storage::disk('public')->exists($arAsset->model_3d_path)) {
            Storage::disk('public')->delete($arAsset->model_3d_path);
        }
        if ($arAsset->image && Storage::disk('public')->exists($arAsset->image)) {
            Storage::disk('public')->delete($arAsset->image);
        }

        $arAsset->delete();

        return response()->json([
            'message' => 'AR Asset berhasil dihapus!'
        ], 200);
    }
}