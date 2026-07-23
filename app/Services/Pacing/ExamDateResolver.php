<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\Setting;
use Carbon\CarbonImmutable;

final class ExamDateResolver
{
    /**
     * Resolve the SEA exam date for the given target year, preferring an
     * admin-set official date over the derived early-April default.
     */
    public function resolve(int $year): CarbonImmutable
    {
        $override = Setting::get("sea_exam_date_{$year}");

        if ($override !== null && $override !== '') {
            return CarbonImmutable::parse($override);
        }

        return CarbonImmutable::create($year, 4, 1);
    }
}
