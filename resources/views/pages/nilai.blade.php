<?php

use App\Livewire\Concerns\WithDataTable;
use App\Models\{MataPelajaran, NilaiTugas, Pengajaran, Semester, Siswa, TahunAjaran};
use App\Services\PenyimpananNilaiMassal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    use WithDataTable;

    #[Url(as: 'pengajaran')]
    public string $pengajaranId = '';

    #[Url]
    public int $bulan = 0;

    #[Url(as: 'tahun_ajaran')]
    public string $tahunId = '';

    #[Url]
    public string $semesterId = '';

    #[Url(as: 'mata_pelajaran')]
    public string $mapelId = '';

    public array $nilai = [];

    public function mount(): void
    {
        $this->bulan = max(1, min(12, (int) ($this->bulan ?: now()->month)));
        $this->initializeDataTable();

        if (! auth()->user()->hasRole('siswa')) {
            $this->loadNilai();
        }
    }

    public function updatedPengajaranId(): void
    {
        $this->datasetChanged();
        $this->loadNilai();
    }

    public function updatedBulan(mixed $value): void
    {
        $this->bulan = max(1, min(12, (int) $value));
        $this->datasetChanged();

        if (! auth()->user()->hasRole('siswa')) {
            $this->loadNilai();
        }
    }

    public function updatedTahunId(): void
    {
        $this->semesterId = '';
        $this->datasetChanged();
    }

    public function updatedSemesterId(): void
    {
        $this->datasetChanged();
    }

    public function updatedMapelId(): void
    {
        $this->datasetChanged();
    }

    public function resetTableFilters(): void
    {
        $this->bulan = now()->month;
        $this->tahunId = '';
        $this->semesterId = '';
        $this->mapelId = '';

        if (! auth()->user()->hasRole('siswa')) {
            $this->pengajaranId = '';
            $this->nilai = [];
        }
    }

    protected function tableSortableColumns(): array
    {
        return auth()->user()->hasRole('siswa')
            ? ['mata_pelajaran', 'guru', 'm1', 'm2', 'm3', 'm4', 'rata_rata']
            : ['nama_lengkap', 'm1', 'm2', 'm3', 'm4', 'rata_rata'];
    }

    protected function tableColumns(): array
    {
        if (auth()->user()->hasRole('siswa')) {
            return [
                ['id' => 'mata_pelajaran', 'label' => 'Mata Pelajaran', 'sortable' => 'mata_pelajaran'],
                ['id' => 'guru', 'label' => 'Guru', 'sortable' => 'guru'],
                ['id' => 'm1', 'label' => 'M1', 'sortable' => 'm1'],
                ['id' => 'm2', 'label' => 'M2', 'sortable' => 'm2'],
                ['id' => 'm3', 'label' => 'M3', 'sortable' => 'm3'],
                ['id' => 'm4', 'label' => 'M4', 'sortable' => 'm4'],
                ['id' => 'rata_rata', 'label' => 'Rata-rata', 'sortable' => 'rata_rata', 'hideable' => false],
            ];
        }

        return [
            ['id' => 'nama_lengkap', 'label' => 'Nama Siswa', 'sortable' => 'nama_lengkap'],
            ['id' => 'm1', 'label' => 'Minggu 1', 'sortable' => 'm1'],
            ['id' => 'm2', 'label' => 'Minggu 2', 'sortable' => 'm2'],
            ['id' => 'm3', 'label' => 'Minggu 3', 'sortable' => 'm3'],
            ['id' => 'm4', 'label' => 'Minggu 4', 'sortable' => 'm4'],
            ['id' => 'rata_rata', 'label' => 'Rata-rata', 'sortable' => 'rata_rata', 'hideable' => false],
        ];
    }

    private function authorizedTeaching(): Pengajaran
    {
        $teaching = Pengajaran::with('kelas.siswaKelas')->findOrFail($this->pengajaranId);

        if (auth()->user()->hasRole('guru')) {
            abort_unless($teaching->guru_id === auth()->user()->guru?->id, 403);
        }

        abort_if(auth()->user()->hasRole('siswa'), 403);

        return $teaching;
    }

    public function loadNilai(): void
    {
        $this->nilai = [];

        if (! $this->pengajaranId) {
            return;
        }

        $teaching = $this->authorizedTeaching();
        $studentIds = $teaching->kelas->siswaKelas()->where('status', 'aktif')->pluck('siswa_id');

        foreach ($studentIds as $studentId) {
            $this->nilai[$studentId] = array_fill(1, 4, '');
        }

        NilaiTugas::query()
            ->where('pengajaran_id', $teaching->id)
            ->where('bulan', $this->bulan)
            ->get()
            ->each(function (NilaiTugas $grade): void {
                $this->nilai[$grade->siswa_id][$grade->minggu] = $grade->nilai === null ? '' : (float) $grade->nilai;
            });
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('nilai.create'), 403);
        $teaching = $this->authorizedTeaching();
        app(PenyimpananNilaiMassal::class)->handle(auth()->user(), $teaching, $this->bulan, $this->nilai);
        $this->dispatch('notify', type: 'success', message: 'Nilai seluruh siswa berhasil disimpan.');
    }

    public function average(array $weeks): string
    {
        $values = collect($weeks)
            ->filter(fn ($value) => $value !== '' && $value !== null)
            ->map(fn ($value) => (float) $value);

        return $values->isEmpty() ? '-' : number_format($values->avg(), 2);
    }

    private function gradeAggregates(string $table = 'nilai_tugas'): array
    {
        return [
            "MAX(CASE WHEN {$table}.minggu = 1 THEN {$table}.nilai END) as m1",
            "MAX(CASE WHEN {$table}.minggu = 2 THEN {$table}.nilai END) as m2",
            "MAX(CASE WHEN {$table}.minggu = 3 THEN {$table}.nilai END) as m3",
            "MAX(CASE WHEN {$table}.minggu = 4 THEN {$table}.nilai END) as m4",
            "AVG({$table}.nilai) as rata_rata",
        ];
    }

    private function teacherTableQuery(): Builder
    {
        if (! $this->pengajaranId) {
            return Siswa::query()->whereRaw('1 = 0');
        }

        $teaching = $this->authorizedTeaching();
        $query = Siswa::query()
            ->select('siswa.*')
            ->addSelect(array_map(fn ($sql) => DB::raw($sql), $this->gradeAggregates()))
            ->join('siswa_kelas', function ($join) use ($teaching): void {
                $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                    ->where('siswa_kelas.kelas_id', $teaching->kelas_id)
                    ->where('siswa_kelas.status', 'aktif');
            })
            ->leftJoin('nilai_tugas', function ($join) use ($teaching): void {
                $join->on('nilai_tugas.siswa_id', '=', 'siswa.id')
                    ->where('nilai_tugas.pengajaran_id', $teaching->id)
                    ->where('nilai_tugas.bulan', $this->bulan);
            })
            ->when($this->search, fn ($builder) => $builder->where(fn ($nested) => $nested
                ->where('siswa.nama_lengkap', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nis', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nisn', 'like', '%'.$this->search.'%')))
            ->groupBy('siswa.id');

        return $query;
    }

    private function studentTableQuery(): Builder
    {
        $studentId = auth()->user()->siswa?->id;

        return Pengajaran::query()
            ->select('pengajaran.*', 'mata_pelajaran.nama as mata_pelajaran', 'guru.nama_lengkap as guru')
            ->addSelect(array_map(fn ($sql) => DB::raw($sql), $this->gradeAggregates()))
            ->join('kelas', 'kelas.id', '=', 'pengajaran.kelas_id')
            ->join('semester', 'semester.id', '=', 'pengajaran.semester_id')
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
            ->join('guru', 'guru.id', '=', 'pengajaran.guru_id')
            ->join('siswa_kelas', function ($join) use ($studentId): void {
                $join->on('siswa_kelas.kelas_id', '=', 'pengajaran.kelas_id')
                    ->where('siswa_kelas.siswa_id', $studentId);
            })
            ->leftJoin('nilai_tugas', function ($join) use ($studentId): void {
                $join->on('nilai_tugas.pengajaran_id', '=', 'pengajaran.id')
                    ->where('nilai_tugas.siswa_id', $studentId)
                    ->where('nilai_tugas.bulan', $this->bulan);
            })
            ->when($this->search, fn ($builder) => $builder->where(fn ($nested) => $nested
                ->where('mata_pelajaran.nama', 'like', '%'.$this->search.'%')
                ->orWhere('guru.nama_lengkap', 'like', '%'.$this->search.'%')))
            ->when($this->tahunId, fn ($builder) => $builder->where('kelas.tahun_ajaran_id', $this->tahunId))
            ->when($this->semesterId, fn ($builder) => $builder->where('pengajaran.semester_id', $this->semesterId))
            ->when($this->mapelId, fn ($builder) => $builder->where('pengajaran.mata_pelajaran_id', $this->mapelId))
            ->groupBy('pengajaran.id', 'mata_pelajaran.nama', 'guru.nama_lengkap');
    }

    private function tableQuery(): Builder
    {
        return auth()->user()->hasRole('siswa') ? $this->studentTableQuery() : $this->teacherTableQuery();
    }

    private function applySort(Builder $query): Builder
    {
        if ($this->sort !== '') {
            return $query->orderBy($this->sort, $this->direction);
        }

        return auth()->user()->hasRole('siswa')
            ? $query->orderBy('mata_pelajaran.nama')
            : $query->orderBy('siswa.nama_lengkap');
    }

    public function with(): array
    {
        $user = auth()->user();
        $error = null;

        try {
            $query = $this->tableQuery();
            $total = (clone $query)->getQuery()->getCountForPagination();
            $rows = $this->applySort($query)->paginate($this->perPage);
        } catch (\Throwable $exception) {
            report($exception);
            $error = 'Terjadi kesalahan saat mengambil data nilai.';
            $total = 0;
            $rows = Siswa::query()->whereRaw('1 = 0')->paginate($this->perPage);
        }

        $teachings = Pengajaran::with(['semester.tahunAjaran', 'kelas', 'mataPelajaran', 'guru'])
            ->where('status', 'aktif')
            ->when($user->hasRole('guru'), fn ($query) => $query->where('guru_id', $user->guru?->id))
            ->get()
            ->mapWithKeys(fn ($teaching) => [$teaching->id => $teaching->semester->tahunAjaran->nama.' · '.ucfirst($teaching->semester->nama).' · '.$teaching->kelas->nama.' · '.$teaching->mataPelajaran->nama.' · '.$teaching->guru->nama_lengkap])
            ->all();

        return [
            'rows' => $rows,
            'datasetTotal' => $total,
            'tableError' => $error,
            'tableColumns' => $this->tableColumns(),
            'visibleColumnIds' => $this->validatedVisibleColumns(),
            'teachings' => $teachings,
            'years' => TahunAjaran::orderByDesc('nama')->pluck('nama', 'id')->all(),
            'semesters' => Semester::with('tahunAjaran')->when($this->tahunId, fn ($query) => $query->where('tahun_ajaran_id', $this->tahunId))->get()->mapWithKeys(fn ($semester) => [$semester->id => $semester->tahunAjaran->nama.' · '.ucfirst($semester->nama)])->all(),
            'subjects' => MataPelajaran::orderBy('nama')->pluck('nama', 'id')->all(),
        ];
    }
};
?>

