<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * DeviceHandoffController — the shared-device "hand this device to my child" flow.
 *
 * On a device the guardian is signed in on, they cannot simply open the student sign-in
 * (the guest-only /go bounces them back to their dashboard). Instead they hand the device
 * over: a short countdown interstitial explains what's happening, then the guardian is
 * signed out and dropped on the kid-branded sign-in with the child's login ID prefilled —
 * the child only has to type their password. The password is never prefilled or put in a URL.
 */
class DeviceHandoffController extends Controller
{
    /**
     * Show the countdown interstitial for handing this device to the child.
     */
    public function show(User $child): View
    {
        $this->authorizeGuardianOf($child);

        return view('auth.device-handoff', ['child' => $child]);
    }

    /**
     * Sign the guardian out and send them to the student sign-in with the login ID prefilled.
     */
    public function commit(Request $request, User $child): RedirectResponse
    {
        $this->authorizeGuardianOf($child);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('student.login', ['login' => $child->email]);
    }

    /**
     * Only the child's own guardian may hand a device over to that child.
     */
    private function authorizeGuardianOf(User $child): void
    {
        abort_unless(
            $child->isStudent() && $child->parent_id === Auth::id(),
            403,
        );
    }
}
