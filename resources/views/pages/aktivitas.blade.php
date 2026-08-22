<?php

use App\Models\Aktivitas;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    public int $perPage = 25;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedType(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->perPage = max(10, min(100, $this->perPage)); $this->resetPage(); }
    public function goToTablePage(int $page, int $lastPage): void { $this->setPage(max(1, min($lastPage, $page))); }

    public function with(): array
    {
        $user = auth()->user();
        $activities = Aktivitas::with('user')
            ->when(! $user->hasRole(['admin', 'kepala_sekolah']), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('description', 'like', '%'.$this->search.'%')
                ->orWhere('actor_name', 'like', '%'.$this->search.'%')
                ->orWhere('role', 'like', '%'.$this->search.'%')))
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->latest()
            ->paginate($this->perPage);

        return compact('activities');
    }
};
?>

<div class="space-y-6">
    <div>
        <p class="text-sm font-semibold text-sky-700">Audit Sistem</p>
        <h1 class="page-title">{{ auth()->user()->hasRole(['admin','kepala_sekolah']) ? 'Aktivitas Pengguna' : 'Aktivitas Saya' }}</h1>
        <p class="page-subtitle">Riwayat login, perubahan data penting, aksi massal, dan ekspor laporan tercatat otomatis.</p>
    </div>

    <section class="card filters grid gap-4 p-4 sm:grid-cols-[minmax(0,1fr)_14rem]">
        <div><label class="form-label" for="activity-search">Cari Aktivitas</label><input id="activity-search" type="search" wire:model.live.debounce.350ms="search" class="form-input" placeholder="Cari pengguna, peran, atau aktivitas..."></div>
        <div><label class="form-label" for="activity-type">Jenis Aktivitas</label><select id="activity-type" wire:model.live="type" class="form-input"><option value="">Semua jenis</option><option value="login">Login</option><option value="logout">Logout</option><option value="tambah">Tambah data</option><option value="ubah">Ubah data</option><option value="hapus">Hapus data</option><option value="ekspor">Ekspor laporan</option><option value="aksi">Aksi lainnya</option></select></div>
    </section>

    <section class="table-shell">
        <x-data-table.mobile-hint />
        <div class="table-scroll"><table class="data-table"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Peran</th><th>Jenis</th><th>Aktivitas</th><th>Alamat IP</th></tr></thead><tbody>
            @forelse($activities as $activity)
                <tr wire:key="activity-{{ $activity->id }}"><td><span class="font-semibold text-slate-800">{{ $activity->created_at->translatedFormat('d M Y') }}</span><span class="mt-1 block text-xs text-slate-500">{{ $activity->created_at->format('H:i:s') }}</span></td><td><span class="font-semibold text-slate-900">{{ $activity->actor_name ?? 'Tidak dikenal' }}</span>@if($activity->user?->username)<span class="mt-1 block text-xs text-slate-500">{{ '@'.$activity->user->username }}</span>@endif</td><td><span class="badge-active">{{ str($activity->role ?? 'publik')->replace('_',' ')->title() }}</span></td><td><span class="activity-type activity-type-{{ $activity->type }}">{{ str($activity->type)->replace('_',' ')->title() }}</span></td><td><p class="max-w-xl font-medium text-slate-800">{{ $activity->description }}</p>@if($activity->route)<span class="mt-1 block text-xs text-slate-500">Route: {{ $activity->route }}</span>@endif</td><td class="font-mono text-xs text-slate-500">{{ $activity->ip_address ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="6" class="py-14 text-center text-slate-500">Belum ada aktivitas yang sesuai dengan filter.</td></tr>
            @endforelse
        </tbody></table></div>
        <x-data-table.pagination :paginator="$activities" :per-page="$perPage" />
    </section>
</div>
