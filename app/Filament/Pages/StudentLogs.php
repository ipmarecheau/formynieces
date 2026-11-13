<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Admin\ActivityEvent;
use App\Services\Admin\StudentActivityLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * AL-01..03 — a read-only, per-student activity timeline for the team: pick a child and see
 * their recent journey (practice, mastery, essays, day-completions, help tickets) newest
 * first, to catch problems early. Composed from existing records; no new capture.
 */
class StudentLogs extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Student Logs';

    protected static ?string $title = 'Student Logs';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected string $view = 'filament.pages.student-logs';

    public ?int $studentId = null;

    /**
     * @return array<int, string>
     */
    public function students(): array
    {
        return User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return list<ActivityEvent>
     */
    public function events(): array
    {
        if ($this->studentId === null) {
            return [];
        }

        $student = User::find($this->studentId);

        return $student ? app(StudentActivityLog::class)->forStudent($student) : [];
    }
}
