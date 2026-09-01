<?php

use App\Livewire\GuardianAccount;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\VerifyEmailWithCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function guardian(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'guardian',
        'age_attested_at' => now(),
        'phone' => '+18685551234',
    ], $attributes));
}

it('renders the account page with profile, billing and history', function () {
    $guardian = guardian();

    $this->actingAs($guardian)
        ->get(route('guardian.account'))
        ->assertOk()
        ->assertSee('Profile')
        ->assertSee('Billing')
        ->assertSee('Billing history')
        ->assertSee('Delete account');
})->group('scenario:GA-01');

it('updates the guardian profile', function () {
    $guardian = guardian(['name' => 'Old Name']);

    Livewire::actingAs($guardian)
        ->test(GuardianAccount::class)
        ->set('name', 'New Name')
        ->set('phone', '+18681112222')
        ->call('updateProfile')
        ->assertHasNoErrors()
        ->assertSet('flash', 'Profile saved.');

    expect($guardian->fresh())
        ->name->toBe('New Name')
        ->phone->toBe('+18681112222');
})->group('scenario:GA-02');

it('re-verifies the email when it is changed', function () {
    Notification::fake();
    $guardian = guardian(['email_verified_at' => now()]);

    Livewire::actingAs($guardian)
        ->test(GuardianAccount::class)
        ->set('email', 'new-address@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($guardian->fresh())
        ->email->toBe('new-address@example.com')
        ->email_verified_at->toBeNull();

    Notification::assertSentTo($guardian->fresh(), VerifyEmailWithCode::class);
})->group('scenario:GA-03');

it('rejects a duplicate email', function () {
    guardian(['email' => 'taken@example.com']);
    $guardian = guardian();

    Livewire::actingAs($guardian)
        ->test(GuardianAccount::class)
        ->set('email', 'taken@example.com')
        ->call('updateProfile')
        ->assertHasErrors(['email']);
})->group('scenario:GA-03');

it('changes the password with the correct current password', function () {
    $guardian = guardian();

    Livewire::actingAs($guardian)
        ->test(GuardianAccount::class)
        ->set('current_password', 'password')
        ->set('password', 'NewStrongPass123!')
        ->set('password_confirmation', 'NewStrongPass123!')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertSet('flash', 'Password updated.');

    expect(Hash::check('NewStrongPass123!', $guardian->fresh()->password))->toBeTrue();
})->group('scenario:GA-04');

it('rejects a wrong current password', function () {
    $guardian = guardian();

    Livewire::actingAs($guardian)
        ->test(GuardianAccount::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'NewStrongPass123!')
        ->set('password_confirmation', 'NewStrongPass123!')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);
})->group('scenario:GA-04');

it('shows an empty state when there are no invoices', function () {
    $guardian = guardian();

    $this->actingAs($guardian)
        ->get(route('guardian.account'))
        ->assertSee('No invoices yet');
})->group('scenario:GA-05');

it('lists the guardian invoices with amount and status', function () {
    $guardian = guardian(['first_bill_at' => now()->addMonth()]);
    Invoice::factory()->for($guardian)->create([
        'number' => 'INV-24680',
        'amount_cents' => 900,
        'status' => 'paid',
    ]);

    $this->actingAs($guardian)
        ->get(route('guardian.account'))
        ->assertSee('INV-24680')
        ->assertSee('$9.00')
        ->assertSee('Paid')
        ->assertDontSee('No invoices yet');
})->group('scenario:GA-05');

it('deletes the account and its linked children', function () {
    $guardian = guardian();
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    Livewire::actingAs($guardian)
        ->test(GuardianAccount::class)
        ->set('delete_password', 'password')
        ->call('deleteAccount')
        ->assertRedirect('/');

    expect(User::find($guardian->id))->toBeNull();
    expect(User::find($child->id))->toBeNull();
    $this->assertGuest();
})->group('scenario:GA-06');
