<?php

namespace App\Livewire;

use App\Notifications\CoParentInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The Family area of the Parent Portal: manage each child's details (essential
 * fields plus optional metadata — birth year, current school) and invite the
 * other parent as a co-parent (GF-01..06).
 */
class GuardianFamily extends Component
{
    /**
     * Editable per-child fields, keyed by student id.
     *
     * @var array<int, array{name: string, birth_year: ?string, current_school: ?string, target_sea_year: ?string}>
     */
    public array $children = [];

    public string $coName = '';

    public string $coEmail = '';

    public string $coRelationship = '';

    public ?string $flash = null;

    public function mount(): void
    {
        foreach (Auth::user()->students()->orderBy('name')->get() as $child) {
            $this->children[$child->id] = [
                'name' => $child->name,
                'birth_year' => $child->birth_year ? (string) $child->birth_year : '',
                'current_school' => $child->current_school ?? '',
                'target_sea_year' => $child->target_sea_year ? (string) $child->target_sea_year : '',
            ];
        }
    }

    /**
     * Save one child's details. Only the name is mandatory; the rest is optional
     * metadata a guardian can fill in over time.
     */
    public function saveChild(int $childId): void
    {
        $child = Auth::user()->students()->findOrFail($childId);

        $validated = $this->validate([
            "children.{$childId}.name" => ['required', 'string', 'max:255'],
            "children.{$childId}.birth_year" => ['nullable', 'integer', 'min:2005', 'max:'.now()->year],
            "children.{$childId}.current_school" => ['nullable', 'string', 'max:255'],
            "children.{$childId}.target_sea_year" => ['nullable', 'integer', 'min:2025', 'max:2035'],
        ]);

        $data = $validated['children'][$childId];

        $child->update([
            'name' => $data['name'],
            'birth_year' => $data['birth_year'] !== '' ? $data['birth_year'] : null,
            'current_school' => $data['current_school'] !== '' ? $data['current_school'] : null,
            'target_sea_year' => $data['target_sea_year'] !== '' ? $data['target_sea_year'] : null,
        ]);

        $this->flash = "{$child->name}'s details saved.";
    }

    /**
     * Invite the other parent. Name and email are essential; the relationship is
     * optional. An invitation email is sent so they can join with that address.
     */
    public function addCoParent(): void
    {
        $guardian = Auth::user();

        $validated = $this->validate([
            'coName' => ['required', 'string', 'max:255'],
            'coEmail' => ['required', 'email', 'max:255'],
            'coRelationship' => ['nullable', 'string', 'max:60'],
        ]);

        if ($guardian->coParents()->where('email', $validated['coEmail'])->exists()) {
            $this->addError('coEmail', 'You have already invited this person.');

            return;
        }

        $coParent = $guardian->coParents()->create([
            'name' => $validated['coName'],
            'email' => $validated['coEmail'],
            'relationship' => $validated['coRelationship'] ?: null,
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        Notification::route('mail', $coParent->email)
            ->notify(new CoParentInvitation($guardian, $coParent->name));

        $this->reset('coName', 'coEmail', 'coRelationship');
        $this->flash = "Invitation sent to {$coParent->name}.";
    }

    public function removeCoParent(int $coParentId): void
    {
        Auth::user()->coParents()->whereKey($coParentId)->delete();
        $this->flash = 'Co-parent removed.';
    }

    #[Layout('layouts.guardian')]
    public function render()
    {
        $guardian = Auth::user();

        return view('livewire.guardian-family', [
            'students' => $guardian->students()->orderBy('name')->get(),
            'coParents' => $guardian->coParents()->latest()->get(),
        ]);
    }
}
