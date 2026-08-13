<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\Lessons\LessonExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads a single lesson as a JSON bundle (LB-02), for the per-row export on the Lessons list.
 * A dedicated route (rather than a table-action response) so the browser reliably downloads it.
 */
class LessonExportController extends Controller
{
    public function __invoke(Lesson $lesson, LessonExporter $exporter): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $lesson->loadMissing('module');

        return response()->streamDownload(
            fn () => print ($exporter->exportLesson($lesson)),
            'lesson-'.($lesson->module?->code ?? $lesson->id).'.json',
            ['Content-Type' => 'application/json'],
        );
    }
}
