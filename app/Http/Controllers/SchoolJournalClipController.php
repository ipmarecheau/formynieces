<?php

namespace App\Http\Controllers;

use App\Models\SchoolJournalQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * SJ-12 — serves a stored question clip (or clips the stored original
 * client-side is impossible over plain <img>, so the full page when no clip
 * exists) to the guardian who owns the journal. Students never get this route —
 * the honest layer owns the clips.
 */
class SchoolJournalClipController extends Controller
{
    public function show(Request $request, SchoolJournalQuestion $question): BinaryFileResponse
    {
        $entry = $question->entry;
        abort_unless($entry !== null && $entry->student->parent_id === auth()->id(), 403);

        $disk = Storage::disk('local');
        $path = $question->clip_path !== null && $disk->exists($question->clip_path)
            ? $question->clip_path
            : $entry->image_path;

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => str_ends_with($path, '.png') ? 'image/png' : 'image/jpeg',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
