<?php
// app/Http/Controllers/Api/NoteController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    // GET /api/notes
    public function index(Request $request)
    {
        $notes = Note::where('user_id', (string) $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json(['message' => 'Berhasil', 'data' => $notes]);
    }

    // POST /api/notes
    public function store(Request $request)
    {
        $request->validate(['content' => 'required|string|max:2000']);

        $note = Note::create([
            'user_id' => (string) $request->user()->id,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Catatan disimpan', 'data' => $note], 201);
    }

    // PUT /api/notes/{id}
    public function update(Request $request, $id)
    {
        $request->validate(['content' => 'required|string|max:2000']);

        $note = Note::where('_id', $id)
            ->where('user_id', (string) $request->user()->id)
            ->first();

        if (!$note) return response()->json(['message' => 'Catatan tidak ditemukan'], 404);

        $note->update(['content' => $request->content]);

        return response()->json(['message' => 'Catatan diperbarui', 'data' => $note->fresh()]);
    }

    // DELETE /api/notes/{id}
    public function destroy(Request $request, $id)
    {
        $note = Note::where('_id', $id)
            ->where('user_id', (string) $request->user()->id)
            ->first();

        if (!$note) return response()->json(['message' => 'Catatan tidak ditemukan'], 404);

        $note->delete();

        return response()->json(['message' => 'Catatan dihapus']);
    }
}