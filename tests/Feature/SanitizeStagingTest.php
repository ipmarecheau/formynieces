<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('refuses to run outside the staging environment', function () {
    app()['env'] = 'production';
    $real = User::factory()->create(['role' => 'guardian', 'is_test' => false, 'email' => 'real@example.com']);

    $this->artisan('staging:sanitize')->assertExitCode(1);

    expect($real->fresh()->email)->toBe('real@example.com'); // untouched
});

it('anonymises real accounts but leaves QA (is_test) accounts logged-in-able on staging', function () {
    app()['env'] = 'staging';

    $real = User::factory()->create(['role' => 'guardian', 'is_test' => false, 'email' => 'parent@real.com', 'phone' => '+1868000', 'name' => 'Real Parent']);
    $qa = User::factory()->create(['role' => 'guardian', 'is_test' => true, 'email' => 'qa-parent@qa.smoothseas.org', 'name' => 'QA Parent']);

    $this->artisan('staging:sanitize', ['--password' => 'staging-pw'])->assertExitCode(0);

    $real->refresh();
    expect($real->email)->toBe("user{$real->id}@staging.invalid");
    expect($real->name)->toBe("Parent {$real->id}");
    expect($real->phone)->toBeNull();
    expect(Hash::check('staging-pw', $real->password))->toBeTrue();

    $qa->refresh();
    expect($qa->email)->toBe('qa-parent@qa.smoothseas.org'); // QA creds preserved
    expect($qa->name)->toBe('QA Parent');
});
