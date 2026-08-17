<?php

use App\Livewire\Concerns\WithDataTable;
use App\Models\{Guru, JadwalPelajaran, Kelas, MataPelajaran, NilaiTugas, PengaturanSekolah, Semester, Siswa};
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    use WithDataTable;

    public string $jenis = 'jadwal';

    #[Url(as: 'semester_id')]
    public string $semesterId = '';

    #[Url(as: 'kelas_id')]
    public string $kelasId = '';

    #[Url(as: 'guru_id')]
    public string $guruId = '';

    #[Url(as: 'mapel_id')]
    public string $mapelId = '';

    #[Url(as: 'siswa_id')]
    public string $siswaId = '';

    #[Url]
    public string $hari = '';

    #[Url]
    public int $bulan = 0;

    public function mount(?string $jenis = null): void
    {
        $this->jenis = $jenis ?? request()->route('jenis', 'jadwal');
        abort_unless(in_array($this->jenis, ['jadwal', 'nilai'], true), 404);
        $this->bulan = max(1, min(12, (int) ($this->bulan ?: now()->month)));

        if (auth()->user()->hasRole('siswa')) {
            $this->siswaId = (string) auth()->user()->siswa?->id;
        }

        if (auth()->user()->hasRole('guru')) {
            $this->guruId = (string) auth()->user()->guru?->id;
        }

        $this->initializeDataTable();
    }

    public function updatedSemesterId(): void { $this->datasetChanged(); }
    public function updatedKelasId(): void { $this->datasetChanged(); }
    public function updatedGuruId(): void { $this->datasetChanged(); }
    public function updatedMapelId(): void { $this->datasetChanged(); }
    public function updatedSiswaId(): void { $this->datasetChanged(); }
    public function updatedHari(): void { $this->datasetChanged(); }

    public function updatedBulan(mixed $value): void
    {
        $this->bulan = max(1, min(12, (int) $value));
        $this->datasetChanged();
    }

    public function resetTableFilters(): void
    {
        $this->semesterId = '';
        $this->kelasId = '';
        $this->mapelId = '';
        $this->hari = '';
        $this->bulan = now()->month;
        $this->guruId = auth()->user()->hasRole('guru') ? (string) auth()->user()->guru?->id : '';
        $this->siswaId = auth()->user()->hasRole('siswa') ? (string) auth()->user()->siswa?->id : '';
    }

    protected function tableSortableColumns(): array
    {
        return $this->jenis === 'jadwal'
            ? ['hari_urutan', 'jam_mulai', 'kelas', 'mata_pelajaran', 'guru']
            : ['identitas', 'nama_siswa', 'mata_pelajaran', 'guru', 'm1', 'm2', 'm3', 'm4', 'rata_rata'];
    }

    protected function tableColumns(): array
    {
        if ($this->jenis === 'jadwal') {
            return [
                ['id' => 'hari', 'label' => 'Hari', 'sortable' => 'hari_urutan'],
                ['id' => 'jam', 'label' => 'Jam', 'sortable' => 'jam_mulai'],
                ['id' => 'kelas', 'label' => 'Kelas', 'sortable' => 'kelas'],
                ['id' => 'mata_pelajaran', 'label' => 'Mata Pelajaran', 'sortable' => 'mata_pelajaran'],
                ['id' => 'guru', 'label' => 'Guru', 'sortable' => 'guru', 'hideable' => false],
            ];
        }

        return [
            ['id' => 'identitas', 'label' => 'NIS/NISN', 'sortable' => 'identitas'],
            ['id' => 'nama_siswa', 'label' => 'Nama Siswa', 'sortable' => 'nama_siswa'],
            ['id' => 'mata_pelajaran', 'label' => 'Mata Pelajaran', 'sortable' => 'mata_pelajaran'],
            ['id' => 'guru', 'label' => 'Guru', 'sortable' => 'guru'],
            ['id' => 'm1', 'label' => 'M1', 'sortable' => 'm1'],
            ['id' => 'm2', 'label' => 'M2', 'sortable' => 'm2'],
            ['id' => 'm3', 'label' => 'M3', 'sortable' => 'm3'],
            ['id' => 'm4', 'label' => 'M4', 'sortable' => 'm4'],
            ['id' => 'rata_rata', 'label' => 'Rata-rata', 'sortable' => 'rata_rata', 'hideable' => false],
        ];
    }

    private function scheduleQuery(): Builder
    {
        $dayOrder = "CASE jadwal_pelajaran.hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 ELSE 7 END";
        $query = JadwalPelajaran::query()
            ->select('jadwal_pelajaran.*', 'kelas.nama as kelas', 'mata_pelajaran.nama as mata_pelajaran', 'guru.nama_lengkap as guru', 'jam_pelajaran.jam_mulai', 'jam_pelajaran.jam_selesai')
            ->selectRaw($dayOrder.' as hari_urutan')
            ->join('pengajaran', 'pengajaran.id', '=', 'jadwal_pelajaran.pengajaran_id')
            ->join('kelas', 'kelas.id', '=', 'pengajaran.kelas_id')
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
            ->join('guru', 'guru.id', '=', 'pengajaran.guru_id')
            ->join('jam_pelajaran', 'jam_pelajaran.id', '=', 'jadwal_pelajaran.jam_pelajaran_id')
            ->when($this->search, fn ($builder) => $builder->where(fn ($nested) => $nested
                ->where('kelas.nama', 'like', '%'.$this->search.'%')
                ->orWhere('mata_pelajaran.nama', 'like', '%'.$this->search.'%')
                ->orWhere('guru.nama_lengkap', 'like', '%'.$this->search.'%')
                ->orWhere('jadwal_pelajaran.hari', 'like', '%'.$this->search.'%')))
            ->when($this->semesterId, fn ($builder) => $builder->where('pengajaran.semester_id', $this->semesterId))
            ->when($this->kelasId, fn ($builder) => $builder->where('pengajaran.kelas_id', $this->kelasId))
            ->when($this->guruId, fn ($builder) => $builder->where('pengajaran.guru_id', $this->guruId))
            ->when($this->hari, fn ($builder) => $builder->where('jadwal_pelajaran.hari', $this->hari));

        if (auth()->user()->hasRole('guru')) {
            $query->where('pengajaran.guru_id', auth()->user()->guru?->id);
        }

        if (auth()->user()->hasRole('siswa')) {
            $classIds = auth()->user()->siswa?->penempatanKelas()->pluck('kelas_id') ?? collect();
            $query->whereIn('pengajaran.kelas_id', $classIds);
        }

        return $query;
    }

    private function gradesQuery(): Builder
    {
        $query = NilaiTugas::query()
            ->selectRaw('MIN(nilai_tugas.id) as id')
            ->selectRaw("COALESCE(siswa.nis, siswa.nisn, '-') as identitas")
            ->selectRaw('siswa.nama_lengkap as nama_siswa')
            ->selectRaw('mata_pelajaran.nama as mata_pelajaran')
            ->selectRaw('guru.nama_lengkap as guru')
            ->selectRaw('MAX(CASE WHEN nilai_tugas.minggu = 1 THEN nilai_tugas.nilai END) as m1')
            ->selectRaw('MAX(CASE WHEN nilai_tugas.minggu = 2 THEN nilai_tugas.nilai END) as m2')
            ->selectRaw('MAX(CASE WHEN nilai_tugas.minggu = 3 THEN nilai_tugas.nilai END) as m3')
            ->selectRaw('MAX(CASE WHEN nilai_tugas.minggu = 4 THEN nilai_tugas.nilai END) as m4')
            ->selectRaw('AVG(nilai_tugas.nilai) as rata_rata')
            ->join('siswa', 'siswa.id', '=', 'nilai_tugas.siswa_id')
            ->join('pengajaran', 'pengajaran.id', '=', 'nilai_tugas.pengajaran_id')
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
            ->join('guru', 'guru.id', '=', 'pengajaran.guru_id')
            ->where('nilai_tugas.bulan', $this->bulan)
            ->when($this->search, fn ($builder) => $builder->where(fn ($nested) => $nested
                ->where('siswa.nama_lengkap', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nis', 'like', '%'.$this->search.'%')
                ->orWhere('siswa.nisn', 'like', '%'.$this->search.'%')
                ->orWhere('mata_pelajaran.nama', 'like', '%'.$this->search.'%')
                ->orWhere('guru.nama_lengkap', 'like', '%'.$this->search.'%')))
            ->when($this->semesterId, fn ($builder) => $builder->where('pengajaran.semester_id', $this->semesterId))
            ->when($this->kelasId, fn ($builder) => $builder->where('pengajaran.kelas_id', $this->kelasId))
            ->when($this->guruId, fn ($builder) => $builder->where('pengajaran.guru_id', $this->guruId))
            ->when($this->mapelId, fn ($builder) => $builder->where('pengajaran.mata_pelajaran_id', $this->mapelId))
            ->when($this->siswaId, fn ($builder) => $builder->where('nilai_tugas.siswa_id', $this->siswaId))
            ->groupBy('nilai_tugas.pengajaran_id', 'nilai_tugas.siswa_id', 'siswa.nis', 'siswa.nisn', 'siswa.nama_lengkap', 'mata_pelajaran.nama', 'guru.nama_lengkap');

        if (auth()->user()->hasRole('guru')) {
            $query->where('pengajaran.guru_id', auth()->user()->guru?->id);
        }

        if (auth()->user()->hasRole('siswa')) {
            $query->where('nilai_tugas.siswa_id', auth()->user()->siswa?->id);
        }

        return $query;
    }

    private function tableQuery(): Builder
    {
        return $this->jenis === 'jadwal' ? $this->scheduleQuery() : $this->gradesQuery();
    }

    private function applySort(Builder $query): Builder
    {
        if ($this->sort !== '') {
            return $query->orderBy($this->sort, $this->direction);
        }

        return $this->jenis === 'jadwal'
            ? $query->orderBy('hari_urutan')->orderBy('jam_pelajaran.urutan')
            : $query->orderBy('nama_siswa')->orderBy('mata_pelajaran');
    }

    public function with(): array
    {
        $error = null;

        try {
            $query = $this->tableQuery();
            $total = (clone $query)->getQuery()->getCountForPagination();
            $rows = $this->applySort($query)->paginate($this->perPage);
        } catch (\Throwable $exception) {
            report($exception);
            $error = 'Terjadi kesalahan saat mengambil data laporan.';
            $total = 0;
            $rows = ($this->jenis === 'jadwal' ? JadwalPelajaran::query() : NilaiTugas::query())->whereRaw('1 = 0')->paginate($this->perPage);
        }

        return [
            'rows' => $rows,
            'datasetTotal' => $total,
            'tableError' => $error,
            'tableColumns' => $this->tableColumns(),
            'visibleColumnIds' => $this->validatedVisibleColumns(),
            'sekolah' => PengaturanSekolah::first(),
            'semesters' => Semester::with('tahunAjaran')->get()->mapWithKeys(fn ($semester) => [$semester->id => $semester->tahunAjaran->nama.' · '.ucfirst($semester->nama)])->all(),
            'classes' => Kelas::orderBy('nama')->pluck('nama', 'id')->all(),
            'teachers' => Guru::orderBy('nama_lengkap')->pluck('nama_lengkap', 'id')->all(),
            'subjects' => MataPelajaran::orderBy('nama')->pluck('nama', 'id')->all(),
            'students' => Siswa::orderBy('nama_lengkap')->pluck('nama_lengkap', 'id')->all(),
            'days' => collect(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])->mapWithKeys(fn ($day) => [$day => ucfirst($day)])->all(),
            'months' => collect(range(1, 12))->mapWithKeys(fn ($month) => [$month => \Carbon\Carbon::create()->month($month)->translatedFormat('F')])->all(),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-sky-700">Laporan</p><h1 class="page-title">Laporan {{ ucfirst($jenis) }}</h1><p class="page-subtitle">Preview web siap cetak dan unduh PDF.</p></div>
        <div class="print-hidden flex gap-2"><button type="button" onclick="window.print()" class="btn-secondary">Cetak</button><a href="{{ route('laporan.pdf',['jenis'=>$jenis,'semester_id'=>$semesterId,'kelas_id'=>$kelasId,'guru_id'=>$guruId,'mapel_id'=>$mapelId,'siswa_id'=>$siswaId,'hari'=>$hari,'bulan'=>$bulan]) }}" class="btn-primary">Unduh PDF</a></div>
    </div>

    <div class="print-hidden mb-4 grid gap-3 lg:grid-cols-[minmax(18rem,1fr)_auto]">
        <div class="card p-4"><label class="form-label" for="report-search">Pencarian Utama</label><input id="report-search" type="search" wire:model.live.debounce.400ms="search" class="form-input" placeholder="Cari data laporan..."></div>
        <div class="card flex flex-wrap items-end gap-3 p-4"><button type="button" wire:click="resetTableState" class="btn-secondary">Reset Filter</button><x-data-table.column-toggle :columns="$tableColumns" :visible="$visibleColumnIds" /></div>
    </div>

    <div class="filters card print-hidden mb-4 grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4">
        <div><label class="form-label">Semester</label><x-searchable-select model="semesterId" :value="$semesterId" :options="$semesters" placeholder="Semua semester" search-placeholder="Cari semester..." /></div>
        <div><label class="form-label">Kelas</label><x-searchable-select model="kelasId" :value="$kelasId" :options="$classes" placeholder="Semua kelas" search-placeholder="Cari kelas..." /></div>
        @unless(auth()->user()->hasAnyRole(['guru','siswa']))<div><label class="form-label">Guru</label><x-searchable-select model="guruId" :value="$guruId" :options="$teachers" placeholder="Semua guru" search-placeholder="Cari guru..." /></div>@endunless
        @if($jenis==='jadwal')
            <div><label class="form-label">Hari</label><x-searchable-select model="hari" :value="$hari" :options="$days" placeholder="Semua hari" search-placeholder="Cari hari..." /></div>
        @else
            <div><label class="form-label">Mata Pelajaran</label><x-searchable-select model="mapelId" :value="$mapelId" :options="$subjects" placeholder="Semua mata pelajaran" search-placeholder="Cari mata pelajaran..." /></div>
            <div><label class="form-label">Bulan</label><x-searchable-select model="bulan" :value="$bulan" :options="$months" placeholder="Pilih bulan" search-placeholder="Cari bulan..." /></div>
            @unless(auth()->user()->hasRole('siswa'))<div><label class="form-label">Siswa</label><x-searchable-select model="siswaId" :value="$siswaId" :options="$students" placeholder="Semua siswa" search-placeholder="Cari siswa..." /></div>@endunless
        @endif
    </div>

    <x-data-table.bulk-toolbar :count="$this->selectedCount($datasetTotal)" />

    <section class="card overflow-hidden">
        <div class="border-b p-6 text-center"><h2 class="font-semibold uppercase text-slate-950">{{ $sekolah?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah' }}</h2>@if($sekolah?->alamat)<p class="mt-1 text-sm text-slate-500">{{ $sekolah->alamat }}</p>@endif<p class="mt-2 text-lg font-bold uppercase">Laporan {{ ucfirst($jenis) }} {{ $jenis==='nilai'?'Tugas Siswa':'' }}</p><p class="mt-2 text-sm text-slate-500">Dicetak {{ now()->translatedFormat('d F Y H:i') }} WIT</p></div>
        <div class="table-scroll"><table class="data-table min-w-[900px]"><thead><tr>
            <th class="table-select-cell print-hidden sticky left-0 z-30 w-14"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" wire:click="toggleSelectAllDataset" @checked($datasetTotal>0&&$this->selectedCount($datasetTotal)===$datasetTotal) x-data x-effect="$el.indeterminate = {{ $this->selectedCount($datasetTotal)>0&&$this->selectedCount($datasetTotal)<$datasetTotal?'true':'false' }}" aria-label="Pilih seluruh baris laporan"></th>
            @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<th><button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="print-hidden inline-flex min-h-11 items-center gap-1 text-left"><span>{{ $column['label'] }}</span><span aria-hidden="true">{{ $sort===$column['sortable']?($direction==='asc'?'↑':'↓'):'↕' }}</span></button><span class="hidden print:inline">{{ $column['label'] }}</span></th>@endforeach
            <th class="table-action-cell print-hidden sticky right-0 z-30">Aksi</th>
        </tr></thead><tbody>
            @foreach($rows as $row)
                @php $selected=$this->isRowSelected($row->getKey()); @endphp
                <tr class="{{ $selected?'is-selected':'' }}" wire:key="report-row-{{ $row->getKey() }}" wire:loading.remove>
                    <td class="table-select-cell print-hidden sticky left-0 z-20"><input type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" @checked($selected) wire:click="toggleRowSelection({{ $row->getKey() }})" aria-label="Pilih baris laporan"></td>
                    @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<td>
                        @if($column['id']==='hari')<span class="font-semibold capitalize">{{ $row->hari }}</span>
                        @elseif($column['id']==='jam')<span class="tabular-nums">{{ substr($row->jam_mulai,0,5) }}–{{ substr($row->jam_selesai,0,5) }}</span>
                        @elseif(in_array($column['id'],['m1','m2','m3','m4'],true)){{ $row->{$column['id']}===null?'-':number_format((float)$row->{$column['id']},0) }}
                        @elseif($column['id']==='rata_rata')<span class="font-semibold tabular-nums">{{ $row->rata_rata===null?'-':number_format((float)$row->rata_rata,2) }}</span>
                        @else{{ filled($row->{$column['id']})?$row->{$column['id']}:'-' }}@endif
                    </td>@endforeach
                    <td class="table-action-cell print-hidden sticky right-0 z-20"><span class="text-slate-400">—</span></td>
                </tr>
            @endforeach
            <x-data-table.states :columns="count($visibleColumnIds)+2" :empty="$rows->isEmpty()" :filtered="filled($search)||filled($semesterId)||filled($kelasId)||filled($guruId)||filled($mapelId)||filled($hari)||filled($siswaId)" :error="$tableError" />
        </tbody></table></div>
        <div class="print-hidden"><x-data-table.pagination :paginator="$rows" :per-page="$perPage" /></div>
    </section>
</div>
