<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The in-app help inbox. The floating help button (any screen) posts here with a typed
 * message and/or a recorded voice note, plus where the user was. Kept deliberately
 * forgiving so a struggling user's cry for help is never lost to validation.
 */
class SupportTicketController extends Controller
{
    private const KINDS = ['issue', 'question', 'idea'];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'kind' => ['nullable', 'string', 'in:'.implode(',', self::KINDS)],
            'url' => ['nullable', 'string', 'max:2048'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'context' => ['nullable', 'array'],
            'audio' => ['nullable', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav,video/webm', 'max:10240'],
        ]);

        // Must carry at least a message or a voice note.
        if (blank($data['message'] ?? null) && ! $request->hasFile('audio')) {
            return response()->json(['ok' => false, 'error' => 'Please type a message or record a voice note.'], 422);
        }

        $audioPath = null;
        if ($request->hasFile('audio')) {
            // Private disk — voice notes are only reachable through the gated admin panel.
            $audioPath = $request->file('audio')->store('support-audio', 'local');
        }

        $user = $request->user();

        $ticket = SupportTicket::create([
            'user_id' => $user?->id,
            'role' => $this->roleOf($user),
            'student_id' => $data['student_id'] ?? null,
            'kind' => $data['kind'] ?? 'issue',
            'message' => $data['message'] ?? null,
            'audio_path' => $audioPath,
            'url' => $data['url'] ?? $request->headers->get('referer'),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'context' => $data['context'] ?? null,
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return response()->json(['ok' => true, 'id' => $ticket->id], 201);
    }

    /**
     * Stream a ticket's private voice note for the admin panel only. The file lives on the
     * private 'local' disk and is never publicly reachable; this route is gated role:admin.
     */
    public function audio(SupportTicket $ticket): StreamedResponse
    {
        abort_if(blank($ticket->audio_path) || ! Storage::disk('local')->exists($ticket->audio_path), 404);

        return Storage::disk('local')->response($ticket->audio_path);
    }

    private function roleOf(?User $user): ?string
    {
        if ($user === null) {
            return 'guest';
        }

        return match (true) {
            $user->isStudent() => 'student',
            $user->isAdmin() => 'admin',
            $user->isGuardian() => 'guardian',
            default => (string) $user->role,
        };
    }
}
