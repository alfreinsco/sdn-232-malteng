<?php

use App\Livewire\Concerns\WithDataTable;
use App\Models\{Kelas, Siswa, TahunAjaran};
use App\Services\PenempatanSiswaKelas;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    use WithDataTable;

    #[Url(as: 'tahun')]
    public string $tahunId = '';

    #[Url(as: 'kelas')]
    public string $kelasId = '';

    public function mount(): void
    {
        $this->tahunId = (string) ($this->tahunId ?: TahunAjaran::aktif()->value('id') ?? '');
        $this->initializeDataTable();
    }

    public function updatedTahunId(): void
    {
        $this->kelasId = '';
        $this->datasetChanged();
    }

    public function updatedKelasId(): void
    {
        $this->clearSelection();
    }

    public function resetTableFilters(): void
    {
        $this->tahunId = (string) (TahunAjaran::aktif()->value('id') ?? '');
        $this->kelasId = '';
    }

    protected function tableSortableColumns(): array
    {
        return ['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'status'];
    }

    protected function tableColumns(): array
    {
        return [
            ['id' => 'nis', 'label' => 'NIS', 'sortable' => 'nis'],
            ['id' => 'nisn', 'label' => 'NISN', 'sortable' => 'nisn'],
            ['id' => 'nama_lengkap', 'label' => 'Nama Siswa', 'sortable' => 'nama_lengkap'],
            ['id' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'sortable' => 'jenis_kelamin'],
            ['id' => 'status', 'label' => 'Status', 'sortable' => 'status', 'hideable' => false],
        ];
    }

    private function tableQuery()
    {
        return Siswa::query()
            ->where('status', 'aktif')
            ->when($this->search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('nama_lengkap', 'like', '%'.$this->search.'%')
                ->orWhere('nis', 'like', '%'.$this->search.'%')
                ->orWhere('nisn', 'like', '%'.$this->search.'%')));
    }

    public function save(): void
    {
        $kelas = Kelas::query()
            ->where('tahun_ajaran_id', $this->tahunId)
            ->find($this->kelasId);

        if (! $kelas) {
            $this->dispatch('notify', type: 'error', message: 'Pilih kelas yang sesuai dengan tahun ajaran.');

            return;
        }

        $total = (clone $this->tableQuery())->count();

        if ($this->selectedCount($total) < 1) {
            $this->dispatch('notify', type: 'error', message: 'Pilih minimal satu siswa.');

            return;
        }

        $service = app(PenempatanSiswaKelas::class);
        $processed = 0;
        $this->applySelection($this->tableQuery())
            ->orderBy('id')
            ->chunkById(100, function ($students) use ($kelas, $service, &$processed): void {
                foreach ($students as $student) {
                    $service->handle($student, $kelas);
                    $processed++;
                }
            });

        $this->clearSelection();
        $this->dispatch('notify', type: 'success', message: $processed.' siswa berhasil ditempatkan ke kelas '.$kelas->nama.'.');
    }

    public function with(): array
    {
        $error = null;

        try {
            $query = $this->tableQuery();
            $total = (clone $query)->count();
            $students = $this->sort !== ''
                ? $query->orderBy($this->sort, $this->direction)->paginate($this->perPage)
                : $query->orderBy('nama_lengkap')->paginate($this->perPage);
        } catch (\Throwable $exception) {
            report($exception);
            $error = 'Terjadi kesalahan saat mengambil data siswa.';
            $total = 0;
            $students = Siswa::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return [
            'tahun' => TahunAjaran::orderByDesc('nama')->get(),
            'kelas' => Kelas::where('tahun_ajaran_id', $this->tahunId)->orderBy('nama')->get(),
            'siswa' => $students,
            'datasetTotal' => $total,
            'tableColumns' => $this->tableColumns(),
            'visibleColumnIds' => $this->validatedVisibleColumns(),
            'tableError' => $error,
        ];
    }
};
?>

