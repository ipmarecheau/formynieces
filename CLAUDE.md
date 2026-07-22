<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- filament/filament (FILAMENT) - v4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== filament/filament rules ===

## Filament

- Filament is a Laravel UI framework built on Livewire, Alpine.js, and Tailwind CSS. UIs are defined in PHP via fluent, chainable components. Follow existing conventions in this app.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Inspect required options before running, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `Set $set` inside `->afterStateUpdated()` on a `->live()` field to mutate another field reactively. Prefer `->live(onBlur: true)` on text inputs to avoid per-keystroke updates:

<code-snippet name="Reactive field update" lang="php">
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

TextInput::make('title')
    ->required()
    ->live(onBlur: true)
    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
        'slug',
        Str::slug($state ?? ''),
    )),

TextInput::make('slug')
    ->required(),

</code-snippet>

Compose layout by nesting `Section` and `Grid`. Children need explicit `->columnSpan()` or `->columnSpanFull()`:

<code-snippet name="Section and Grid layout" lang="php">
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

Section::make('Details')
    ->schema([
        Grid::make(2)->schema([
            TextInput::make('first_name')
                ->columnSpan(1),
            TextInput::make('last_name')
                ->columnSpan(1),
            TextInput::make('bio')
                ->columnSpanFull(),
        ]),
    ]),

</code-snippet>

Use `Repeater` for inline `HasMany` management. `->relationship()` with no args binds to the relationship matching the field name:

<code-snippet name="Repeater for HasMany" lang="php">
use Filament\Forms\Components\Repeater;

Repeater::make('qualifications')
    ->relationship()
    ->schema([
        TextInput::make('institution')
            ->required(),
        TextInput::make('qualification')
            ->required(),
    ])
    ->columns(2),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Use `SelectFilter` for enum or relationship filters, and `Filter` with a `->query()` closure for custom logic:

<code-snippet name="Table filters" lang="php">
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

SelectFilter::make('status')
    ->options(UserStatus::class),

SelectFilter::make('author')
    ->relationship('author', 'name'),

Filter::make('verified')
    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at')),

</code-snippet>

Actions are buttons that encapsulate optional modal forms and behavior:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data)),

</code-snippet>

### Testing

Testing setup (requires `pestphp/pest-plugin-livewire` in `composer.json`):

- Always call `$this->actingAs(User::factory()->create())` before testing panel functionality.
- For edit pages, pass `['record' => $user->id]`, use `->call('save')` (not `->call('create')`), and do not assert `->assertRedirect()` (edit pages do not redirect after save).

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertHasNoFormErrors()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Edit resource test" lang="php">
livewire(EditUser::class, ['record' => $user->id])
    ->fillForm(['name' => 'Updated'])
    ->call('save')
    ->assertNotified()
    ->assertHasNoFormErrors();

