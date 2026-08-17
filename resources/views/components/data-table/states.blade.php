@props(['columns', 'empty', 'filtered' => false, 'error' => null])

<tr class="hidden" wire:loading.class.remove="hidden">
    <td colspan="{{ $columns }}" class="p-0">
        <div class="space-y-3 p-5" aria-label="Memuat data">
            @foreach (range(1, 5) as $row)
                <div class="h-10 animate-pulse rounded-lg bg-slate-100"></div>
            @endforeach
        </div>
    </td>
</tr>
@if ($error)
    <tr wire:loading.remove><td colspan="{{ $columns }}" class="py-14 text-center">
        <p class="font-semibold text-rose-700">Data tidak dapat dimuat.</p><p class="mt-1 text-sm text-slate-500">{{ $error }}</p>
        <button type="button" wire:click="$refresh" class="btn-secondary mt-4">Coba Lagi</button>
    </td></tr>
@elseif ($empty)
    <tr wire:loading.remove><td colspan="{{ $columns }}" class="py-14 text-center text-slate-500">
        <p>{{ $filtered ? 'Tidak ada data yang sesuai dengan pencarian atau filter saat ini.' : 'Belum ada data.' }}</p>
        @if ($filtered)<button type="button" wire:click="resetTableState" class="btn-secondary mt-4">Reset Filter</button>@endif
    </td></tr>
@endif
