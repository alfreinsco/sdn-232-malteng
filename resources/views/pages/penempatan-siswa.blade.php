<?php

use App\Livewire\Concerns\WithDataTable;
use App\Models\{Kelas, Siswa, SiswaKelas, TahunAjaran};
use App\Services\PenempatanSiswaKelas;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    use WithDataTable;

    #[Url(as: 'tahun', keep: true)]
    public string $tahunId = '';

    #[Url(as: 'kelas')]
    public string $kelasId = '';

    #[Url(as: 'status_kelas')]
    public string $statusKelas = '';

    public function mount(): void
    {
        if (! request()->query->has('tahun')) {
            $this->tahunId = (string) (TahunAjaran::aktif()->value('id') ?? '');
        }
        if (! in_array($this->statusKelas, ['', 'sudah', 'belum'], true)) {
            $this->statusKelas = '';
        }
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

    public function updatedStatusKelas(): void
    {
        $this->datasetChanged();
    }

    public function resetTableFilters(): void
    {
        $this->tahunId = (string) (TahunAjaran::aktif()->value('id') ?? '');
        $this->kelasId = '';
        $this->statusKelas = '';
    }

    protected function tableSortableColumns(): array
    {
        return ['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'kelas_nama', 'status'];
    }

    protected function tableColumns(): array
    {
        return [
            ['id' => 'nis', 'label' => 'NIS', 'sortable' => 'nis'],
            ['id' => 'nisn', 'label' => 'NISN', 'sortable' => 'nisn'],
            ['id' => 'nama_lengkap', 'label' => 'Nama Siswa', 'sortable' => 'nama_lengkap'],
            ['id' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'sortable' => 'jenis_kelamin'],
            ['id' => 'kelas_nama', 'label' => 'Kelas Saat Ini', 'sortable' => 'kelas_nama'],
            ['id' => 'status', 'label' => 'Status', 'sortable' => 'status', 'hideable' => false],
        ];
    }

    private function tableQuery()
    {
        $placements = SiswaKelas::query()
            ->select('siswa_kelas.siswa_id', 'siswa_kelas.kelas_id')
            ->join('kelas as kelas_tahun', 'kelas_tahun.id', '=', 'siswa_kelas.kelas_id')
            ->where('siswa_kelas.status', 'aktif')
            ->when($this->tahunId, fn ($query) => $query->where('kelas_tahun.tahun_ajaran_id', $this->tahunId), fn ($query) => $query->whereRaw('1 = 0'));

        return Siswa::query()
            ->select('siswa.*', 'kelas_penempatan.nama as kelas_nama', 'kelas_penempatan.id as kelas_penempatan_id')
            ->leftJoinSub($placements, 'penempatan_tahun', fn ($join) => $join->on('penempatan_tahun.siswa_id', '=', 'siswa.id'))
            ->leftJoin('kelas as kelas_penempatan', 'kelas_penempatan.id', '=', 'penempatan_tahun.kelas_id')
            ->where('siswa.status', 'aktif')
            ->when($this->statusKelas === 'sudah', fn ($query) => $query->whereNotNull('kelas_penempatan.id'))
            ->when($this->statusKelas === 'belum', fn ($query) => $query->whereNull('kelas_penempatan.id'))
            ->when($this->search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('siswa.nama_lengkap', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nis', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nisn', 'like', '%'.$this->search.'%')
                ->orWhere('kelas_penempatan.nama', 'like', '%'.$this->search.'%')));
    }

    private function applyTableSort($query)
    {
        $columns = [
            'nis' => 'siswa.nis',
            'nisn' => 'siswa.nisn',
            'nama_lengkap' => 'siswa.nama_lengkap',
            'jenis_kelamin' => 'siswa.jenis_kelamin',
            'kelas_nama' => 'kelas_penempatan.nama',
            'status' => 'siswa.status',
        ];

        return $this->sort !== ''
            ? $query->orderBy($columns[$this->sort], $this->direction)
            : $query->orderBy('siswa.nama_lengkap');
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
            ->orderBy('siswa.id')
            ->chunkById(100, function ($students) use ($kelas, $service, &$processed): void {
                foreach ($students as $student) {
                    $service->handle($student, $kelas);
                    $processed++;
                }
            }, 'siswa.id', 'id');

        $this->clearSelection();
        $this->dispatch('notify', type: 'success', message: $processed.' siswa berhasil ditempatkan ke kelas '.$kelas->nama.'.');
    }

    public function with(): array
    {
        $error = null;

        try {
            $query = $this->tableQuery();
            $total = (clone $query)->count();
            $students = $this->applyTableSort($query)->paginate($this->perPage);
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

    <div class="filters card mb-4 grid gap-3 p-4 md:grid-cols-3">
        <div><label class="form-label">Tahun Ajaran</label><x-searchable-select model="tahunId" :value="$tahunId" :options="$tahun->pluck('nama','id')->all()" placeholder="Pilih tahun ajaran" search-placeholder="Cari tahun ajaran..." /></div>
        <div><label class="form-label">Kelas Tujuan</label><x-searchable-select model="kelasId" :value="$kelasId" :options="$kelas->pluck('nama','id')->all()" placeholder="Pilih kelas" search-placeholder="Cari kelas..." /></div>
        <div><label class="form-label">Status Penempatan</label><x-searchable-select model="statusKelas" :value="$statusKelas" :options="['sudah'=>'Sudah memiliki kelas','belum'=>'Belum memiliki kelas']" placeholder="Semua siswa" search-placeholder="Cari status penempatan..." /></div>
    </div>

    <x-data-table.bulk-toolbar :count="$this->selectedCount($datasetTotal)">
        <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-primary">Tempatkan ke Kelas</button>
    </x-data-table.bulk-toolbar>

    <div class="table-shell">
        <x-data-table.mobile-hint />
        <div class="table-scroll"><table class="data-table"><thead><tr>
            <th class="table-select-cell sticky left-0 z-20 w-14"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" wire:click="toggleSelectAllDataset" @checked($datasetTotal>0&&$this->selectedCount($datasetTotal)===$datasetTotal) x-data x-effect="$el.indeterminate={{ $this->selectedCount($datasetTotal)>0&&$this->selectedCount($datasetTotal)<$datasetTotal?'true':'false' }}" aria-label="Pilih seluruh siswa hasil pencarian"></th>
            @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<th><button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="inline-flex min-h-11 items-center gap-1">{{ $column['label'] }} <span aria-hidden="true">{{ $sort===$column['sortable']?($direction==='asc'?'↑':'↓'):'↕' }}</span></button></th>@endforeach
            <th class="table-action-cell sticky right-0 z-20">Aksi</th>
        </tr></thead><tbody>
            @foreach($siswa as $student)@php $selected=$this->isRowSelected($student->id);@endphp<tr class="{{ $selected?'is-selected':($student->kelas_penempatan_id?'is-assigned':'') }}" wire:key="placement-row-{{$student->id}}" wire:loading.remove><td class="table-select-cell sticky left-0 z-10"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" @checked($selected) wire:click="toggleRowSelection({{$student->id}})" aria-label="Pilih {{$student->nama_lengkap}}"></td>
            @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<td>@if($column['id']==='status')<span class="badge-active">Aktif</span>@elseif($column['id']==='jenis_kelamin'){{ $student->jenis_kelamin?ucfirst($student->jenis_kelamin):'-' }}@elseif($column['id']==='kelas_nama')@if($student->kelas_nama)<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">{{ $student->kelas_nama }}</span>@else<span class="text-slate-400">Belum memiliki kelas</span>@endif @else{{ data_get($student,$column['id'])??'-' }}@endif</td>@endforeach
            <td class="table-action-cell sticky right-0 z-10"><button type="button" wire:click="toggleRowSelection({{$student->id}})" class="btn-secondary table-action-button" aria-label="{{ $selected?'Batalkan pilihan':'Pilih' }} {{$student->nama_lengkap}}" title="{{ $selected?'Batalkan':'Pilih' }}"><svg viewBox="0 0 24 24" class="size-5 sm:hidden" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">@if($selected)<path d="M6 6l12 12M18 6 6 18"/>@else<path d="m5 12 4 4L19 6"/>@endif</svg><span class="hidden sm:inline">{{ $selected?'Batalkan':'Pilih' }}</span></button></td></tr>@endforeach
            <x-data-table.states :columns="count($visibleColumnIds)+2" :empty="$siswa->isEmpty()" :filtered="filled($search)||filled($statusKelas)" :error="$tableError" />
        </tbody></table></div>
        <x-data-table.pagination :paginator="$siswa" :per-page="$perPage" />
    </div>
</div>
