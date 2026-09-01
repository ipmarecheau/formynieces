<?php

namespace App\Http\Controllers;

use App\Mail\ChildAccountCreated;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChildSetupController extends Controller
{
    private const STUDENT_EMAIL_DOMAIN = '@smoothseas.org';

    public function create(): View|RedirectResponse
    {
        if ($guard = $this->requirePhoneVerification()) {
            return $guard;
        }

        return view('guardian.child-setup', [
            'strandsBySubject' => SyllabusModule::strandsBySubject(),
        ]);
    }

    /**
     * A guardian who registered with a phone must verify it before onboarding.
     * Users with no phone on file (pre-existing accounts) are unaffected.
     */
    private function requirePhoneVerification(): ?RedirectResponse
    {
        if (auth()->user()->needsPhoneVerification()) {
            return redirect()->route('verification.notice');
        }

        return null;
    }

    public function store(Request $request): RedirectResponse
    {
        if ($guard = $this->requirePhoneVerification()) {
            return $guard;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_sea_year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'known_weak_areas' => ['nullable', 'array'],
            'known_weak_areas.*' => ['string', 'max:100'],
        ]);

        // The login username AND a strong password are generated for the guardian,
        // not chosen. The password is stored hashed for auth and, encrypted, so the
        // guardian can reveal/reset it later from the Parent Portal.
        $username = $this->generateUsername($validated['name']);
        $email = $username.self::STUDENT_EMAIL_DOMAIN;
        $password = self::generatePassword();

        $student = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'student',
            'parent_id' => $request->user()->id,
            'target_sea_year' => $validated['target_sea_year'],
            'known_weak_areas' => $validated['known_weak_areas'] ?? [],
            // onboarding_completed_at intentionally left null.
        ]);
        $student->child_password_enc = $password; // encrypted cast; guardian can reveal/reset later
        $student->save();

        // Email the guardian the login ID as a record (never the password).
        try {
            Mail::to($request->user()->email)->send(
                new ChildAccountCreated($student, $email, route('guardian.children')),
            );
        } catch (\Throwable $e) {
            report($e); // never block account creation on mail delivery
        }

        // Show the child's login details to the guardian once.
        return redirect()
            ->route('child.setup')
            ->with('student_credentials', [
                'name' => $student->name,
                'username' => $username,
                'login_id' => $email,
                'password' => $password,
            ]);
    }

    /**
     * A strong but child-typeable password: two capitalised ocean words + two
     * digits (e.g. "CoralTide48") — good entropy from the pairing, easy to key in.
     */
    public static function generatePassword(): string
    {
        $words = ['Coral', 'Tide', 'Reef', 'Wave', 'Turtle', 'Anchor', 'Pearl', 'Shell', 'Sail', 'Harbour', 'Compass', 'Lagoon', 'Dolphin', 'Breeze', 'Island', 'Voyage'];
        $a = $words[array_rand($words)];
        do {
            $b = $words[array_rand($words)];
        } while ($b === $a);

        return $a.$b.random_int(10, 99);
    }

    /**
     * Build the child's login username from her name: the first initial plus the
     * first four letters of the last name (lowercased, ASCII, a–z0–9 only). If a
     * matching account already exists, a numeric suffix (1, 2, 3, …) is appended
     * until it is unique — since the username becomes a unique login id.
     */
    private function generateUsername(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? (string) end($parts) : $first;

        $base = preg_replace(
            '/[^a-z0-9]/',
            '',
            Str::lower(Str::ascii(mb_substr($first, 0, 1).mb_substr($last, 0, 4)))
        );

        if ($base === '') {
            $base = 'student';
        }

        $username = $base;
        $suffix = 1;
        while (User::where('email', $username.self::STUDENT_EMAIL_DOMAIN)->exists()) {
            $username = $base.$suffix;
            $suffix++;
        }

        return $username;
    }
}
