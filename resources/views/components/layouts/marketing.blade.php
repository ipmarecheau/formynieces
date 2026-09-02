<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Free SEA Mock + Placement Report — SmoothSeas' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --ink: #12222e; --ink-soft: #40566a; --paper: #fbf8f2; --paper-2: #ffffff;
            --line: #e7ddcd; --teal: #0d7d8c; --teal-deep: #0a5c68; --amber: #f2a900; --amber-tint: #fdf1d6;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--paper); color: var(--ink); font-family: 'Nunito', system-ui, sans-serif; line-height: 1.55; }
    </style>
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
