// Livewire v3 ships and starts its own Alpine (via @livewireScripts), and exposes
// it globally as window.Alpine. Importing and starting a second Alpine here made two
// instances fight over any tree that mixes x-data with Livewire directives
// (wire:click / $wire / @entangle) — silently breaking those bindings. The tour
// components are exactly such trees, which is why "Skip the tour" did nothing and the
// lesson tour stayed stuck after re-login. Rely on Livewire's Alpine; register any
// Alpine plugins here via a `livewire:init` listener + Alpine.plugin(), never a second start.
