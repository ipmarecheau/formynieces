@props([
    'title' => 'SmoothSeas — SEA exam prep, sailed with a turtle named Smooth',
    'description' => 'SmoothSeas is the SEA companion for Caribbean primary-school children: Math, ELA and Writing in one adaptive daily plan, with weekly reports for parents and Smooth the turtle at the helm.',
    'image' => null,
    'type' => 'website',
])
@php
    $canonical = url()->current();
    $ogImage = $image ?? asset('reels/child-reel-poster.png');
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="SmoothSeas">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:locale" content="en_TT">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">

{{-- Optional structured data / extra head tags passed by the page --}}
{{ $slot }}
