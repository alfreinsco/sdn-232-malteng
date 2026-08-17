@props([
    'model',
    'options' => [],
    'value' => '',
    'placeholder' => 'Pilih...',
    'searchPlaceholder' => 'Cari opsi...',
    'allowCustom' => false,
    'min' => 1,
    'max' => 250,
    'ariaLabel' => null,
])

@php
    $normalizedOptions = collect($options)->map(fn ($label, $optionValue) => [
        'value' => (string) $optionValue,
        'label' => (string) $label,
    ])->values()->all();
@endphp

<div class="relative" wire:key="searchable-select-{{ str($model)->slug() }}-{{ md5((string) $value) }}"
    x-data="{
        open: false,
        query: '',
        selected: @js((string) $value),
        active: 0,
        options: @js($normalizedOptions),
        get filtered() {
            const term = this.query.toLocaleLowerCase('id');
            return this.options.filter(option => option.label.toLocaleLowerCase('id').includes(term));
        },
        get selectedLabel() {
            return this.options.find(option => option.value === this.selected)?.label ?? (this.selected || @js($placeholder));
        },
        choose(option) {
            this.selected = option.value;
            this.open = false;
            this.query = '';
            this.active = 0;
            $wire.set(@js($model), option.value, true);
        },
        clear() {
            this.selected = '';
            this.open = false;
            this.query = '';
            $wire.set(@js($model), '', true);
        },
        commitCustom() {
            if (! @js($allowCustom)) return;
            const number = Math.max(@js($min), Math.min(@js($max), Number.parseInt(this.query, 10) || @js($min)));
            this.selected = String(number);
            this.open = false;
            this.query = '';
            $wire.set(@js($model), number, true);
        },
        move(step) {
            if (!this.filtered.length) return;
            this.active = (this.active + step + this.filtered.length) % this.filtered.length;
        },
        chooseActive() {
            if (this.filtered[this.active]) this.choose(this.filtered[this.active]);
            else this.commitCustom();
        }
    }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" class="form-input flex items-center justify-between gap-2 text-left"
        @click="open = !open; if (open) $nextTick(() => $refs.search.focus())"
        :aria-expanded="open" aria-haspopup="listbox" aria-label="{{ $ariaLabel ?? $placeholder }}">
        <span class="truncate" x-text="selectedLabel"></span>
        <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
    </button>
    <div x-show="open" x-transition x-cloak
        class="absolute z-50 mt-2 w-full min-w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
        <input x-ref="search" x-model="query" type="search" class="form-input mb-2"
            placeholder="{{ $searchPlaceholder }}" autocomplete="off"
            @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="chooseActive()">
        <div class="max-h-56 overflow-y-auto" role="listbox">
            <button type="button" class="flex min-h-10 w-full items-center justify-between rounded-lg px-3 text-left text-sm hover:bg-slate-50"
                @click="clear()">
                <span>{{ $placeholder }}</span><span x-show="selected === ''" aria-hidden="true">✓</span>
            </button>
            <template x-for="(option, index) in filtered" :key="option.value">
                <button type="button" class="flex min-h-10 w-full items-center justify-between rounded-lg px-3 text-left text-sm"
                    :class="active === index ? 'bg-sky-50 text-sky-700' : 'hover:bg-slate-50'"
                    @mouseenter="active = index" @click="choose(option)" role="option"
                    :aria-selected="selected === option.value">
                    <span x-text="option.label"></span><span x-show="selected === option.value" aria-hidden="true">✓</span>
                </button>
            </template>
            <p x-show="filtered.length === 0 && !@js($allowCustom)" class="px-3 py-4 text-center text-sm text-slate-500">Opsi tidak ditemukan.</p>
            @if ($allowCustom)
                <button x-show="query !== ''" type="button" @click="commitCustom()"
                    class="mt-1 min-h-10 w-full rounded-lg px-3 text-left text-sm font-medium text-sky-700 hover:bg-sky-50">
                    Gunakan <span x-text="query"></span> data per halaman
                </button>
            @endif
        </div>
    </div>
</div>
