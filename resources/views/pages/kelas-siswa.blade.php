<?php

use App\Livewire\Concerns\WithDataTable;
use App\Models\{Kelas, Siswa};
use App\Services\{KeluarkanSiswaKelas, PenempatanSiswaKelas};
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use WithDataTable;

    public Kelas $kelas;
    public bool $showAdd = false;
    public string $siswaId = '';

    public function mount(Kelas $kelas): void
    {
        $this->kelas = $kelas->load(['tahunAjaran', 'waliKelas']);
        $this->initializeDataTable();
    }

    protected function tableSortableColumns(): array
    {
        return ['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'tanggal_lahir'];
    }

    protected function tableColumns(): array
    {
        return [
            ['id' => 'nis', 'label' => 'NIS', 'sortable' => 'nis'],
            ['id' => 'nisn', 'label' => 'NISN', 'sortable' => 'nisn'],
            ['id' => 'nama_lengkap', 'label' => 'Nama Siswa', 'sortable' => 'nama_lengkap'],
            ['id' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'sortable' => 'jenis_kelamin'],
            ['id' => 'tempat_tanggal_lahir', 'label' => 'Tempat, Tanggal Lahir', 'sortable' => 'tanggal_lahir'],
        ];
    }

    private function tableQuery()
    {
        return Siswa::query()
            ->select('siswa.*')
            ->join('siswa_kelas', 'siswa_kelas.siswa_id', '=', 'siswa.id')
            ->where('siswa_kelas.kelas_id', $this->kelas->id)
            ->where('siswa_kelas.status', 'aktif')
            ->when($this->search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('siswa.nama_lengkap', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nis', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nisn', 'like', '%'.$this->search.'%')));
    }

    private function applySort($query)
    {
        $columns = [
            'nis' => 'siswa.nis',
            'nisn' => 'siswa.nisn',
            'nama_lengkap' => 'siswa.nama_lengkap',
            'jenis_kelamin' => 'siswa.jenis_kelamin',
            'tanggal_lahir' => 'siswa.tanggal_lahir',
        ];

        return $this->sort !== ''
            ? $query->orderBy($columns[$this->sort], $this->direction)
            : $query->orderBy('siswa.nama_lengkap');
    }

    public function openAdd(): void
    {
        abort_unless(auth()->user()->can('kelas.update'), 403);
        $this->resetValidation();
        $this->siswaId = '';
        $this->showAdd = true;
    }

    public function addStudent(): void
    {
        abort_unless(auth()->user()->can('kelas.update'), 403);
        $this->validate([
            'siswaId' => ['required', Rule::exists('siswa', 'id')->where('status', 'aktif')],
        ], [
            'siswaId.required' => 'Pilih siswa yang akan ditambahkan.',
            'siswaId.exists' => 'Siswa tidak tersedia atau sudah tidak aktif.',
        ]);

        $student = Siswa::findOrFail($this->siswaId);
        app(PenempatanSiswaKelas::class)->handle($student, $this->kelas);

        $this->showAdd = false;
        $this->siswaId = '';
        $this->dispatch('notify', type: 'success', message: $student->nama_lengkap.' berhasil ditambahkan ke kelas '.$this->kelas->nama.'.');
    }

    public function removeStudent(int $studentId): void
    {
        abort_unless(auth()->user()->can('kelas.update'), 403);
        $student = $this->tableQuery()->findOrFail($studentId);

        try {
            app(KeluarkanSiswaKelas::class)->handle($student, $this->kelas);
        } catch (ValidationException $exception) {
            $this->dispatch('notify', type: 'error', message: collect($exception->errors())->flatten()->first());

            return;
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$studentId]));
        $this->dispatch('notify', type: 'success', message: $student->nama_lengkap.' berhasil dikeluarkan dari kelas. Data siswa tidak dihapus.');
    }

    public function bulkRemove(): void
    {
        abort_unless(auth()->user()->can('kelas.update'), 403);
        $query = $this->applySelection($this->tableQuery());
        $count = (clone $query)->count();

        if ($count < 1) {
            return;
        }

        $service = app(KeluarkanSiswaKelas::class);
        $query->orderBy('siswa.id')->chunkById(100, function ($students) use ($service): void {
            foreach ($students as $student) {
                $service->handle($student, $this->kelas);
            }
        }, 'siswa.id', 'id');

        $this->clearSelection();
        $this->dispatch('notify', type: 'success', message: $count.' siswa berhasil dikeluarkan dari kelas. Data siswa tidak dihapus.');
    }

    public function resetTableFilters(): void
    {
        // Halaman ini hanya memiliki pencarian global.
    }

    private function availableStudentOptions(): array
    {
        return Siswa::query()
            ->where('status', 'aktif')
            ->whereDoesntHave('penempatanKelas', fn ($query) => $query
                ->where('kelas_id', $this->kelas->id)
                ->where('status', 'aktif'))
            ->orderBy('nama_lengkap')
            ->get()
            ->mapWithKeys(fn ($student) => [
                $student->id => ($student->nis ? $student->nis.' · ' : '').$student->nama_lengkap,
            ])->all();
    }

    public function with(): array
    {
        $error = null;

        try {
            $query = $this->tableQuery();
            $total = (clone $query)->count();
            $students = $this->applySort($query)->paginate($this->perPage);
        } catch (\Throwable $exception) {
            report($exception);
            $error = 'Terjadi kesalahan saat mengambil anggota kelas.';
            $total = 0;
            $students = Siswa::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return [
            'students' => $students,
            'datasetTotal' => $total,
            'tableColumns' => $this->tableColumns(),
            'visibleColumnIds' => $this->validatedVisibleColumns(),
            'tableError' => $error,
            'studentOptions' => $this->showAdd ? $this->availableStudentOptions() : [],
            'canManage' => auth()->user()->can('kelas.update'),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <nav class="mb-2 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('kelas.index') }}" wire:navigate class="hover:text-sky-700">Kelas</a>
                <span aria-hidden="true"> / </span><span>{{ $kelas->nama }}</span>
            </nav>
            <p class="text-sm font-semibold text-sky-700">Anggota Kelas</p>
            <h1 class="page-title">Siswa Kelas {{ $kelas->nama }}</h1>
            <p class="page-subtitle">{{ $kelas->tahunAjaran->nama }} · Tingkat {{ $kelas->tingkat }} · Wali kelas: {{ $kelas->waliKelas?->nama_lengkap ?? 'Belum ditentukan' }}</p>
        </div>
        @if($canManage)
            <button type="button" wire:click="openAdd" class="btn-primary">Tambah Siswa</button>
        @endif
    </div>

    <div class="mb-4 grid gap-3 lg:grid-cols-[minmax(18rem,1fr)_auto]">
        <div class="card p-4">
            <label class="form-label" for="class-student-search">Pencarian Utama</label>
            <div class="relative">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-3 size-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="class-student-search" type="search" wire:model.live.debounce.400ms="search" class="form-input pl-10" placeholder="Cari nama, NIS, atau NISN...">
            </div>
        </div>
        <div class="card flex items-end gap-3 p-4">
            <button type="button" wire:click="resetTableState" class="btn-secondary">Reset Filter</button>
            <x-data-table.column-toggle :columns="$tableColumns" :visible="$visibleColumnIds" />
        </div>
    </div>

    @if($canManage)
        <x-data-table.bulk-toolbar :count="$this->selectedCount($datasetTotal)">
            <button type="button" wire:click="bulkRemove" wire:confirm="Keluarkan {{ $this->selectedCount($datasetTotal) }} siswa dari kelas ini? Data siswa tidak akan dihapus." class="btn-secondary text-rose-700">Hapus dari Kelas</button>
        </x-data-table.bulk-toolbar>
    @endif

    <div class="table-shell">
        <x-data-table.mobile-hint />
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr>
                    @if($canManage)
                        <th class="table-select-cell sticky left-0 z-20 w-14"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" wire:click="toggleSelectAllDataset" @checked($datasetTotal>0&&$this->selectedCount($datasetTotal)===$datasetTotal) x-data x-effect="$el.indeterminate={{ $this->selectedCount($datasetTotal)>0&&$this->selectedCount($datasetTotal)<$datasetTotal?'true':'false' }}" aria-label="Pilih seluruh siswa dalam kelas"></th>
                    @endif
                    @foreach($tableColumns as $column)
                        @continue(!in_array($column['id'],$visibleColumnIds,true))
                        <th><button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="inline-flex min-h-11 items-center gap-1" aria-label="Urutkan berdasarkan {{ $column['label'] }}">{{ $column['label'] }} <span aria-hidden="true">{{ $sort===$column['sortable']?($direction==='asc'?'↑':'↓'):'↕' }}</span></button></th>
                    @endforeach
                    @if($canManage)<th class="table-action-cell sticky right-0 z-20">Aksi</th>@endif
                </tr></thead>
                <tbody>
                    @foreach($students as $student)
                        @php $selected=$this->isRowSelected($student->id); @endphp
                        <tr class="{{ $selected?'is-selected':'' }}" wire:key="class-student-{{ $student->id }}" wire:loading.remove>
                            @if($canManage)<td class="table-select-cell sticky left-0 z-10"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" @checked($selected) wire:click="toggleRowSelection({{ $student->id }})" aria-label="Pilih {{ $student->nama_lengkap }}"></td>@endif
                            @foreach($tableColumns as $column)
                                @continue(!in_array($column['id'],$visibleColumnIds,true))
                                <td>
                                    @if($column['id']==='jenis_kelamin'){{ $student->jenis_kelamin ? ucfirst($student->jenis_kelamin) : '-' }}
                                    @elseif($column['id']==='tempat_tanggal_lahir'){{ collect([$student->tempat_lahir,$student->tanggal_lahir?->translatedFormat('d F Y')])->filter()->implode(', ') ?: '-' }}
                                    @else{{ data_get($student,$column['id']) ?: '-' }}@endif
                                </td>
                            @endforeach
                            @if($canManage)<td class="table-action-cell sticky right-0 z-10"><button type="button" wire:click="removeStudent({{ $student->id }})" wire:confirm="Keluarkan {{ $student->nama_lengkap }} dari kelas ini? Data siswa tidak akan dihapus." class="btn-secondary table-action-button whitespace-nowrap text-rose-700" aria-label="Hapus {{ $student->nama_lengkap }} dari kelas" title="Hapus dari Kelas"><svg viewBox="0 0 24 24" class="size-5 sm:hidden" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg><span class="hidden sm:inline">Hapus dari Kelas</span></button></td>@endif
                        </tr>
                    @endforeach
                    <x-data-table.states :columns="count($visibleColumnIds)+($canManage?2:0)" :empty="$students->isEmpty()" :filtered="filled($search)" :error="$tableError" />
                </tbody>
            </table>
        </div>
        <x-data-table.pagination :paginator="$students" :per-page="$perPage" />
    </div>

    @if($showAdd)
        <div class="fixed inset-0 z-[80] grid place-items-center overflow-y-auto bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="add-class-student-title">
            <form wire:submit="addStudent" class="card w-full max-w-xl">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div><h2 id="add-class-student-title" class="text-lg font-semibold">Tambah Siswa ke Kelas</h2><p class="mt-1 text-sm text-slate-500">{{ $kelas->nama }} · {{ $kelas->tahunAjaran->nama }}</p></div>
                    <button type="button" wire:click="$set('showAdd',false)" class="grid size-11 place-items-center rounded-lg hover:bg-slate-100" aria-label="Tutup">&times;</button>
                </div>
                <div class="space-y-4 p-5">
                    <div><label class="form-label">Siswa <span>*</span></label><x-searchable-select model="siswaId" :value="$siswaId" :options="$studentOptions" placeholder="Pilih siswa..." search-placeholder="Cari nama atau NIS siswa..." />@error('siswaId')<p class="form-error" role="alert">{{ $message }}</p>@enderror</div>
                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Jika siswa masih aktif di kelas lain pada tahun ajaran yang sama, siswa akan dipindahkan ke kelas ini secara otomatis.</p>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 p-5"><button type="button" wire:click="$set('showAdd',false)" class="btn-secondary">Batal</button><button type="submit" wire:loading.attr="disabled" class="btn-primary"><span wire:loading.remove>Tambahkan</span><span wire:loading>Menambahkan...</span></button></div>
            </form>
        </div>
    @endif
</div>
