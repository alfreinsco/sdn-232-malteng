<?php

use App\Models\{Guru, JadwalPelajaran, Kelas, MataPelajaran, NilaiTugas, Pengajaran, Semester, Siswa, TahunAjaran};
use App\Services\PeriodeAktif;
use Livewire\Component;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        $periode = app(PeriodeAktif::class);
        $semester = $periode->semester();
        $hari = ['Sunday' => 'minggu', 'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu'][now()->format('l')];
        $jadwal = JadwalPelajaran::with(['pengajaran.kelas', 'pengajaran.guru', 'pengajaran.mataPelajaran', 'jamPelajaran'])
            ->where('hari', $hari)
            ->when($semester, fn($q) => $q->whereHas('pengajaran', fn($x) => $x->where('semester_id', $semester->id)))
            ->when($user->hasRole('guru'), fn($q) => $q->whereHas('pengajaran', fn($x) => $x->where('guru_id', $user->guru?->id)))
            ->when($user->hasRole('siswa'), function ($q) use ($user, $periode) {
                $kelasId = $user->siswa?->penempatanKelas()->where('status', 'aktif')->whereHas('kelas', fn($x) => $x->where('tahun_ajaran_id', $periode->tahunAjaran()?->id))->value('kelas_id');
                $q->whereHas('pengajaran', fn($x) => $x->where('kelas_id', $kelasId));
            })
            ->get()
            ->sortBy('jamPelajaran.urutan');
        $cards = $user->hasRole('guru')
            ? [['label' => 'Kelas Diajar', 'value' => Pengajaran::where('guru_id', $user->guru?->id)->distinct()->count('kelas_id')], ['label' => 'Mata Pelajaran', 'value' => Pengajaran::where('guru_id', $user->guru?->id)->distinct()->count('mata_pelajaran_id')], ['label' => 'Jadwal Hari Ini', 'value' => $jadwal->count()], ['label' => 'Nilai Diinput', 'value' => NilaiTugas::whereHas('pengajaran', fn($q) => $q->where('guru_id', $user->guru?->id))->whereNotNull('nilai')->count()]]
            : ($user->hasRole('siswa')
                ? [['label' => 'Kelas', 'value' => $user->siswa?->penempatanKelas()->where('status', 'aktif')->with('kelas')->first()?->kelas?->nama ?? '-'], ['label' => 'Jadwal Hari Ini', 'value' => $jadwal->count()], ['label' => 'Mata Pelajaran', 'value' => $jadwal->pluck('pengajaran.mata_pelajaran_id')->unique()->count()], ['label' => 'Rata-rata Nilai', 'value' => number_format((float) $user->siswa?->nilaiTugas()->whereNotNull('nilai')->avg('nilai'), 2)]]
                : [['label' => 'Jumlah Guru', 'value' => Guru::where('status', 'aktif')->count()], ['label' => 'Jumlah Siswa', 'value' => Siswa::where('status', 'aktif')->count()], ['label' => 'Jumlah Kelas', 'value' => Kelas::where('status', 'aktif')->count()], ['label' => 'Mata Pelajaran', 'value' => MataPelajaran::where('status', 'aktif')->count()], ['label' => 'Jumlah Jadwal', 'value' => JadwalPelajaran::count()], ['label' => 'Nilai Terisi', 'value' => NilaiTugas::whereNotNull('nilai')->count()]]);
        return compact('cards', 'jadwal', 'semester') + ['tahun' => $periode->tahunAjaran()];
    }
};
?>
<div>
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-sky-700">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="page-title mt-1">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="page-subtitle">Ringkasan kegiatan akademik sekolah hari ini.</p>
        </div>
        <div class="rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm"><span class="text-slate-500">Periode
                aktif</span>
            <p class="font-semibold text-sky-800">{{ $tahun?->nama ?? 'Belum diatur' }} ·
                {{ ucfirst($semester?->nama ?? '-') }}</p>
        </div>
    </div>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as $card)
            <article class="card card-body relative overflow-hidden">
                <div class="absolute -right-4 -top-8 size-24 rounded-full bg-sky-50"></div>
                <p class="relative text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                <p class="relative mt-3 text-3xl font-semibold tabular-nums text-slate-950">{{ $card['value'] }}</p>
            </article>
        @endforeach
    </section>
    <section class="card mt-6">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="font-semibold text-slate-950">Jadwal Hari Ini</h2>
                <p class="mt-1 text-sm text-slate-500">Pelajaran yang berlangsung berdasarkan hak akses Anda.</p>
            </div>
            @can('nilai.create')
                <a href="{{ route('nilai.index') }}" wire:navigate class="btn-primary">Input Nilai</a>
            @endcan
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jam</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Guru</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwal as $item)
                        <tr>
                            <td class="font-medium tabular-nums">
                                {{ substr($item->jamPelajaran->jam_mulai, 0, 5) }}–{{ substr($item->jamPelajaran->jam_selesai, 0, 5) }}
                            </td>
                            <td>{{ $item->pengajaran->mataPelajaran->nama }}</td>
                            <td>{{ $item->pengajaran->kelas->nama }}</td>
                            <td>{{ $item->pengajaran->guru->nama_lengkap }}</td>
                    </tr>@empty<tr>
                            <td colspan="4" class="py-12 text-center text-slate-500">Belum ada jadwal pada hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
