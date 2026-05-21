@props(['href', 'icon', 'active' => false])

@php
    $active = $active || request()->routeIs(trim($href, '/') . '*') || rtrim(url()->current(), '/') === rtrim($href, '/');
    $classes = $active
        ? 'flex items-center gap-3 px-4 py-2.5 rounded-lg bg-brand-canopy text-white font-medium shadow-brand'
        : 'flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-soft hover:bg-brand-canopy/5 hover:text-brand-canopy transition';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <x-icon :name="$icon" class="w-5 h-5 flex-shrink-0" />
    <span class="text-sm">{{ $slot }}</span>
</a>
