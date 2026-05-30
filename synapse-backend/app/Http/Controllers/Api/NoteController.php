<?php
// app/Http/Controllers/Api/NoteController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = Note::where('user_id', (string) $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json(['message' => 'Berhasil', 'data' => $notes]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content'     => 'required|string|max:2000',
            'color_index' => 'nullable|integer|min:0|max:7',
        ]);

        $note = Note::create([
            'user_id'     => (string) $request->user()->id,
            'content'     => $request->content,
            'color_index' => $request->color_index ?? 0,
        ]);

        return response()->json(['message' => 'Catatan disimpan', 'data' => $note], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content'     => 'required|string|max:2000',
            'color_index' => 'nullable|integer|min:0|max:7',
        ]);

        $note = Note::where('_id', $id)
            ->where('user_id', (string) $request->user()->id)
            ->first();

        if (!$note) return response()->json(['message' => 'Tidak ditemukan'], 404);

        $note->update([
            'content'     => $request->content,
            'color_index' => $request->color_index ?? $note->color_index ?? 0,
        ]);

        return response()->json(['message' => 'Catatan diperbarui', 'data' => $note->fresh()]);
    }

    public function destroy(Request $request, $id)
    {
        $note = Note::where('_id', $id)
            ->where('user_id', (string) $request->user()->id)
            ->first();

        if (!$note) return response()->json(['message' => 'Tidak ditemukan'], 404);

        $note->delete();
        return response()->json(['message' => 'Catatan dihapus']);
    }
}