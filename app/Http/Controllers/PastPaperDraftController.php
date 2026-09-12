<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PastPaperDraftController extends Controller
{
    public function source(Request $request, string $draft)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless(preg_match('/^[A-Za-z0-9._-]+\.json$/', $draft) === 1, 404);
        $disk = Storage::disk('local');
        $draftPath = 'past-paper-source/extractions/'.$draft;
        abort_unless($disk->exists($draftPath), 404);
        $data = json_decode($disk->get($draftPath), true);
        $filename = basename((string) ($data['filename'] ?? ''));
        abort_unless($filename !== '' && preg_match('/^[A-Za-z0-9._ -]+\.pdf$/i', $filename) === 1, 404);
        $path = collect($disk->allFiles('past-paper-source'))->first(fn (string $candidate) => basename($candidate) === $filename);
        abort_unless($path !== null, 404);

        return response()->file($disk->path($path), ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.$filename.'"']);
    }

    public function page(Request $request, string $draft, int $page = 1)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless(preg_match('/^[A-Za-z0-9._-]+\.json$/', $draft) === 1 && $page > 0 && $page < 1000, 404);
        $disk = Storage::disk('local');
        $draftPath = 'past-paper-source/extractions/'.$draft;
        abort_unless($disk->exists($draftPath), 404);
        $data = json_decode($disk->get($draftPath), true);
        $filename = basename((string) ($data['filename'] ?? ''));
        $render = 'past-paper-source/rendered/'.sha1($filename).'/page-'.str_pad((string) $page, 3, '0', STR_PAD_LEFT).'.png';
        abort_unless($disk->exists($render), 404);
        return response()->file($disk->path($render), ['Content-Type' => 'image/png']);
    }
}
