@props(['href' => null, 'tagline' => null])

{{-- SmoothSeas wordmark: a ship + gradient "Seas". Wrap in a link when $href is set. --}}
@php $tag = $href ? 'a' : 'span'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'ss-logo']) }}>
    <span class="ss-logo-mark">⛵</span>
    <span>
        <span class="ss-logo-word">Smooth<b>Seas</b></span>
        @if($tagline)
            <span style="display:block; font-family: var(--ss-font-body); font-size: 13px; color: var(--ss-muted); margin-top: 2px;">{{ $tagline }}</span>
        @endif
    </span>
</{{ $tag }}>
