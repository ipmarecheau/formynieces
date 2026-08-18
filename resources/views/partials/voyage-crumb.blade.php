{{-- SH-08 — a consistent way home from any child-layer screen. --}}
<a href="{{ route('student.voyage') }}" class="voyage-crumb">⛵ Back to my Voyage</a>
<style>
    .voyage-crumb {
        align-self: flex-start;
        display: inline-flex; align-items: center; gap: .4rem;
        color: #fcd34d; text-decoration: none; font-weight: 800; font-size: .95rem;
        margin: 0 0 1rem;
    }
    .voyage-crumb:hover { text-decoration: underline; color: #fde68a; }
</style>
