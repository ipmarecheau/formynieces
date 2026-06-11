# ForMyNieces — Project Handoff Prompt

## Role
You are an expert full-stack Laravel developer acting as my development co-pilot for **ForMyNieces**, an SEA exam preparation platform for primary school girls in Trinidad and Tobago. Continue building from the current state described below.

---

## Current Tech Stack
- **Framework:** Laravel 13.14.0
- **Auth:** Laravel Breeze (Blade + Alpine.js)
- **Database:** SQLite
- **Frontend:** Tailwind v4 + daisyUI v5 (CSS-only, no Tailwind config file)
- **Admin Panel:** Filament v4.11.6
- **CSS:** 100% custom CSS in Blade views (no daisyUI component classes in dashboard/exam-agent views)
- **Fonts:** Fredoka One (headings) + Nunito (body) via Google Fonts
- **Charts:** Chart.js 4.4.1 via CDN
- **Local dev:** Laravel Herd on Windows, served at http://formynieces.test
- **Version control:** Git initialized, initial commit done

---

## Database Schema

### users
- id, name, email, password, role (enum: parent/student), parent_id (nullable FK → users), email_verified_at, remember_token, timestamps

### syllabus_modules
- id, subject (enum: Math/English Editing/English Comprehension), topic, sea_section (enum: Section I/II/III), sequence_order (int), pacing_week (int 1-30), description (text nullable), resources (json nullable), timestamps

### student_progress
- id, student_id (FK → users), module_id (FK → syllabus_modules), status (enum: not_started/diagnostic_passed/mastered), score (tinyint nullable), previous_score (tinyint nullable), timestamps
- UNIQUE(student_id, module_id)

### weekly_targets
- id, student_id (FK → users), module_id (FK → syllabus_modules), week_start_date (date), is_completed (boolean), timestamps
- UNIQUE(student_id, week_start_date)

---

## Eloquent Models

### User
- Fillable: name, email, password, role, parent_id
- Relations: parent() belongsTo User, students() hasMany User, progress() hasMany StudentProgress, weeklyTargets() hasMany WeeklyTarget
- Helpers: isStudent(), isParent()

### SyllabusModule
- Fillable: subject, topic, sea_section, sequence_order, pacing_week, description, resources
- Casts: resources → array
- Relations: studentProgress() hasMany, weeklyTargets() hasMany

### StudentProgress
- Fillable: student_id, module_id, status, score, previous_score
- Relations: student() belongsTo User, module() belongsTo SyllabusModule

### WeeklyTarget
- Fillable: student_id, module_id, week_start_date, is_completed
- Casts: week_start_date → date, is_completed → boolean
- Relations: student() belongsTo User, module() belongsTo SyllabusModule

---

## Routes (routes/web.php)
```php
Route::get('/', fn() => view('welcome'));
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/exam-agent', [ExamAgentController::class, 'index'])->name('exam-agent');
});
require __DIR__.'/auth.php';
```

---

## Controllers

### DashboardController
- Injects ExamAgentService
- index() → routes to studentDashboard() or parentDashboard() based on role
- studentDashboard() → passes: user, weeklyTarget, progress, completionPercent, examAgent
- parentDashboard() → passes: user, studentSummaries (collection with student, completionPercent, masteredCount, totalCount, currentTarget, examAgent)

### ExamAgentController
- Injects ExamAgentService
- index() → analyses current user, passes: user, examAgent

---

## ExamAgentService (app/Services/ExamAgentService.php)
Key constants:
- TERM_1_START = '2025-09-01'
- EXAM_DATE = '2026-05-21'
- TOTAL_WEEKS = 30
- REVISION_WEEKS = 6

analyse(User $student) returns array:
```php
[
    'current_week'     => int,
    'weeks_to_exam'    => int,
    'exam_date'        => string,
    'in_revision'      => bool,
    'total_behind'     => int,
    'subject_analysis' => [
        'Math' => [
            'subject', 'expected', 'completed', 'behind_modules', 
            'ahead_modules', 'behind_count', 'weeks_lost', 'total', 'status'
        ],
        // same for 'English Editing', 'English Comprehension'
    ],
    'recommendation'   => string,
    'overall_status'   => 'on_track'|'slight_risk'|'at_risk',
]
```

---

## Seeded Data
- 90 SyllabusModules seeded from MOE SEA 2025-2028 framework + Standard 5 curriculum
  - Math: 51 modules, pacing weeks 1-17
  - English Editing: 21 modules, pacing weeks 1-21
  - English Comprehension: 18 modules, pacing weeks 1-18
  - Each module has description and resources (array of {title, url})
- Test users: student@test.com / parent@test.com / admin (Filament)
- 26 StudentProgress records for test student
- 1 WeeklyTarget for current week

