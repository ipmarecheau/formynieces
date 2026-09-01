<?php

namespace App\Livewire;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * The guardian Account area: profile editing, a display-only billing summary
 * (plan + first bill date + invoice history — no charges are taken at the free
 * launch), and account deletion. Sits in the Guardian Bridge portal (GA-01..06).
 */
class GuardianAccount extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|lowercase|email|max:255')]
    public string $email = '';

    #[Validate('required|string|regex:/^\+[1-9]\d{7,14}$/')]
    public string $phone = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $delete_password = '';

    public ?string $flash = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    /**
     * Update the guardian's profile. Changing the email address re-triggers email
     * verification so the new address is confirmed.
     */
    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
        ], [
            'phone.regex' => 'Enter the phone number in full international format, e.g. +18685551234.',
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
            $this->flash = 'Profile saved. Confirm your new email — we just sent a link.';

            return;
        }

        $this->flash = 'Profile saved.';
    }

    /**
     * Change the guardian's password after confirming the current one.
     */
    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'That does not match your current password.',
        ]);

        Auth::user()->update(['password' => Hash::make($validated['password'])]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->flash = 'Password updated.';
    }

    /**
     * Permanently delete the account after re-entering the password.
     */
    public function deleteAccount(): RedirectResponse|Redirector
    {
        $this->validate([
            'delete_password' => ['required', 'current_password'],
        ], [
            'delete_password.current_password' => 'That does not match your current password.',
        ]);

        $user = Auth::user();

        Auth::logout();

        // Remove every linked child, then the guardian herself.
        $user->students()->delete();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    #[Layout('layouts.guardian')]
    public function render()
    {
        $user = Auth::user();

        return view('livewire.guardian-account', [
            'user' => $user,
            'invoices' => $user->invoices()->latest('issued_at')->get(),
        ]);
    }
}
