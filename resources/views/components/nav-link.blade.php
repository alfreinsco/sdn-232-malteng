@props(['route', 'label', 'icon' => 'circle'])

@php
    $active = request()->routeIs($route)
        || ($route === 'kelas.index' && request()->routeIs('kelas.siswa.*'));
@endphp

<a href="{{ route($route) }}" wire:navigate @click="sidebarOpen = false"
    @if($active) aria-current="page" @endif
    class="group relative flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 font-medium transition duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 {{ $active ? 'bg-sky-50 text-sky-800 ring-1 ring-inset ring-sky-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
    @if($active)<span class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-sky-500" aria-hidden="true"></span>@endif
    <span class="grid size-8 shrink-0 place-items-center rounded-lg transition duration-200 {{ $active ? 'bg-white text-sky-600' : 'text-slate-400 group-hover:bg-white group-hover:text-sky-600' }}">
        <x-nav-icon :name="$icon" />
    </span>
    <span class="min-w-0 flex-1 truncate">{{ $label }}</span>
    @if($active)<span class="size-1.5 shrink-0 rounded-full bg-sky-500" aria-hidden="true"></span>@endif
</a>
