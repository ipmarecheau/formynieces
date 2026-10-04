<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Registers a native app's push token against the signed-in user. Called by the
 * Capacitor shell after the OS grants push permission (see partials/native-bridge).
 */
class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:20'],
            'app' => ['nullable', 'string', 'max:20'],
        ]);

        // Upsert by token so re-registration (or a token moving between accounts on a
        // shared device) never duplicates and always points at the current user.
        $token = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? null,
                'app' => $data['app'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['registered' => true, 'id' => $token->id]);
    }
}
