<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material; 
use Illuminate\Support\Facades\Storage;

class WebMaterialController extends Controller
{
    // Fungsi untuk menampilkan halaman Daftar Materi
    public function index()
    {
        // Ambil semua materi dari database yang DIBUAT OLEH DOSEN YANG SEDANG LOGIN
        $materials = Material::where('user_id', auth()->id())->get();
        
        // Lempar datanya ke file HTML (Blade)
        return view('materials.index', compact('materials'));
    }

    // Fungsi untuk membuka halaman Form Tambah Materi
    public function create()
    {
        return view('materials.create');
    }

    // Fungsi untuk menangkap data dari Form dan menyimpannya ke Database
    // Fungsi untuk menangkap data dari Form dan menyimpannya ke Database
    public function store(Request $request)
    {
        // 1. Validasi: Pastikan format gambar benar (maks 2MB)
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' // Validasi Foto
        ]);

        $material = new Material();
        $material->title = $request->title;
        $material->description = $request->description;
        $material->content = $request->content;
        $material->user_id = auth()->id();

        // 2. Cek apakah Dosen meng-upload foto?
        if ($request->hasFile('image')) {
            // Simpan foto ke folder storage/app/public/materials
            $imagePath = $request->file('image')->store('materials', 'public');
            $material->image = $imagePath;
        }

        $material->save();

        return redirect()->route('materials.index')->with('success', 'Yeay! Materi E-Modul baru berhasil ditambahkan! 📚');
    }

    // Fungsi untuk membuka halaman Form Edit
    public function edit($id)
    {
        $material = Material::findOrFail($id); // Cari materi berdasarkan ID
        return view('materials.edit', compact('material'));
    }

    // Fungsi untuk menyimpan perubahan data
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' // Validasi jika ada gambar baru
        ]);

        $material = Material::findOrFail($id);
        $material->title = $request->title;
        $material->description = $request->description;
        $material->content = $request->content;

        // 2. Cek apakah Dosen meng-upload foto BARU?
        if ($request->hasFile('image')) {
            
            // Hapus foto lama dari brankas server (jika sebelumnya punya foto)
            if ($material->image) {
                Storage::disk('public')->delete($material->image);
            }
            
            // Simpan foto baru
            $imagePath = $request->file('image')->store('materials', 'public');
            $material->image = $imagePath;
        }

        $material->save();

        return redirect()->route('materials.index')->with('success', 'E-Modul berhasil diperbarui! ✏️📚');
    }

    // Fungsi untuk menghapus materi
    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Materi berhasil dihapus!');
    }

    // Fungsi khusus untuk menangani gambar yang di-paste/di-upload ke dalam CKEditor
    public function uploadImage(Request $request)
    {
        // CKEditor secara default mengirim file dengan nama 'upload'
        if ($request->hasFile('upload')) { 
            $file = $request->file('upload');
            
            // Buat nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke folder public/storage/materials/editor_images
            $path = $file->storeAs('materials/editor_images', $filename, 'public');

            // Kembalikan respons JSON berisi URL gambar ke CKEditor
            return response()->json([
                'url' => asset('storage/' . $path)
            ]);
        }

        // Jika gagal
        return response()->json(['error' => ['message' => 'Gagal mengunggah gambar.']]);
    }
}