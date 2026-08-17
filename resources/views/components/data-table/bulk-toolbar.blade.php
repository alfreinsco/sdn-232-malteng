@props(['count'])

@if ($count > 0)
    <div class="sticky top-20 z-30 mb-3 flex flex-wrap items-center gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-950">
        <strong>{{ number_format($count) }} data dipilih</strong>
        <div class="ml-auto flex flex-wrap items-center gap-2">{{ $slot }}</div>
        <button type="button" wire:click="clearSelection" class="btn-secondary">Bersihkan Pilihan</button>
    </div>
@endif
