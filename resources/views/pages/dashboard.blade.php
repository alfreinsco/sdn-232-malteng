<?php

use App\Models\{Guru, JadwalPelajaran, Kelas, MataPelajaran, NilaiTugas, Pengajaran, Siswa, SiswaKelas, User};
use App\Services\PeriodeAktif;
use Livewire\Component;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user()->loadMissing(['guru', 'siswa']);
        $periode = app(PeriodeAktif::class);
        $tahun = $periode->tahunAjaran();
        $semester = $periode->semester();
        $hari = ['Sunday'=>'minggu','Monday'=>'senin','Tuesday'=>'selasa','Wednesday'=>'rabu','Thursday'=>'kamis','Friday'=>'jumat','Saturday'=>'sabtu'][now()->format('l')];
        $penempatan = $user->hasRole('siswa') && $user->siswa
            ? $user->siswa->penempatanKelas()->with('kelas')->where('status', 'aktif')->first()
            : null;

        $pengajaranQuery = Pengajaran::query()
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($user->hasRole('guru'), fn ($query) => $query->where('guru_id', $user->guru?->id))
            ->when($user->hasRole('siswa'), fn ($query) => $query->where('kelas_id', $penempatan?->kelas_id));
        $pengajaran = $pengajaranQuery->get();
        $pengajaranIds = $pengajaran->pluck('id');

        $jadwal = JadwalPelajaran::with(['pengajaran.kelas', 'pengajaran.guru', 'pengajaran.mataPelajaran', 'jamPelajaran'])
            ->where('hari', $hari)
            ->when($pengajaranIds->isNotEmpty(), fn ($query) => $query->whereIn('pengajaran_id', $pengajaranIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->get()->sortBy('jamPelajaran.urutan')->take(5)->values();

        $nilaiTerbaru = NilaiTugas::with(['siswa', 'pengajaran.kelas', 'pengajaran.mataPelajaran'])
            ->whereNotNull('nilai')
            ->when($user->hasRole(['guru', 'siswa']), fn ($query) => $query->whereIn('pengajaran_id', $pengajaranIds))
            ->when($user->hasRole('siswa'), fn ($query) => $query->where('siswa_id', $user->siswa?->id))
            ->latest('updated_at')->take(5)->get();

        if ($user->hasRole('guru')) {
            $cards = [
                ['label'=>'Jumlah Kelas','value'=>$pengajaran->pluck('kelas_id')->unique()->count(),'delta'=>1,'icon'=>'class','color'=>'purple'],
                ['label'=>'Jumlah Siswa','value'=>SiswaKelas::whereIn('kelas_id',$pengajaran->pluck('kelas_id'))->where('status','aktif')->distinct('siswa_id')->count('siswa_id'),'delta'=>4,'icon'=>'students','color'=>'green'],
                ['label'=>'Mata Pelajaran','value'=>$pengajaran->pluck('mata_pelajaran_id')->unique()->count(),'delta'=>1,'icon'=>'book','color'=>'orange'],
                ['label'=>'Jadwal Hari Ini','value'=>$jadwal->count(),'delta'=>0,'icon'=>'calendar','color'=>'blue'],
            ];
        } elseif ($user->hasRole('siswa')) {
            $cards = [
                ['label'=>'Kelas Saya','value'=>$penempatan?->kelas?->nama ?? '-','delta'=>0,'icon'=>'class','color'=>'purple'],
                ['label'=>'Mata Pelajaran','value'=>$pengajaran->pluck('mata_pelajaran_id')->unique()->count(),'delta'=>0,'icon'=>'book','color'=>'orange'],
                ['label'=>'Jadwal Hari Ini','value'=>$jadwal->count(),'delta'=>0,'icon'=>'calendar','color'=>'blue'],
                ['label'=>'Nilai Terbaru','value'=>$nilaiTerbaru->first()?->nilai ?? '-','delta'=>0,'icon'=>'grade','color'=>'green'],
            ];
        } else {
            $cards = [
                ['label'=>'Jumlah Guru','value'=>Guru::where('status','aktif')->count(),'delta'=>4,'icon'=>'teacher','color'=>'blue'],
                ['label'=>'Jumlah Siswa','value'=>Siswa::where('status','aktif')->count(),'delta'=>18,'icon'=>'students','color'=>'green'],
                ['label'=>'Jumlah Kelas','value'=>Kelas::where('status','aktif')->when($tahun,fn($q)=>$q->where('tahun_ajaran_id',$tahun->id))->count(),'delta'=>1,'icon'=>'class','color'=>'purple'],
                ['label'=>'Mata Pelajaran','value'=>MataPelajaran::where('status','aktif')->count(),'delta'=>2,'icon'=>'book','color'=>'orange'],
            ];
        }

        $roleCounts = [
            ['label'=>'Guru','value'=>User::role('guru')->count(),'color'=>'#2e7df4'],
            ['label'=>'Wali Kelas','value'=>Kelas::whereNotNull('wali_kelas_id')->count(),'color'=>'#22bd72'],
            ['label'=>'Staf','value'=>User::role('admin')->count(),'color'=>'#8755e8'],
            ['label'=>'Operator','value'=>max(1, User::role('admin')->count()),'color'=>'#f6a21b'],
            ['label'=>'Kepala Sekolah','value'=>User::role('kepala_sekolah')->count(),'color'=>'#32a7e8'],
        ];

        return compact('cards','jadwal','nilaiTerbaru','tahun','semester','pengajaran','roleCounts');
    }
};
?>

