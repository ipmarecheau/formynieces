<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Parent Portal — manage a guardian's children's logins: reveal or reset the
 * generated password on demand (the modern, safer alternative to scheduled
 * rotation). Passwords are stored encrypted (not hashed) for this reveal.
 */
class GuardianChildrenController extends Controller
{
    public function index(): View
    {
        return view('guardian.children', [
            'children' => auth()->user()->students()->orderBy('name')->get(),
        ]);
    }

    public function reveal(User $child): RedirectResponse
    {
        $this->authorizeChild($child);

        return back()->with('revealed', [
            'id' => $child->id,
            'password' => $child->child_password_enc ?: null,
        ]);
    }

    public function reset(User $child): RedirectResponse
    {
        $this->authorizeChild($child);

        $password = ChildSetupController::generatePassword();
        $child->password = $password;          // 'hashed' cast hashes it
        $child->child_password_enc = $password; // 'encrypted' cast stores a recoverable copy
        $child->save();

        return back()->with('revealed', ['id' => $child->id, 'password' => $password])
            ->with('reset_done', $child->name);
    }

    private function authorizeChild(User $child): void
    {
        abort_unless($child->isStudent() && $child->parent_id === auth()->id(), 403);
    }
}
