<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes an unauthenticated health probe', function () {
    $this->getJson('/api/mobile/ping')
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('service', 'smoothseas-mobile-api');
});

it('logs a parent in and returns a parent-scoped token', function () {
    $parent = User::factory()->create(['role' => 'guardian', 'password' => bcrypt('secret123')]);

    $this->postJson('/api/mobile/login', [
        'email' => $parent->email,
        'password' => 'secret123',
        'device_name' => 'Test Phone',
    ])
        ->assertOk()
        ->assertJsonPath('default_experience', 'parent')
        ->assertJsonPath('user.id', $parent->id)
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'role'], 'default_experience']);
});

it('logs a child in with a child-scoped experience', function () {
    $child = User::factory()->create(['role' => 'student', 'password' => bcrypt('secret123')]);

    $this->postJson('/api/mobile/login', [
        'email' => $child->email,
        'password' => 'secret123',
        'device_name' => 'Test Phone',
    ])
        ->assertOk()
        ->assertJsonPath('default_experience', 'child');
});

it('rejects bad credentials', function () {
    $parent = User::factory()->create(['role' => 'guardian', 'password' => bcrypt('secret123')]);

    $this->postJson('/api/mobile/login', [
        'email' => $parent->email,
        'password' => 'wrong',
        'device_name' => 'Test Phone',
    ])->assertStatus(422);
});

it('requires a token for /me', function () {
    $this->getJson('/api/mobile/me')->assertUnauthorized();
});

it('returns the parent and their children from /me', function () {
    $parent = User::factory()->create(['role' => 'guardian']);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $parent->id, 'target_sea_year' => now()->year]);

    $token = $parent->createToken('t', ['parent'])->plainTextToken;

    $this->withToken($token)->getJson('/api/mobile/me')
        ->assertOk()
        ->assertJsonPath('user.id', $parent->id)
        ->assertJsonPath('children.0.id', $child->id)
        ->assertJsonPath('children.0.standard', 'Standard 5');
});

it('revokes the token on logout', function () {
    $parent = User::factory()->create(['role' => 'guardian']);
    $token = $parent->createToken('t', ['parent'])->plainTextToken;

    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($token)->postJson('/api/mobile/logout')->assertOk();
    $this->assertDatabaseCount('personal_access_tokens', 0);
});
