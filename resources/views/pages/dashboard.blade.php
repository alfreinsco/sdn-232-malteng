<?php

use App\Models\{Guru, JadwalPelajaran, Kelas, MataPelajaran, NilaiTugas, Pengajaran, Siswa, SiswaKelas};
use App\Services\PeriodeAktif;
use Livewire\Component;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user()->loadMissing(['guru', 'siswa']);
        $periode = app(PeriodeAktif::class);
        $tahun = $periode->tahunAjaran();
        $semester = $periode->semester();
        $bulan = now()->month;
        $hari = [
            'Sunday' => 'minggu', 'Monday' => 'senin', 'Tuesday' => 'selasa',
            'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat',
            'Saturday' => 'sabtu',
        ][now()->format('l')];

        $penempatan = null;
        if ($user->hasRole('siswa') && $user->siswa) {
            $penempatan = $user->siswa->penempatanKelas()
                ->with(['kelas.waliKelas'])
                ->where('status', 'aktif')
                ->when($tahun, fn ($query) => $query->whereHas('kelas', fn ($kelas) => $kelas->where('tahun_ajaran_id', $tahun->id)))
                ->first();
        }

        $pengajaranQuery = Pengajaran::query()
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($user->hasRole('guru'), fn ($query) => $query->where('guru_id', $user->guru?->id))
            ->when($user->hasRole('siswa'), fn ($query) => $query->where('kelas_id', $penempatan?->kelas_id));

        $pengajaranAktif = (clone $pengajaranQuery)->get(['id', 'kelas_id', 'mata_pelajaran_id', 'guru_id']);
        $pengajaranIds = $pengajaranAktif->pluck('id');

        $jadwalQuery = JadwalPelajaran::with(['pengajaran.kelas', 'pengajaran.guru', 'pengajaran.mataPelajaran', 'jamPelajaran'])
            ->where('hari', $hari)
            ->when($pengajaranIds->isNotEmpty(), fn ($query) => $query->whereIn('pengajaran_id', $pengajaranIds), fn ($query) => $query->whereRaw('1 = 0'));
        $jadwal = $jadwalQuery->get()->sortBy('jamPelajaran.urutan')->values();

        $nilaiBulanIni = NilaiTugas::query()
            ->whereIn('pengajaran_id', $pengajaranIds)
            ->where('bulan', $bulan)
            ->when($user->hasRole('siswa'), fn ($query) => $query->where('siswa_id', $user->siswa?->id));
        $nilaiTerisi = (clone $nilaiBulanIni)->whereNotNull('nilai')->count();

        $jumlahSiswaPerKelas = SiswaKelas::query()
            ->where('status', 'aktif')
            ->whereIn('kelas_id', $pengajaranAktif->pluck('kelas_id')->unique())
            ->select(['kelas_id', 'siswa_id'])
            ->get()
            ->groupBy('kelas_id')
            ->map(fn ($items) => $items->pluck('siswa_id')->unique()->count());
        $targetNilai = $user->hasRole('siswa')
            ? $pengajaranAktif->count() * 4
            : $pengajaranAktif->sum(fn ($item) => ($jumlahSiswaPerKelas[$item->kelas_id] ?? 0) * 4);
        $progresNilai = $targetNilai > 0 ? min(100, round(($nilaiTerisi / $targetNilai) * 100)) : 0;

        if ($user->hasRole('guru')) {
            $jumlahSiswa = $jumlahSiswaPerKelas->sum();
            $cards = [
                ['label' => 'Kelas Diajar', 'value' => $pengajaranAktif->pluck('kelas_id')->unique()->count(), 'description' => 'Pada semester aktif', 'icon' => 'class'],
                ['label' => 'Mata Pelajaran', 'value' => $pengajaranAktif->pluck('mata_pelajaran_id')->unique()->count(), 'description' => 'Tanggung jawab mengajar', 'icon' => 'book'],
                ['label' => 'Siswa Terjangkau', 'value' => $jumlahSiswa, 'description' => 'Dari seluruh kelas diajar', 'icon' => 'students'],
                ['label' => 'Jadwal Hari Ini', 'value' => $jadwal->count(), 'description' => 'Sesi mengajar hari ini', 'icon' => 'calendar'],
            ];
            $insights = [
                ['label' => 'Progres nilai '.now()->translatedFormat('F'), 'value' => $progresNilai.'%', 'description' => number_format($nilaiTerisi).' dari '.number_format($targetNilai).' nilai mingguan telah terisi', 'progress' => $progresNilai],
                ['label' => 'Pengajaran aktif', 'value' => $pengajaranAktif->count(), 'description' => 'Kombinasi kelas dan mata pelajaran semester ini'],
            ];
            $quickActions = [
                ['label' => 'Input Nilai', 'description' => 'Isi nilai Minggu 1-4 secara massal', 'route' => 'nilai.index', 'icon' => 'grade'],
                ['label' => 'Jadwal Mengajar', 'description' => 'Lihat seluruh jadwal semester aktif', 'route' => 'jadwal.index', 'icon' => 'calendar'],
                ['label' => 'Laporan Nilai', 'description' => 'Pratinjau, cetak, atau unduh PDF', 'route' => 'laporan.nilai', 'icon' => 'report'],
            ];
        } elseif ($user->hasRole('siswa')) {
            $rataRata = (clone $nilaiBulanIni)->whereNotNull('nilai')->avg('nilai');
            $cards = [
                ['label' => 'Kelas Saya', 'value' => $penempatan?->kelas?->nama ?? '-', 'description' => $penempatan?->kelas?->waliKelas?->nama_lengkap ? 'Wali: '.$penempatan->kelas->waliKelas->nama_lengkap : 'Wali kelas belum ditentukan', 'icon' => 'class'],
                ['label' => 'Mata Pelajaran', 'value' => $pengajaranAktif->pluck('mata_pelajaran_id')->unique()->count(), 'description' => 'Pada semester aktif', 'icon' => 'book'],
                ['label' => 'Jadwal Hari Ini', 'value' => $jadwal->count(), 'description' => 'Pelajaran untuk kelas Anda', 'icon' => 'calendar'],
                ['label' => 'Rata-rata Bulan Ini', 'value' => $rataRata === null ? '-' : number_format((float) $rataRata, 2, ',', '.'), 'description' => now()->translatedFormat('F Y'), 'icon' => 'grade'],
            ];
            $insights = [
                ['label' => 'Nilai sudah tersedia', 'value' => $nilaiTerisi, 'description' => 'Dari '.$targetNilai.' nilai mingguan bulan ini', 'progress' => $progresNilai],
                ['label' => 'Status kelas', 'value' => $penempatan ? 'Aktif' : 'Belum ditempatkan', 'description' => $penempatan?->kelas?->nama ? 'Terdaftar di kelas '.$penempatan->kelas->nama : 'Hubungi administrator sekolah'],
            ];
            $quickActions = [
                ['label' => 'Nilai Saya', 'description' => 'Lihat nilai Minggu 1-4 dan rata-rata', 'route' => 'nilai.index', 'icon' => 'grade'],
                ['label' => 'Jadwal Pelajaran', 'description' => 'Lihat jadwal lengkap kelas Anda', 'route' => 'jadwal.index', 'icon' => 'calendar'],
                ['label' => 'Profil Saya', 'description' => 'Periksa identitas dan keamanan akun', 'route' => 'profile.edit', 'icon' => 'profile'],
            ];
        } else {
            $jumlahSiswaAktif = Siswa::where('status', 'aktif')->count();
            $siswaDitempatkan = SiswaKelas::query()
                ->where('status', 'aktif')
                ->when($tahun, fn ($query) => $query->whereHas('kelas', fn ($kelas) => $kelas->where('tahun_ajaran_id', $tahun->id)))
                ->distinct('siswa_id')
                ->count('siswa_id');
            $cards = [
                ['label' => 'Guru Aktif', 'value' => Guru::where('status', 'aktif')->count(), 'description' => 'Tenaga pengajar terdaftar', 'icon' => 'teacher'],
                ['label' => 'Siswa Aktif', 'value' => $jumlahSiswaAktif, 'description' => $siswaDitempatkan.' sudah ditempatkan', 'icon' => 'students'],
                ['label' => 'Kelas Aktif', 'value' => Kelas::where('status', 'aktif')->when($tahun, fn ($query) => $query->where('tahun_ajaran_id', $tahun->id))->count(), 'description' => 'Pada tahun ajaran aktif', 'icon' => 'class'],
                ['label' => 'Mata Pelajaran', 'value' => MataPelajaran::where('status', 'aktif')->count(), 'description' => 'Master mata pelajaran aktif', 'icon' => 'book'],
                ['label' => 'Pengajaran', 'value' => $pengajaranAktif->count(), 'description' => 'Penugasan semester aktif', 'icon' => 'teaching'],
                ['label' => 'Jadwal Hari Ini', 'value' => $jadwal->count(), 'description' => 'Seluruh sesi sekolah hari ini', 'icon' => 'calendar'],
            ];
            $belumDitempatkan = max(0, $jumlahSiswaAktif - $siswaDitempatkan);
            $insights = [
                ['label' => 'Kelengkapan nilai '.now()->translatedFormat('F'), 'value' => $progresNilai.'%', 'description' => number_format($nilaiTerisi).' dari '.number_format($targetNilai).' nilai mingguan telah terisi', 'progress' => $progresNilai],
                ['label' => 'Penempatan siswa', 'value' => $belumDitempatkan === 0 ? 'Lengkap' : $belumDitempatkan.' belum ditempatkan', 'description' => $siswaDitempatkan.' dari '.$jumlahSiswaAktif.' siswa aktif memiliki kelas'],
                ['label' => 'Periode akademik', 'value' => $semester ? ucfirst($semester->nama) : 'Belum aktif', 'description' => $semester ? $semester->tanggal_mulai->translatedFormat('d M Y').' - '.$semester->tanggal_selesai->translatedFormat('d M Y') : 'Aktifkan semester untuk memulai kegiatan akademik'],
            ];
            $quickActions = $user->hasRole('admin') ? [
                ['label' => 'Tempatkan Siswa', 'description' => 'Atur anggota kelas tahun ajaran aktif', 'route' => 'siswa-kelas.index', 'icon' => 'students'],
                ['label' => 'Atur Pengajaran', 'description' => 'Hubungkan guru, kelas, dan mapel', 'route' => 'pengajaran.index', 'icon' => 'teaching'],
                ['label' => 'Kelola Jadwal', 'description' => 'Susun jadwal tanpa bentrok', 'route' => 'jadwal.index', 'icon' => 'calendar'],
                ['label' => 'Monitoring Nilai', 'description' => 'Pantau kelengkapan nilai siswa', 'route' => 'nilai.index', 'icon' => 'grade'],
            ] : [
                ['label' => 'Monitoring Jadwal', 'description' => 'Pantau kegiatan belajar sekolah', 'route' => 'jadwal.index', 'icon' => 'calendar'],
                ['label' => 'Monitoring Nilai', 'description' => 'Lihat perkembangan nilai siswa', 'route' => 'nilai.index', 'icon' => 'grade'],
                ['label' => 'Laporan Jadwal', 'description' => 'Cetak jadwal sesuai periode', 'route' => 'laporan.jadwal', 'icon' => 'report'],
                ['label' => 'Laporan Nilai', 'description' => 'Tinjau rekap nilai akademik', 'route' => 'laporan.nilai', 'icon' => 'report'],
            ];
        }

        return compact('cards', 'insights', 'quickActions', 'jadwal', 'semester', 'tahun', 'hari');
    }
};
?>

