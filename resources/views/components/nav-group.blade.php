@props(['label'])
<section aria-label="{{ $label }}">
    <div class="mb-2 flex items-center gap-3 px-3">
        <p class="shrink-0 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $label }}</p>
        <span class="h-px flex-1 bg-slate-100" aria-hidden="true"></span>
    </div>
    <div class="space-y-1">{{ $slot }}</div>
</section>
