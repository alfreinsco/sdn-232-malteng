@props(['paginator', 'perPage'])

@if ($paginator->total() > 0)
    <div class="flex flex-col gap-3 border-t border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="text-sm text-slate-500">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
        <div class="flex flex-wrap items-center gap-2">
            <div class="w-36">
                <x-searchable-select model="perPage" :value="$perPage"
                    :options="[10 => '10 data', 25 => '25 data', 50 => '50 data', 100 => '100 data']"
                    placeholder="Per halaman" search-placeholder="Cari atau ketik angka..." :allow-custom="true"
                    :min="1" :max="250" aria-label="Jumlah data per halaman" />
            </div>
            <button type="button" wire:click="gotoPage(1)" class="btn-secondary size-11 px-0"
                @disabled($paginator->onFirstPage()) aria-label="Halaman pertama" title="Halaman pertama">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 5v14M18 6l-6 6 6 6" /></svg>
            </button>
            <button type="button" wire:click="previousPage" class="btn-secondary size-11 px-0"
                @disabled($paginator->onFirstPage()) aria-label="Halaman sebelumnya" title="Halaman sebelumnya">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
            </button>
            <div class="flex items-center gap-2 text-sm text-slate-600" x-data="{ page: {{ $paginator->currentPage() }} }">
                <label class="sr-only" for="table-page-input">Nomor halaman</label>
                <input id="table-page-input" type="number" min="1" max="{{ $paginator->lastPage() }}"
                    x-model.number="page" @change="$wire.goToTablePage(page, {{ $paginator->lastPage() }}); page = Math.max(1, Math.min({{ $paginator->lastPage() }}, page || 1))"
                    @keydown.enter.prevent="$el.blur()" class="form-input w-20 text-center" aria-label="Nomor halaman aktif">
                <span>/ {{ $paginator->lastPage() }}</span>
            </div>
            <button type="button" wire:click="nextPage" class="btn-secondary size-11 px-0"
                @disabled(!$paginator->hasMorePages()) aria-label="Halaman berikutnya" title="Halaman berikutnya">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
            </button>
            <button type="button" wire:click="gotoPage({{ $paginator->lastPage() }})" class="btn-secondary size-11 px-0"
                @disabled(!$paginator->hasMorePages()) aria-label="Halaman terakhir" title="Halaman terakhir">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 6 6-6 6M18 5v14" /></svg>
            </button>
        </div>
    </div>
@endif