<div class="space-y-6">
    <section class="content-hero flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative z-10 min-w-0">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm font-semibold text-sky-700">
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                <span class="size-1 rounded-full bg-sky-300" aria-hidden="true"></span>
                <span class="capitalize">{{ $hari }}</span>
            </div>
            <h1 class="page-title">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="page-subtitle">Pantau kegiatan akademik, pekerjaan penting, dan perkembangan terbaru dari satu tempat.</p>
        </div>
        <div class="relative z-10 grid shrink-0 gap-2 sm:grid-cols-2 lg:min-w-[22rem]">
            <div class="rounded-xl border border-sky-200 bg-sky-50/90 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Tahun Ajaran</p>
                <p class="mt-1 font-bold text-slate-900">{{ $tahun?->nama ?? 'Belum diatur' }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-white/90 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Semester Aktif</p>
                <p class="mt-1 font-bold text-slate-900">{{ ucfirst($semester?->nama ?? 'Belum diatur') }}</p>
            </div>
        </div>
    </section>

    <section aria-labelledby="ringkasan-dashboard">
        <div class="mb-3 flex items-end justify-between gap-4">
            <div>
                <h2 id="ringkasan-dashboard" class="text-lg font-bold text-slate-950">Ringkasan Utama</h2>
                <p class="mt-1 text-sm text-slate-500">Data semester aktif yang paling sering dibutuhkan.</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($cards as $card)
                <article class="card metric-card card-body flex items-start gap-4">
                    <span class="relative grid size-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                        @switch($card['icon'])
                            @case('teacher') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M6 20v-2a6 6 0 0 1 12 0v2M18 5l2 2-2 2"/></svg> @break
                            @case('students') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4.6V20"/></svg> @break
                            @case('class') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg> @break
                            @case('book') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5a3 3 0 0 1 3-3h13v17H7a3 3 0 0 0-3 3V5Z"/><path d="M7 19h13"/></svg> @break
                            @case('teaching') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h18v12H3zM8 20h8M12 16v4M7 9l3 2 4-4"/></svg> @break
                            @case('grade') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h3"/></svg> @break
                            @default <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                        @endswitch
                    </span>
                    <div class="relative min-w-0">
                        <p class="text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums text-slate-950">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $card['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(18rem,0.85fr)]">
        <div class="card min-w-0 overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-slate-950">Jadwal Hari Ini</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $jadwal->count() }} sesi ditemukan berdasarkan akses Anda.</p>
                </div>
                <a href="{{ route('jadwal.index') }}" wire:navigate class="btn-secondary shrink-0">Lihat Jadwal Lengkap</a>
            </div>
            <div class="table-scroll max-h-[28rem]">
                <table class="data-table">
                    <thead><tr><th>Jam</th><th>Mata Pelajaran</th><th>Kelas</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($jadwal as $item)
                            @php
                                $mulai = substr($item->jamPelajaran->jam_mulai, 0, 5);
                                $selesai = substr($item->jamPelajaran->jam_selesai, 0, 5);
                                $sekarang = now()->format('H:i');
                                $status = $sekarang < $mulai ? 'Akan datang' : ($sekarang <= $selesai ? 'Berlangsung' : 'Selesai');
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap font-semibold tabular-nums">{{ $mulai }}-{{ $selesai }}</td>
                                <td><p class="font-semibold text-slate-800">{{ $item->pengajaran->mataPelajaran->nama }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $item->pengajaran->guru->nama_lengkap }}</p></td>
                                <td>{{ $item->pengajaran->kelas->nama }}</td>
                                <td><span class="{{ $status === 'Berlangsung' ? 'badge-active' : ($status === 'Akan datang' ? 'inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700' : 'badge-inactive') }}">{{ $status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-14 text-center"><div class="mx-auto grid size-12 place-items-center rounded-full bg-sky-50 text-sky-600"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg></div><p class="mt-3 font-semibold text-slate-700">Belum ada jadwal hari ini</p><p class="mt-1 text-sm text-slate-500">Gunakan tombol di atas untuk melihat jadwal hari lain.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="card p-5">
            <div class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg></span>
                <div><h2 class="font-bold text-slate-950">Status Akademik</h2><p class="mt-0.5 text-xs text-slate-500">Indikator yang perlu diperhatikan</p></div>
            </div>
            <div class="mt-5 space-y-4">
                @foreach($insights as $insight)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="flex items-start justify-between gap-3"><p class="text-sm font-semibold text-slate-600">{{ $insight['label'] }}</p><p class="shrink-0 font-bold tabular-nums text-slate-950">{{ $insight['value'] }}</p></div>
                        @isset($insight['progress'])
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-label="{{ $insight['label'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $insight['progress'] }}"><div class="h-full rounded-full bg-sky-600 transition-all" style="width: {{ $insight['progress'] }}%"></div></div>
                        @endisset
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ $insight['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </aside>
    </section>

    <section>
        <div class="mb-3"><h2 class="text-lg font-bold text-slate-950">Akses Cepat</h2><p class="mt-1 text-sm text-slate-500">Buka pekerjaan utama tanpa mencari menu di sidebar.</p></div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($quickActions as $action)
                <a href="{{ route($action['route']) }}" wire:navigate class="group card flex min-h-28 items-center gap-4 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-sky-600 text-white shadow-sm shadow-sky-200 transition group-hover:bg-sky-700">
                        @if($action['icon'] === 'calendar')<svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                        @elseif($action['icon'] === 'grade')<svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h3"/></svg>
                        @elseif($action['icon'] === 'report')<svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 2h9l4 4v16H6zM14 2v5h5M9 12h7M9 16h7"/></svg>
                        @elseif($action['icon'] === 'profile')<svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M5 21a7 7 0 0 1 14 0"/></svg>
                        @elseif($action['icon'] === 'teaching')<svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h18v12H3zM8 20h8M12 16v4M7 9l3 2 4-4"/></svg>
                        @else<svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4.6V20"/></svg>@endif
                    </span>
                    <span class="min-w-0"><span class="flex items-center gap-2 font-bold text-slate-900">{{ $action['label'] }}<svg viewBox="0 0 24 24" class="size-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-sky-600 motion-reduce:transform-none" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></span><span class="mt-1 block text-xs leading-5 text-slate-500">{{ $action['description'] }}</span></span>
                </a>
            @endforeach
        </div>
    </section>
</div>
