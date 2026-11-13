<?php

namespace App\Http\Controllers;

use App\Services\Reminders\ReminderScheduleBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The self-contained reminder plan the native app reads on launch and writes to the device
 * so reminders fire offline, with the app closed (LR-01, LR-07).
 *
 * @see formynieces-spec/features/local_reminders.feature
 */
class ReminderFeedController extends Controller
{
    public function __invoke(Request $request, ReminderScheduleBuilder $builder): JsonResponse
    {
        $student = $request->user();

        $reminders = array_map(
            fn ($reminder) => $reminder->toArray(),
            $builder->forStudent($student),
        );

        return response()->json(['reminders' => $reminders]);
    }
}