<div class="min-w-0 w-full">
    <div class="mb-6">
        <p class="text-sm font-semibold text-sky-700">Akademik</p>
        <h1 class="page-title">{{ auth()->user()->hasRole('siswa') ? 'Nilai Saya' : 'Nilai Siswa' }}</h1>
        <p class="page-subtitle">Nilai tugas Minggu 1–4; kolom kosong berarti belum dinilai, bukan nol.</p>
    </div>

    <div class="mb-4 grid gap-3 lg:grid-cols-[minmax(18rem,1fr)_auto]">
        <div class="card p-4">
            <label class="form-label" for="grade-search">Pencarian Utama</label>
            <input id="grade-search" type="search" wire:model.live.debounce.400ms="search" class="form-input" placeholder="Cari siswa, mata pelajaran, atau guru...">
        </div>
        <div class="card flex flex-wrap items-end gap-3 p-4">
            <button type="button" wire:click="resetTableState" class="btn-secondary">Reset Filter</button>
            <x-data-table.column-toggle :columns="$tableColumns" :visible="$visibleColumnIds" />
        </div>
    </div>

    <div class="filters card mb-4 grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-4">
        @if(auth()->user()->hasRole('siswa'))
            <div><label class="form-label">Tahun Ajaran</label><x-searchable-select model="tahunId" :value="$tahunId" :options="$years" placeholder="Semua tahun ajaran" search-placeholder="Cari tahun ajaran..." /></div>
            <div><label class="form-label">Semester</label><x-searchable-select model="semesterId" :value="$semesterId" :options="$semesters" placeholder="Semua semester" search-placeholder="Cari semester..." /></div>
            <div><label class="form-label">Mata Pelajaran</label><x-searchable-select model="mapelId" :value="$mapelId" :options="$subjects" placeholder="Semua mata pelajaran" search-placeholder="Cari mata pelajaran..." /></div>
        @else
            <div class="md:col-span-2 xl:col-span-3"><label class="form-label">Pengajaran</label><x-searchable-select model="pengajaranId" :value="$pengajaranId" :options="$teachings" placeholder="Pilih pengajaran" search-placeholder="Cari kelas, mata pelajaran, atau guru..." /></div>
        @endif
        <div><label class="form-label">Bulan</label><x-searchable-select model="bulan" :value="$bulan" :options="collect(range(1,12))->mapWithKeys(fn($month)=>[$month=>\Carbon\Carbon::create()->month($month)->translatedFormat('F')])->all()" placeholder="Pilih bulan" search-placeholder="Cari bulan..." /></div>
    </div>

    <form wire:submit="save" class="block min-w-0 w-full">
        @can('nilai.create')
            @unless(auth()->user()->hasRole('siswa'))
                <div class="mb-3 flex justify-end"><button type="submit" class="btn-primary" wire:loading.attr="disabled" @disabled(!$pengajaranId)><span wire:loading.remove wire:target="save">Simpan Nilai</span><span wire:loading wire:target="save">Menyimpan...</span></button></div>
            @endunless
        @endcan

        <div class="table-shell w-full">
            <div class="table-scroll w-full">
                <table class="data-table w-full min-w-[820px]">
                    <thead><tr>
                        @foreach($tableColumns as $column)
                            @continue(!in_array($column['id'], $visibleColumnIds, true))
                            <th><button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="inline-flex min-h-11 items-center gap-1 text-left"><span>{{ $column['label'] }}</span><span aria-hidden="true">{{ $sort===$column['sortable'] ? ($direction==='asc'?'↑':'↓') : '↕' }}</span></button></th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr wire:key="grade-row-{{ $row->getKey() }}" wire:loading.remove>
                                @foreach($tableColumns as $column)
                                    @continue(!in_array($column['id'], $visibleColumnIds, true))
                                    <td>
                                        @if(!auth()->user()->hasRole('siswa') && in_array($column['id'], ['m1','m2','m3','m4'], true))
                                            @php $week=(int)substr($column['id'],1); @endphp
                                            @can('nilai.create')<input type="number" min="0" max="100" step="0.01" inputmode="decimal" wire:model.live.debounce.500ms="nilai.{{ $row->id }}.{{ $week }}" class="form-input w-20 tabular-nums sm:w-24" aria-label="Nilai {{ $row->nama_lengkap }} minggu {{ $week }}">@else{{ data_get($nilai, $row->id.'.'.$week)==='' ? '-' : data_get($nilai, $row->id.'.'.$week) }}@endcan
                                            @error('nilai.'.$row->id.'.'.$week)<p class="form-error">Nilai harus 0–100.</p>@enderror
                                        @elseif($column['id']==='rata_rata')
                                            <span class="font-semibold tabular-nums">{{ auth()->user()->hasRole('siswa') ? ($row->rata_rata===null?'-':number_format((float)$row->rata_rata,2)) : $this->average($nilai[$row->id]??[]) }}</span>
                                        @elseif(in_array($column['id'], ['m1','m2','m3','m4'], true))
                                            {{ $row->{$column['id']}===null ? '-' : number_format((float)$row->{$column['id']}, 0) }}
                                        @else
                                            {{ filled($row->{$column['id']}) ? $row->{$column['id']} : '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <x-data-table.states :columns="count($visibleColumnIds)" :empty="$rows->isEmpty()" :filtered="filled($search)||filled($pengajaranId)||filled($tahunId)||filled($semesterId)||filled($mapelId)" :error="$tableError" />
                    </tbody>
                </table>
            </div>
            <x-data-table.pagination :paginator="$rows" :per-page="$perPage" />
        </div>
    </form>
</div>
