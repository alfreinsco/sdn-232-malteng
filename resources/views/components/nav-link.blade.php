@props(['route', 'label', 'icon' => 'circle'])

@php
    $active = request()->routeIs($route)
        || ($route === 'kelas.index' && request()->routeIs('kelas.siswa.*'));
@endphp

<a href="{{ route($route) }}" wire:navigate @click="sidebarOpen = false"
    @if($active) aria-current="page" @endif
    class="sidebar-link {{ $active ? 'active' : '' }}">
    <span><x-nav-icon :name="$icon" /></span><b>{{ $label }}</b>
</a>
