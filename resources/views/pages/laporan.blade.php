<?php

use App\Livewire\Concerns\WithDataTable;
use App\Models\{Guru, JadwalPelajaran, Kelas, MataPelajaran, NilaiTugas, PengaturanSekolah, Semester, Siswa};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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

    private function gradeSummary(): ?object
    {
        if ($this->jenis !== 'nilai') {
            return null;
        }

        return DB::query()
            ->fromSub($this->gradesQuery(), 'laporan_nilai')
            ->selectRaw('AVG(m1) as avg_m1')
            ->selectRaw('AVG(m2) as avg_m2')
            ->selectRaw('AVG(m3) as avg_m3')
            ->selectRaw('AVG(m4) as avg_m4')
            ->selectRaw('AVG(rata_rata) as avg_rata_rata')
            ->first();
    }

    public function with(): array
    {
        $error = null;
        $gradeSummary = null;

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

        if ($this->jenis === 'nilai' && $total > 0 && $error === null) {
            try {
                $gradeSummary = $this->gradeSummary();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return [
            'rows' => $rows,
            'datasetTotal' => $total,
            'gradeSummary' => $gradeSummary,
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

@php
    $semesterLabel = $semesterId ? ($semesters[$semesterId] ?? 'Semester tidak ditemukan') : 'Semua semester';
    $classLabel = $kelasId ? ($classes[$kelasId] ?? 'Kelas tidak ditemukan') : 'Semua kelas';
    $teacherLabel = $guruId ? ($teachers[$guruId] ?? 'Guru tidak ditemukan') : 'Semua guru';
    $subjectLabel = $mapelId ? ($subjects[$mapelId] ?? 'Mata pelajaran tidak ditemukan') : 'Semua mata pelajaran';
    $studentLabel = $siswaId ? ($students[$siswaId] ?? 'Siswa tidak ditemukan') : 'Semua siswa';
    $periodLabel = $jenis === 'jadwal' ? ($hari ? ucfirst($hari) : 'Semua hari') : ($months[$bulan] ?? '-');
@endphp

<div class="min-w-0 w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-sky-700">Laporan Akademik</p><h1 class="page-title">Laporan {{ ucfirst($jenis) }}</h1><p class="page-subtitle">Tinjau data, sesuaikan periode, kemudian cetak atau unduh sebagai PDF.</p></div>
        <div class="print-hidden flex flex-wrap gap-2">
            <button type="button" onclick="window.print()" class="btn-secondary gap-2"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v6H7z"/></svg>Cetak</button>
            <a href="{{ route('laporan.pdf',['jenis'=>$jenis,'semester_id'=>$semesterId,'kelas_id'=>$kelasId,'guru_id'=>$guruId,'mapel_id'=>$mapelId,'siswa_id'=>$siswaId,'hari'=>$hari,'bulan'=>$bulan]) }}" class="btn-primary gap-2"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14"/></svg>Unduh PDF</a>
        </div>
    </div>

    <div class="filters card print-hidden mb-5 overflow-visible">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-end">
            <div class="min-w-0 flex-1"><label class="form-label" for="report-search">Pencarian Utama</label><div class="relative"><svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-3 size-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input id="report-search" type="search" wire:model.live.debounce.400ms="search" class="form-input pl-10" placeholder="Cari {{ $jenis === 'jadwal' ? 'kelas, mata pelajaran, atau guru' : 'siswa, mata pelajaran, atau guru' }}..."></div></div>
            <div class="flex flex-wrap gap-2"><button type="button" wire:click="resetTableState" class="btn-secondary gap-2"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 4v6h6M20 20v-6h-6M5.5 15a7 7 0 0 0 11.9 2M18.5 9A7 7 0 0 0 6.6 7"/></svg>Reset Filter</button><x-data-table.column-toggle :columns="$tableColumns" :visible="$visibleColumnIds" /></div>
        </div>
        <div class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-4">
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
    </div>

    <section class="report-sheet overflow-hidden">
        <header class="report-heading">
            <div class="flex min-w-0 items-center gap-4 text-left"><img src="{{ asset('images/favicon-logo-pendidikan/favicon.svg') }}" alt="Logo Tut Wuri Handayani" width="56" height="68" class="h-16 w-14 shrink-0 object-contain"><div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Dokumen Akademik Sekolah</p><h2 class="mt-1 text-lg font-bold uppercase leading-tight text-slate-950 sm:text-xl">{{ $sekolah?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah' }}</h2>@if($sekolah?->alamat)<p class="mt-1 text-sm leading-5 text-slate-500">{{ $sekolah->alamat }}</p>@endif</div></div>
            <div class="mt-5 flex flex-col gap-1 border-t border-slate-200 pt-5 text-left sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Jenis Laporan</p><h3 class="mt-1 text-xl font-bold text-slate-950">Laporan {{ ucfirst($jenis) }} {{ $jenis==='nilai'?'Tugas Siswa':'' }}</h3></div><p class="mt-2 text-xs text-slate-500 sm:mt-0">Diperbarui {{ now()->translatedFormat('d F Y, H:i') }} WIT</p></div>
            <dl class="report-meta-grid">
                <div><dt>Semester</dt><dd>{{ $semesterLabel }}</dd></div>
                <div><dt>Kelas</dt><dd>{{ $classLabel }}</dd></div>
                <div><dt>{{ $jenis === 'jadwal' ? 'Guru' : 'Mata Pelajaran' }}</dt><dd>{{ $jenis === 'jadwal' ? $teacherLabel : $subjectLabel }}</dd></div>
                <div><dt>{{ $jenis === 'jadwal' ? 'Hari' : 'Bulan' }}</dt><dd>{{ $periodLabel }}</dd></div>
                @if($jenis === 'nilai' && $siswaId)<div><dt>Siswa</dt><dd>{{ $studentLabel }}</dd></div>@endif
            </dl>
        </header>
        <x-data-table.mobile-hint />
        <div class="table-scroll report-table-scroll w-full"><table class="data-table report-table w-full min-w-full"><thead><tr>
            @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<th aria-sort="{{ $sort===$column['sortable']?($direction==='asc'?'ascending':'descending'):'none' }}"><button type="button" wire:click="sortBy('{{ $column['sortable'] }}')" class="print-hidden inline-flex min-h-11 items-center gap-1.5 text-left"><span>{{ $column['label'] }}</span><span class="text-sky-200" aria-hidden="true">{{ $sort===$column['sortable']?($direction==='asc'?'↑':'↓'):'↕' }}</span></button><span class="hidden print:inline">{{ $column['label'] }}</span></th>@endforeach
        </tr></thead><tbody>
            @foreach($rows as $row)
                <tr wire:key="report-row-{{ $row->getKey() }}" wire:loading.remove>
                    @foreach($tableColumns as $column)@continue(!in_array($column['id'],$visibleColumnIds,true))<td>
                        @if($column['id']==='hari')<span class="report-day-badge capitalize">{{ $row->hari }}</span>
                        @elseif($column['id']==='jam')<span class="inline-flex flex-col tabular-nums"><strong class="text-slate-900">{{ substr($row->jam_mulai,0,5) }}</strong><span class="text-xs text-slate-500">s.d. {{ substr($row->jam_selesai,0,5) }}</span></span>
                        @elseif($column['id']==='kelas')<span class="report-class-badge">{{ $row->kelas }}</span>
                        @elseif(in_array($column['id'],['nama_siswa','mata_pelajaran'],true))<span class="font-semibold text-slate-900">{{ $row->{$column['id']} }}</span>
                        @elseif($column['id']==='identitas')<span class="font-medium tabular-nums text-slate-700">{{ $row->identitas }}</span>
                        @elseif(in_array($column['id'],['m1','m2','m3','m4'],true))<span class="report-score">{{ $row->{$column['id']}===null?'-':number_format((float)$row->{$column['id']},0) }}</span>
                        @elseif($column['id']==='rata_rata')<span class="report-average">{{ $row->rata_rata===null?'-':number_format((float)$row->rata_rata,2) }}</span>
                        @else<span class="text-slate-700">{{ filled($row->{$column['id']})?$row->{$column['id']}:'-' }}</span>@endif
                    </td>@endforeach
                </tr>
            @endforeach
            <x-data-table.states :columns="count($visibleColumnIds)" :empty="$rows->isEmpty()" :filtered="filled($search)||filled($semesterId)||filled($kelasId)||filled($guruId)||filled($mapelId)||filled($hari)||filled($siswaId)" :error="$tableError" />
        </tbody>
            @if($jenis === 'nilai' && $gradeSummary && ! $rows->isEmpty())
                @php
                    $summaryLabelColumnId = collect(['identitas', 'nama_siswa', 'mata_pelajaran', 'guru'])->first(fn ($id) => in_array($id, $visibleColumnIds, true)) ?? $visibleColumnIds[0];
                @endphp
                <tfoot>
                    <tr class="report-summary-row">
                        @foreach($tableColumns as $column)
                            @continue(!in_array($column['id'], $visibleColumnIds, true))
                            <td>
                                @if($column['id']===$summaryLabelColumnId)<span class="font-bold text-slate-900">Rata-rata</span>@endif
                                @if(in_array($column['id'], ['m1', 'm2', 'm3', 'm4'], true))
                                    <span class="report-score">{{ $gradeSummary->{'avg_'.$column['id']}===null ? '-' : number_format((float) $gradeSummary->{'avg_'.$column['id']}, 2) }}</span>
                                @elseif($column['id']==='rata_rata')
                                    <span class="report-average">{{ $gradeSummary->avg_rata_rata===null ? '-' : number_format((float) $gradeSummary->avg_rata_rata, 2) }}</span>
                                @elseif($column['id']!==$summaryLabelColumnId)
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table></div>
        <div class="print-hidden"><x-data-table.pagination :paginator="$rows" :per-page="$perPage" /></div>
    </section>
</div>
