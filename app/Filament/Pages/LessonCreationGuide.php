<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * A workflow guide for admins: how to generate coherent lessons + practice banks with Claude Code from
 * their own source material (textbook chapters, past papers), then import them. Pairs the `lesson-authoring`
 * skill (the rules) with the practical upload-and-generate steps and the per-level question minimum.
 */
class LessonCreationGuide extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Create lessons with Claude';

    protected static ?string $title = 'Creating lessons with Claude Code';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.lesson-creation-guide';

    /** The per-level practice-question minimum, surfaced in the guide (kept in one place). */
    public int $minQuestionsPerLevel = 15;
}
