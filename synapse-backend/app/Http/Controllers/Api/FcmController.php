<?php
// ============================================================
// STEP 1B: FcmController
// Taruh di: synapse-backend/app/Http/Controllers/Api/FcmController.php
// ============================================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FcmToken;

class FcmController extends Controller
{
    // POST /api/fcm-token
    // Simpan atau update FCM token device user yang login.
    // Dipanggil dari Flutter setiap kali app dibuka / token diperbarui.
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $userId = (string) $request->user()->id;

        // Upsert: kalau token untuk user ini sudah ada, update.
        // Kalau belum, insert baru.
        FcmToken::updateOrInsert(
            ['user_id' => $userId],
            [
                'user_id'    => $userId,
                'token'      => $request->token,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'FCM token disimpan'], 200);
    }

    // DELETE /api/fcm-token
    // Hapus token saat logout agar notifikasi tidak dikirim ke device lama.
    public function destroy(Request $request)
    {
        $userId = (string) $request->user()->id;
        FcmToken::where('user_id', $userId)->delete();
        return response()->json(['message' => 'FCM token dihapus'], 200);
    }
}