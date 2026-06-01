<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArAsset;
use App\Models\Material;

class ArAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = ArAsset::with(['material:id,title', 'course:id,title']);
        $user = $request->user();
        if ($user && $user->role === 'dosen' && isset($user->course_id)) {
            $query->where('course_id', $user->course_id);
        }
        $arAssets = $query->latest()->get();
        return response()->json(['message' => 'Berhasil mengambil daftar AR Assets', 'data' => $arAssets], 200);
    }

    public function getByMaterial($materialId)
    {
        $material = Material::find($materialId);
        if (!$material) return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        $arAssets = ArAsset::where('material_id', $materialId)->latest()->get();
        return response()->json(['message' => 'Berhasil mengambil AR Assets untuk materi ini', 'material' => ['id' => $material->id, 'title' => $material->title], 'data' => $arAssets], 200);
    }

    public function show($id)
    {
        $arAsset = ArAsset::with('material:id,title')->find($id);
        if (!$arAsset) return response()->json(['message' => 'AR Asset tidak ditemukan'], 404);
        return response()->json(['message' => 'Berhasil mengambil detail AR Asset', 'data' => $arAsset], 200);
    }

    public function store(Request $request, $materialId)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'model_3d'    => 'required|file|max:102400',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $material = Material::find($materialId);
        if (!$material) return response()->json(['message' => 'Materi tidak ditemukan'], 404);

        $data = [
            'material_id' => $materialId,
            'course_id'   => $material->course_id,
            'user_id'     => auth()->id(),
            'title'       => $request->title,
            'description' => $request->description,
        ];

    if ($request->hasFile('model_3d')) {

        $uploadedModel = $request->file('model_3d')->storeOnCloudinaryAs(
            'ar_models',
            pathinfo(
                $request->file('model_3d')->getClientOriginalName(),
                PATHINFO_FILENAME
            ),
            [
                'resource_type' => 'raw'
            ]
        );

        $data['model_3d_path'] = $uploadedModel->getSecurePath();
    }

    if ($request->hasFile('thumbnail')) {

        $uploadedThumbnail = $request->file('thumbnail')
            ->storeOnCloudinary('ar_thumbnails');

        $data['image'] = $uploadedThumbnail->getSecurePath();
    }

        $arAsset = ArAsset::create($data);
        return response()->json(['message' => 'AR Asset berhasil ditambahkan!', 'data' => $arAsset], 201);
    }

    public function update(Request $request, $id)
    {
        $arAsset = ArAsset::find($id);
        if (!$arAsset) return response()->json(['message' => 'AR Asset tidak ditemukan'], 404);

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'model_3d'    => 'nullable|file|max:102400',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $updateData = $request->only(['title', 'description']);

        if ($request->hasFile('model_3d')) {

            $uploadedModel = $request->file('model_3d')->storeOnCloudinaryAs(
                'ar_models',
                pathinfo(
                    $request->file('model_3d')->getClientOriginalName(),
                    PATHINFO_FILENAME
                ),
                [
                    'resource_type' => 'raw'
                ]
            );

            $updateData['model_3d_path'] = $uploadedModel->getSecurePath();
        }

        if ($request->hasFile('thumbnail')) {

            $uploadedThumbnail = $request->file('thumbnail')
                ->storeOnCloudinary('ar_thumbnails');

            $updateData['image'] = $uploadedThumbnail->getSecurePath();
        }

        $arAsset->update($updateData);
        return response()->json(['message' => 'AR Asset berhasil diupdate!', 'data' => $arAsset->fresh()], 200);
    }

    public function destroy($id)
    {
        $arAsset = ArAsset::find($id);
        if (!$arAsset) return response()->json(['message' => 'AR Asset tidak ditemukan'], 404);
        $arAsset->delete();
        return response()->json(['message' => 'AR Asset berhasil dihapus!'], 200);
    }
}