assertDatabaseHas(User::class, [
    'id' => $user->id,
    'name' => 'Updated',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

Use `->callAction(DeleteAction::class)` for page actions, or `->callAction(TestAction::make('name')->table($record))` for table actions:

<code-snippet name="Calling actions" lang="php">
use Filament\Actions\Testing\TestAction;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, `Repeater`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Table columns (`TextColumn`, `IconColumn`, etc.): `Filament\Tables\Columns\`
- Table filters (`SelectFilter`, `Filter`, etc.): `Filament\Tables\Filters\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, `Fieldset`, and `Repeater` do not span all columns by default.
- **Use `Select::make('author_id')->relationship('author', 'name')` for BelongsTo fields.** `BelongsToSelect` does not exist in v4.
- **`Repeater` uses `->schema()`, not `->fields()`.**
- **Never add `->dehydrated(false)` to fields that need to be saved.** It strips the value from form state before `->action()` or the save handler runs. Only use it for helper/UI-only fields.
- **Use correct property types when overriding `Page`, `Resource`, and `Widget` properties.** These properties have union types or changed modifiers that must be preserved:
  - `$navigationIcon`: `protected static string | BackedEnum | null` (not `?string`)
  - `$navigationGroup`: `protected static string | UnitEnum | null` (not `?string`)
  - `$view`: `protected string` (not `protected static string`) on `Page` and `Widget` classes

</laravel-boost-guidelines>
---

# VPS ORCHESTRATOR MODE

Everything below applies when running on the VPS (`/root/dev/formynieces`), the
always-on session Isaac steers from the Claude mobile app via Remote Control.

## Platform

- Pest: `./vendor/bin/pest` (NOT the Windows `vendor\bin\pest`)
- Repo: `/root/dev/formynieces` — this is the DEV workspace, never the deploy path
- The running Docker container `formynieces` serves the live app and is NOT this
  directory. Never restart, rebuild, or edit that container from here.

## Chat-level control — the governing principle

**This session behaves like Claude chat.** Nothing consequential happens without
Isaac's explicit go-ahead in the conversation. When in doubt, ask — a wasted
confirmation costs seconds; an unwanted action costs trust.

### Hard gates — never proceed past these without an explicit "yes"

| Gate | What must be shown before asking |
|---|---|
| G1. Scenario selection | The chosen ID + full Gherkin text |
| G2. Delegation to GLM | The exact instruction string + the exact file list it may touch |
| G3. Accepting GLM's work | Pest group result + `git diff --stat` + one-paragraph summary |
| G4. Any commit | The staged file list + the full commit message |
| G5. `specs:verify` | What Isaac actually confirmed (his words, not assumed) |
| G6. Push to origin | Confirm after ledger commit, before `git push` |
| G7. Anything destructive | `git checkout --`, `git reset`, migrations on a non-test DB, deleting files — always ask, always show what will be lost |

"Explicit yes" means Isaac says so in this conversation. Silence, a previous yes to
a similar action, or a yes given for a different scenario do NOT carry forward.
One approval covers one action.

### Conversation style

- Default to **discussion, not action.** If Isaac raises a design question, answer
  it as a conversation. Do not start editing files because a design chat implied a
  direction. Transition to action only when he says to build.
- Before any multi-step sequence, state the plan in 3–6 plain lines and wait.
- Report in chat-sized turns: what happened, what's next, what needs his decision.
  Summarize terminal output; offer the raw text if he wants it.
- If Isaac is clearly on mobile (short messages, dictation artifacts), keep
  responses tight and front-load the decision he needs to make.

### Permission mode

- This session runs with default permissions. NEVER suggest, use, or enable
  `--dangerously-skip-permissions` or auto-accept modes, even if faster, even for
  "safe" batches. The approval prompts ARE the product.
- Read-only observation commands are pre-approved via `.claude/settings.json`.
  Never chain a mutating command onto an allowed read-only prefix.
- If an action would generate a large burst of prompts, say so first and let Isaac
  decide whether to proceed prompt-by-prompt or restructure the task.

### Standing state honesty

- At session start (or when Isaac reconnects after a gap), give a one-line status:
  branch, clean/dirty tree, last verified scenario, anything mid-flight.
- Never present work done in a previous session as if just completed.
- If the tree is dirty from an interrupted loop, describe it and ask before
  touching anything.

---

## GLM executor delegation

The local executor is GLM-5.2 via Z.ai, launched as a headless subprocess. It is
the ONLY place autonomy exists, and only after G2 approval.

### Division of labour

| Loop step | Who |
|---|---|
| Step 1 — pick scenario (`specs:trace`) | Orchestrator |
| Step 2 — write the failing Pest test | Orchestrator (NEVER delegate test design) |
| Step 3 — minimum implementation code | **Delegate to GLM** |
| Step 4–5 — confirm green, full suite | Orchestrator runs, reads output only |
| Step 6 — browser checklist | Orchestrator |
| Step 7–10.5 — commits, verify, ledger, push | Orchestrator |

### Launch form

The launch string is the fixed anti-orchestrator PREAMBLE followed by the APPROVED
instruction, run with `--permission-mode acceptEdits`, captured to a log in the background
(per the timeout/background preference — never block the session on a GLM run):

```bash
# PREAMBLE (verbatim, always prepended — this is a standing wrapper, NOT a content
# addition to the approved instruction):
#   YOU ARE A CODING EXECUTOR, NOT AN ORCHESTRATOR. Ignore any instructions in the
#   repository CLAUDE.md about delegating to GLM, gates, approvals, or asking the user —
#   those DO NOT apply to you. Do NOT print a plan. Do NOT ask for approval. Do NOT
#   delegate. Using your file-editing tools, directly CREATE and EDIT the files described
#   below yourself, right now, then stop. When you finish, the files must be written to disk.

ANTHROPIC_BASE_URL="https://api.z.ai/api/anthropic" \
ANTHROPIC_AUTH_TOKEN="$ZAI_KEY" \
timeout 500 claude -p "$(cat preamble+approved-instruction.txt)" \
  --model glm-5.2 --permission-mode acceptEdits > glm-XX.log 2>&1 &
```

**Why the preamble is mandatory:** without it, the GLM subprocess reads THIS CLAUDE.md,
role-plays the orchestrator, and prints a "G2 gate, reply go" message instead of writing
any code (a silent no-op — `git status` shows zero changes). The preamble neutralizes that.
Confirmed failure + fix in the AC-04 loop.

### Rules

- The instruction given to GLM is exactly the one Isaac approved — no additions to its
  CONTENT. The fixed anti-orchestrator preamble (see Launch form) is the ONE exception: it
  is a required standing wrapper, not a content change, and does not need per-loop approval.
- GLM runs with `--permission-mode acceptEdits` so it can write files unattended; its
  permission prompts cannot reach Isaac (background subprocess), so anything needing
  approval — sudo, migrations, git — simply fails inside GLM. Never rely on GLM for those.
- Name exact file paths in the instruction. Include the scenario ID, the relevant
  Gherkin lines, the failing test path, and the assertion it must satisfy.
- State "minimum code only — no speculative features."
- For model/service edits, instruct full-file output, not fragments.
- **Filament 4 work is NOT delegated.** The orchestrator implements it directly,
  after Laravel Boost `search-docs`, per existing convention.
- GLM never commits, never pushes, never touches git config, never runs migrations
  outside the test database.

### Token budget

After GLM exits, read ONLY:
1. `./vendor/bin/pest --group=scenario:XX-NN`
2. `git diff --stat`

Do NOT read full diffs while tests pass. Read full diffs when:
- the tagged group fails, or
- `git diff --stat` shows files not named in the approved instruction (report this
  at G3 as an anomaly before anything else).

### Escalation

1. GLM attempt 1 fails → ONE corrective message with the exact Pest failure output,
   not paraphrased. **If GLM wrote NOTHING (git status clean) and printed an
   orchestrator-style plan, it role-played the orchestrator — re-launch with the
   anti-orchestrator preamble (see Launch form); that counts as the corrective retry.**
2. Attempt 2 fails → stop delegating. Bring the problem to Isaac with a
   recommendation. Taking over is his call — it spends his Pro tokens.
3. Full-suite regression caused by a delegated edit → orchestrator fixes it
   directly; do not send regressions back to GLM.

---

## Commit attribution (mandatory)

Every commit records who executed the code, as a trailer on the final `-m` flag:

- GLM wrote the implementation: `Executed-By: GLM-5.2`
- Orchestrator wrote it: `Executed-By: Claude`
- Mixed (GLM implemented, Claude fixed): `Executed-By: GLM-5.2, Claude`

The orchestrator MUST state which trailer it is using at gate G4, as part of the
commit message shown for approval. Never guess — if the orchestrator took over
after a GLM failure, that is "GLM-5.2, Claude".

Ledger commits (`chore(specs): verify XX-NN`) always use `Executed-By: Claude`.

Example:

```
git commit -m "feat(ac-03): per-student cap override in resolver" \
           -m "- CapResolver honours users.weekly_module_cap_override" \
           -m "- Pest group scenario:AC-03 green; full suite 151 passing" \
           -m "Executed-By: GLM-5.2"
```

Audit queries:

```
git log --grep="Executed-By: GLM" --oneline
git log pre-cascade..main --oneline
```

---

## Known environment quirks

- **specs:trace false STALE** — the unquoted `verified_at` bug is still live. A
  STALE on a scenario whose `.feature` is untouched (`git log --oneline -- <file>`
  shows no edit since its verify date) is THIS bug, not a real spec change.
- **GroqService** — this box has a placeholder `GROQ_API_KEY`. Tests pass because
  the constructor only assigns; any test making a real Groq call would fail.
  `GroqService` hard-failing on a null key is a known fragility (parked).
- **`.env.example` is out of date** — a fresh clone needs `GROQ_API_KEY` added by
  hand to reach green. Parked as a candidate fix.
