<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SyllabusModuleSeeder::class,
            ModulePrerequisiteSeeder::class,
            MathAnchorQuestionSeeder::class,
            ElaAnchorQuestionSeeder::class,
            WritingAnchorQuestionSeeder::class,
            PracticeQuestionSeeder::class,   // 36 practice Qs across modules 1, 3, 52, 73
            WritingPromptSeeder::class,      // current-week Writer's Log prompt
            TestAccountSeeder::class,        // guardian + onboarded student + needs_work row
            TestUserSeeder::class,
        ]);
    }
}
