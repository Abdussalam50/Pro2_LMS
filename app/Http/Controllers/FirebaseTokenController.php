<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FirebaseTokenController extends Controller
{
    /** Simpan FCM token ke kolom users.fcm_token */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        $request->user()->update(['fcm_token' => $request->token]);

        return response()->json(['status' => 'ok']);
    }

    /** Hapus FCM token (saat logout) */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->update(['fcm_token' => null]);

        return response()->json(['status' => 'ok']);
    }
}
