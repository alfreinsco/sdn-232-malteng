@props(['columns', 'visible'])

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" @click="open = !open" class="btn-secondary size-11 px-0"
        aria-label="Atur visibilitas kolom" title="Atur visibilitas kolom" :aria-expanded="open">
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5h16v14H4zM9 5v14M15 5v14" /></svg>
    </button>
    <div x-show="open" x-transition x-cloak class="absolute right-0 z-40 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
        <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Tampilkan Kolom</p>
        @foreach ($columns as $column)
            @if ($column['hideable'] ?? true)
                <label class="flex min-h-10 cursor-pointer items-center gap-3 rounded-lg px-3 text-sm hover:bg-slate-50">
                    <input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600"
                        @checked(in_array($column['id'], $visible, true))
                        wire:click="toggleColumn('{{ $column['id'] }}')">
                    <span>{{ $column['label'] }}</span>
                </label>
            @endif
        @endforeach
    </div>
</div>
