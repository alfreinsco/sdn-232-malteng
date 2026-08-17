@props(['route','label','icon'=>null])
<a href="{{ route($route) }}" wire:navigate class="flex min-h-11 items-center rounded-xl px-3 font-medium transition {{ request()->routeIs($route) ? 'bg-sky-50 text-sky-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">{{ $label }}</a>