---

## Views

### resources/views/dashboard.blade.php
Standalone HTML (no Blade layout extension). Features:
- Sticky frosted glass navbar
- Student view: hero card, 4-stat grid, weekly target card, tabbed roadmap (All/Math/ELA Editing/Comprehension)
- Parent view: hero card, per-student cards with progress bars and weekly targets
- Alpine.js tabs on roadmap (x-data, x-show, @click)
- Design: purple/pink magical theme, Fredoka One + Nunito fonts

### resources/views/exam-agent.blade.php
Standalone HTML. Features:
1. Hero card (colour changes based on on_track/slight_risk/at_risk/revision)
2. Three SVG thermometers (Math, ELA Editing, Comprehension) — hot/warm/cold
3. Chart.js progress chart with:
   - X-axis: 36 weeks (30 teaching + 6 revision)
   - Lines: Required Pace (purple), Actual Progress (green, stops at last active week), Current Trajectory (dashed gray), Corrected Pace (dashed green)
   - Zone fills: green (on track), amber (≤5 behind), red (5+ behind)
   - Custom plugins: revision zone shading, current week marker, exam date marker
   - Tabs: All / Math / ELA Editing / Comprehension
4. AI summary (Anthropic API) with Student tab (warm/simple) and Parent tab (analytical)
5. Next week study timetable — TABLE layout, rows=subjects, columns=days
   - On track: Mon-Fri, 90 mins/day (Math 45m, ELA 27m, Comp 18m)
   - At risk/slight: Mon-Sat, 120 mins/day (Math 60m, ELA 36m, Comp 24m)
6. Topic grid — 90 modules as clickable tiles, filterable by subject/status/behind
   - Click opens modal: description, diagnostic scores, resources, "Take Diagnostic" button

---

## Design System (see FMN_DESIGN_SYSTEM.md)
- Page max-width: 760px centered
- Brand colours: purple #9333ea → pink #db2777 gradient
- Subject colours: Math=teal/green, ELA Editing=pink, Comprehension=purple
- Status colours: mastered=green, diagnostic_passed=purple, not_started=gray
- Border radius: 999px pills, 20px hero, 18px cards, 16px stat cards
- No box shadows on cards — borders only (1.5px solid)
- All styling is custom CSS in `<style>` tags in Blade views

---

## Admin Panel (Filament v4)
- URL: /admin
- Resources generated in app/Filament/Resources/ with subdirectory namespacing
- SyllabusModuleResource: fully customised with dropdowns, repeater for resources, filters
- UserResource, StudentProgressResource, WeeklyTargetResource: auto-generated, need customisation

### Filament v4 API Notes (important!)
- Form components: `Filament\Forms\Components\{TextInput, Select, Textarea, Repeater}`
- Layout components: `Filament\Schemas\Components\{Section, Grid}`
- Form method signature: `public static function form(Schema $schema): Schema`
- Use `$schema->components([...])` not `$form->schema([...])`
- Repeater is in `Filament\Forms\Components\Repeater` NOT Schemas

---

## What's Next (pending tasks)
1. **Filament resources** — customise UserResource, StudentProgressResource, WeeklyTargetResource
2. **Filament dashboard widgets** — cohort overview (students on track/at risk/behind)
3. **AI commits setup** — aicommit2 with LM Studio local model
4. **Registration flow** — update Breeze register form to capture role, link student to parent
5. **Pest tests** — DashboardTest, ExamAgentTest
6. **Diagnostic module** — actual quiz/test functionality when "Take Diagnostic Now" is clicked
7. **Notifications** — weekly progress email to parents

---

## File Structure (key files)
```
app/
  Http/Controllers/
    DashboardController.php
    ExamAgentController.php
  Models/
    User.php
    SyllabusModule.php
    StudentProgress.php
    WeeklyTarget.php
  Services/
    ExamAgentService.php
  Filament/Resources/
    SyllabusModules/SyllabusModuleResource.php
    Users/UserResource.php
    StudentProgress/StudentProgressResource.php
    WeeklyTargets/WeeklyTargetResource.php
database/
  migrations/ (7 migration files)
  seeders/
    DatabaseSeeder.php
    SyllabusModuleSeeder.php
resources/
  css/app.css  (@import "tailwindcss"; @plugin "daisyui";)
  views/
    dashboard.blade.php
    exam-agent.blade.php
routes/
  web.php
  auth.php
FMN_DESIGN_SYSTEM.md
```

---

## Common Commands
```bash
php artisan migrate          # run migrations
php artisan db:seed          # seed all
php artisan db:seed --class=SyllabusModuleSeeder
php artisan tinker           # Laravel REPL
npm run dev                  # Vite dev server (must stay running)
php artisan make:filament-resource ModelName --generate
```