<div>
    <div class="mb-6">
        <p class="text-sm font-semibold text-sky-700">Akademik</p>
        <h1 class="page-title">Penempatan Siswa Kelas</h1>
        <p class="page-subtitle">Pilih siswa pada seluruh halaman lalu tempatkan ke satu kelas pada tahun ajaran yang sama.</p>
    </div>

    <div class="mb-4 grid gap-3 lg:grid-cols-[minmax(18rem,1fr)_auto]">
        <div class="card p-4">
            <label class="form-label" for="placement-search">Pencarian Utama</label>
            <input id="placement-search" type="search" wire:model.live.debounce.400ms="search" class="form-input" placeholder="Cari nama, NIS, atau NISN...">
        </div>
        <div class="card flex items-end gap-3 p-4">
            <button type="button" wire:click="resetTableState" class="btn-secondary">Reset Filter</button>
            <x-data-table.column-toggle :columns="$tableColumns" :visible="$visibleColumnIds" />
        </div>
    </div>

    <div class="filters card mb-4 grid gap-3 p-4 md:grid-cols-2">
        <div><label class="form-label">Tahun Ajaran</label><x-searchable-select model="tahunId" :value="$tahunId" :options="$tahun->pluck('nama','id')->all()" placeholder="Pilih tahun ajaran" search-placeholder="Cari tahun ajaran..." /></div>
        <div><label class="form-label">Kelas Tujuan</label><x-searchable-select model="kelasId" :value="$kelasId" :options="$kelas->pluck('nama','id')->all()" placeholder="Pilih kelas" search-placeholder="Cari kelas..." /></div>
    </div>

    <x-data-table.bulk-toolbar :count="$this->selectedCount($datasetTotal)">
        <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-primary">Tempatkan ke Kelas</button>
    </x-data-table.bulk-toolbar>

    <div class="table-shell">
        <div class="table-scroll"><table class="data-table"><thead><tr>
            <th class="table-select-cell sticky left-0 z-20 w-14"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" wire:click="toggleSelectAllDataset" @checked($datasetTotal>0&&$this->selectedCount($datasetTotal)===$datasetTotal) x-data x-effect="$el.indeterminate={{ $this->selectedCount($datasetTotal)>0&&$this->selectedCount($datasetTotal)<$datasetTotal?'true':'false' }}" aria-label="Pilih seluruh siswa hasil pencarian"></th>
            @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<th><button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="inline-flex min-h-11 items-center gap-1">{{ $column['label'] }} <span aria-hidden="true">{{ $sort===$column['sortable']?($direction==='asc'?'↑':'↓'):'↕' }}</span></button></th>@endforeach
            <th class="table-action-cell sticky right-0 z-20">Aksi</th>
        </tr></thead><tbody>
            @foreach($siswa as $student)@php $selected=$this->isRowSelected($student->id);@endphp<tr class="{{ $selected?'is-selected':'' }}" wire:key="placement-row-{{$student->id}}" wire:loading.remove><td class="table-select-cell sticky left-0 z-10"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" @checked($selected) wire:click="toggleRowSelection({{$student->id}})" aria-label="Pilih {{$student->nama_lengkap}}"></td>
            @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<td>@if($column['id']==='status')<span class="badge-active">Aktif</span>@elseif($column['id']==='jenis_kelamin'){{ ucfirst($student->jenis_kelamin) }}@else{{ data_get($student,$column['id'])??'-' }}@endif</td>@endforeach
            <td class="table-action-cell sticky right-0 z-10"><button type="button" wire:click="toggleRowSelection({{$student->id}})" class="btn-secondary">{{ $selected?'Batalkan':'Pilih' }}</button></td></tr>@endforeach
            <x-data-table.states :columns="count($visibleColumnIds)+2" :empty="$siswa->isEmpty()" :filtered="filled($search)" :error="$tableError" />
        </tbody></table></div>
        <x-data-table.pagination :paginator="$siswa" :per-page="$perPage" />
    </div>
</div>
