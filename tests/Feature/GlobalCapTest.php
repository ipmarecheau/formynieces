<?php

use App\Models\Setting;
use App\Models\User;
use App\Services\Pacing\CapResolver;
use App\Filament\Pages\PacingSettings;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('sets the global cap via Filament and a student with no override uses it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['weekly_module_cap_override' => null]);

    actingAs($admin);

    Livewire::test(PacingSettings::class)
        ->fillForm(['weekly_module_cap' => 8])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::query()->where('key', 'weekly_module_cap')->value('value'))->toBe('8');
    expect(app(CapResolver::class)->resolve($student))->toBe(8);
})->group('scenario:AC-01');

it('prefers a student override over the global cap', function () {
    Setting::query()->updateOrCreate(['key' => 'weekly_module_cap'], ['value' => '8']);
    $student = User::factory()->create(['weekly_module_cap_override' => 3]);

    expect(app(CapResolver::class)->resolve($student))->toBe(3);
})->group('scenario:AC-01');

it('falls back to 5 when no override and no global setting exist', function () {
    Setting::query()->where('key', 'weekly_module_cap')->delete();
    $student = User::factory()->create(['weekly_module_cap_override' => null]);

    expect(app(CapResolver::class)->resolve($student))->toBe(5);
})->group('scenario:AC-01');
