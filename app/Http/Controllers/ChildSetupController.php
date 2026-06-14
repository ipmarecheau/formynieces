<?php

namespace App\Http\Controllers;

use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ChildSetupController extends Controller
{
    private const STUDENT_EMAIL_DOMAIN = '@students.formynieces.com';

    public function create(): View
    {
        return view('guardian.child-setup', [
            'strandsBySubject' => SyllabusModule::strandsBySubject(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50'],
            'password' => ['required', 'confirmed', 'min:8'],
            'target_sea_year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'known_weak_areas' => ['nullable', 'array'],
            'known_weak_areas.*' => ['string', 'max:100'],
        ]);

        $email = strtolower($validated['username']) . self::STUDENT_EMAIL_DOMAIN;

        // Username must be globally unique because it becomes a unique email.
        if (User::where('email', $email)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'That username is already taken. Please choose another.']);
        }

        $student = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'parent_id' => $request->user()->id,
            'target_sea_year' => $validated['target_sea_year'],
            'known_weak_areas' => $validated['known_weak_areas'] ?? [],
            // onboarding_completed_at intentionally left null.
        ]);

        // Show the child's login details to the guardian once.
        return redirect()
            ->route('child.setup')
            ->with('student_credentials', [
                'name' => $student->name,
                'username' => $validated['username'],
                'login_id' => $email,
                'password' => $validated['password'],
            ]);
    }
}