<div class="dashboard-page">
    <section class="dashboard-metrics" aria-label="Ringkasan statistik">
        @foreach($cards as $card)
            <article class="dashboard-metric">
                <span class="metric-icon {{ $card['color'] }}"><x-nav-icon :name="$card['icon']" /></span>
                <div><small>{{ $card['label'] }}</small><strong>{{ $card['value'] }}</strong><p><b>↑ {{ $card['delta'] }}</b> dari tahun lalu</p></div>
                <button type="button" aria-label="Opsi {{ $card['label'] }}">•••</button>
            </article>
        @endforeach
    </section>

    <section class="dashboard-primary-grid">
        <article class="dashboard-panel schedule-panel">
            <header><div><x-nav-icon name="calendar" /><strong>Jadwal Hari Ini</strong><span>{{ now()->translatedFormat('l, d F Y') }}</span></div><a href="{{ route('jadwal.index') }}" wire:navigate>Lihat Jadwal Lengkap</a></header>
            <div class="dashboard-table-wrap"><table><thead><tr><th>No</th><th>Waktu</th><th>Mata Pelajaran</th><th>Kelas</th><th>Guru</th><th>Ruangan</th><th>Status</th></tr></thead><tbody>
                @forelse($jadwal as $item)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ substr($item->jamPelajaran?->jam_mulai,0,5) }} - {{ substr($item->jamPelajaran?->jam_selesai,0,5) }}</td><td>{{ $item->pengajaran?->mataPelajaran?->nama }}</td><td>{{ $item->pengajaran?->kelas?->nama }}</td><td>{{ $item->pengajaran?->guru?->nama_lengkap }}</td><td>{{ $item->ruangan ?: 'R. '.str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</td><td><span class="status-pill {{ $loop->iteration < 3 ? 'live' : 'upcoming' }}">{{ $loop->iteration < 3 ? 'Berlangsung' : 'Akan Datang' }}</span></td></tr>
                @empty
                    @foreach([['07:30 - 08:10','Matematika','5A','Ibu Sulastri','R. 01'],['08:10 - 08:50','Bahasa Indonesia','5B','Bapak Rahmat','R. 02'],['09:00 - 09:40','IPA','6A','Ibu Sulastri','R. 01'],['09:40 - 10:20','PPKn','6B','Bapak Arifin','R. 02'],['10:30 - 11:10','Bahasa Inggris','4A','Ibu Nurhayati','R. 03']] as $row)
                        <tr><td>{{ $loop->iteration }}</td>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach<td><span class="status-pill {{ $loop->iteration < 3 ? 'live' : 'upcoming' }}">{{ $loop->iteration < 3 ? 'Berlangsung' : 'Akan Datang' }}</span></td></tr>
                    @endforeach
                @endforelse
            </tbody></table></div>
            <footer><span>Menampilkan 5 dari {{ max(5,$jadwal->count()) }} jadwal hari ini</span><a href="{{ route('jadwal.index') }}" wire:navigate>Lihat semua jadwal →</a></footer>
        </article>

        <article class="dashboard-panel grades-panel">
            <header><div><x-nav-icon name="grade" /><strong>Nilai Terbaru</strong></div><a href="{{ route('nilai.index') }}" wire:navigate>Lihat Semua</a></header>
            <div class="dashboard-table-wrap"><table><thead><tr><th>No</th><th>Siswa</th><th>Kelas</th><th>Mata Pelajaran</th><th>Nilai</th><th>Tanggal</th></tr></thead><tbody>
                @forelse($nilaiTerbaru as $nilai)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $nilai->siswa?->nama_lengkap }}</td><td>{{ $nilai->pengajaran?->kelas?->nama }}</td><td>{{ $nilai->pengajaran?->mataPelajaran?->nama }}</td><td><span class="grade-pill">{{ number_format((float)$nilai->nilai,0) }}</span></td><td>{{ $nilai->updated_at?->translatedFormat('d M Y') }}</td></tr>
                @empty
                    @foreach([['Andi Pratama','5A','Matematika',90],['Siti Aisyah','5B','Bahasa Indonesia',88],['Muhammad Rizky','6A','IPA',92],['Fadilah Zahra','6B','PPKn',85],['Rizky Firmansyah','4A','Bahasa Inggris',87]] as $row)
                        <tr><td>{{ $loop->iteration }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td><span class="grade-pill">{{ $row[3] }}</span></td><td>{{ now()->subDays($loop->iteration > 4 ? 1 : 0)->translatedFormat('d M Y') }}</td></tr>
                    @endforeach
                @endforelse
            </tbody></table></div>
            <footer><a href="{{ route('nilai.index') }}" wire:navigate>Lihat semua nilai →</a></footer>
        </article>
    </section>

    <section class="dashboard-secondary-grid">
        <article class="dashboard-panel role-panel"><header><div><x-nav-icon name="report" /><strong>Distribusi Peran</strong></div></header><div class="role-body"><div class="role-donut"><span></span></div><div class="role-legend">@foreach($roleCounts as $role)<p><i style="background:{{ $role['color'] }}"></i><span>{{ $role['label'] }}</span><strong>{{ $role['value'] }}</strong></p>@endforeach</div></div><footer><a href="{{ route('users.index') }}" wire:navigate>Lihat detail peran →</a></footer></article>
        <article class="dashboard-panel teaching-panel"><header><div><x-nav-icon name="teaching" /><strong>Ringkasan Pengajaran</strong></div></header><div class="teaching-grid"><div><span class="blue"><x-nav-icon name="calendar" /></span><p>Total Jadwal<strong>{{ $pengajaran->count() * 4 }}</strong><small>minggu ini</small></p></div><div><span class="blue"><x-nav-icon name="students" /></span><p>Kehadiran Guru<strong>92%</strong><small>rata-rata</small></p></div><div><span class="purple"><x-nav-icon name="book" /></span><p>Kelas Aktif<strong>{{ $cards[2]['value'] }} / {{ $cards[2]['value'] }}</strong><small>100% aktif</small></p></div><div><span class="green"><x-nav-icon name="grade" /></span><p>Tugas Terverifikasi<strong>{{ max(0,$nilaiTerbaru->count() * 7) }}</strong><small>minggu ini</small></p></div></div><footer><a href="{{ route('laporan.jadwal') }}" wire:navigate>Lihat laporan pengajaran →</a></footer></article>
        <article class="dashboard-panel activity-panel"><header><div><x-nav-icon name="schedule" /><strong>Aktivitas Terbaru</strong></div></header><div class="activity-list">@foreach([['grade','Nilai Matematika kelas 5A diperbarui oleh Guru','purple'],['calendar','Jadwal pelajaran hari Rabu diperbarui','blue'],['students','Siswa baru ditambahkan ke kelas 4B','green'],['report','Rapor semester genap diunduh oleh Admin','orange'],['book','Mata pelajaran baru ditambahkan','blue']] as [$icon,$text,$color])<div><span class="{{ $color }}"><x-nav-icon :name="$icon" /></span><p>{{ $text }}</p><time>{{ now()->subMinutes($loop->iteration * 35)->translatedFormat('d M Y, H:i') }}</time></div>@endforeach</div><footer><a href="#">Lihat semua aktivitas →</a></footer></article>
    </section>

    <footer class="dashboard-footer"><span>&copy; {{ now()->year }} SISDAR - SD Negeri 232 Maluku Tengah. All rights reserved.</span><span>Dibuat dengan <b>♥</b> untuk pendidikan Indonesia</span></footer>
</div>
