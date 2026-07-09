<?php

namespace App\Services\Pacing;

use App\Models\Setting;
use App\Models\User;

class CapResolver
{
    private const FALLBACK_CAP = 5;

    public function resolve(User $student): int
    {
        if (! is_null($student->weekly_module_cap_override)) {
            return (int) $student->weekly_module_cap_override;
        }

        $global = Setting::get('weekly_module_cap');

        if (! is_null($global)) {
            return (int) $global;
        }

        return self::FALLBACK_CAP;
    }
}
