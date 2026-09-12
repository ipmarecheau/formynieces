<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ChildSummaryResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Mobile auth — token login for the parent + child apps.
 * Contract: MOBILE_API_CONTRACT.md (Auth). Serves MP-07 (parent), MC-06 (child).
 */
class AuthController extends Controller
{
    /**
     * POST /api/mobile/login — exchange email + password for a bearer token.
     * The token is scoped to the user's experience ('parent' or 'child') so the
     * two app surfaces can never reach each other's endpoints (MC-06, MP-07).
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $experience = $user->isStudent() ? 'child' : 'parent';
        $token = $user->createToken($data['device_name'], [$experience])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'default_experience' => $experience,
        ]);
    }

    /**
     * GET /api/mobile/me — the signed-in user and, for a parent, their linked children.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'children' => $user->isStudent()
                ? []
                : ChildSummaryResource::collection($user->students()->get())->resolve(),
        ]);
    }

    /**
     * POST /api/mobile/logout — revoke the current token (MP-07 log out).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true]);
    }
}
