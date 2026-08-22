<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="SISDAR, Sistem Informasi Sekolah Dasar untuk pengelolaan akademik sekolah.">
    <title>SISDAR — Sistem Informasi Sekolah Dasar</title>
    <link rel="icon" href="{{ asset('images/favicon-logo-pendidikan/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon-logo-pendidikan/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-logo-pendidikan/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $school = \App\Models\PengaturanSekolah::first();
    $schoolName = $school?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah';
    $schoolLogo = asset('images/favicon-logo-pendidikan/favicon.svg');
    $schoolAddress = $school?->alamat ?? 'Kabupaten Maluku Tengah, Maluku';
    $teacherCount = \App\Models\Guru::where('status', 'aktif')->count();
    $studentCount = \App\Models\Siswa::where('status', 'aktif')->count();
    $classCount = \App\Models\Kelas::where('status', 'aktif')->count();
    $subjectCount = \App\Models\MataPelajaran::where('status', 'aktif')->count();
@endphp

<body class="sisdar-landing antialiased">
    <a href="#konten-utama" class="skip-link">Lewati ke konten utama</a>
    <header class="landing-header">
        <nav class="landing-container landing-nav" aria-label="Navigasi utama">
            <a href="#beranda" class="landing-brand" aria-label="SISDAR Beranda">
                <img src="{{ $schoolLogo }}" alt="Logo {{ $schoolName }}" width="50" height="60">
                <span><strong>SISDAR</strong><small>{{ $schoolName }}</small></span>
            </a>
            <div class="landing-menu" aria-label="Tautan halaman">
                <a class="active" href="#beranda">Beranda</a><a href="#fitur">Fitur</a><a href="#tentang">Tentang</a><a href="#kontak">Kontak</a>
            </div>
            <a href="{{ route('login') }}" class="landing-login"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H9"/></svg>Masuk Sistem</a>
        </nav>
    </header>

    <main id="konten-utama">
        <section id="beranda" class="landing-hero">
            <div class="hero-orb" aria-hidden="true"></div><div class="hero-dots" aria-hidden="true"></div>
            <div class="landing-container hero-grid">
                <div class="hero-copy">
                    <div class="hero-eyebrow"><span></span><strong>SISDAR</strong><i></i>Sistem Informasi Sekolah Dasar</div>
                    <h1>Kelola Jadwal Pelajaran dan<br>Nilai Siswa dengan <em>Lebih Mudah</em></h1>
                    <p>SISDAR membantu Admin, Guru, Siswa, dan Kepala Sekolah mengelola informasi akademik secara terintegrasi, akurat, dan efisien dalam satu platform.</p>
                    <div class="hero-actions">
                        <a href="{{ route('login') }}" class="hero-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H9"/></svg>Masuk Sistem</a>
                        <a href="#fitur" class="hero-secondary"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m9 7 8 5-8 5V7Z"/></svg>Lihat Demo</a>
                    </div>
                </div>

                <div class="dashboard-preview" aria-label="Pratinjau dashboard SISDAR">
                    <aside>
                        <div class="preview-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 5.5A2.5 2.5 0 0 1 5.5 3H11v17H5.5A2.5 2.5 0 0 0 3 22V5.5ZM21 5.5A2.5 2.5 0 0 0 18.5 3H13v17h5.5A2.5 2.5 0 0 1 21 22V5.5Z"/></svg>SISDAR</div>
                        @foreach ([['home','Beranda'],['calendar','Jadwal'],['grade','Nilai'],['students','Siswa'],['teacher','Guru'],['report','Laporan'],['settings','Pengaturan']] as [$icon,$label])
                            <div class="preview-nav {{ $loop->first ? 'selected' : '' }}"><x-nav-icon :name="$icon" />{{ $label }}</div>
                        @endforeach
                    </aside>
                    <div class="preview-main">
                        <div class="preview-top"><strong>Beranda</strong><span><i></i><b>A</b><small>Admin<br><em>Administrator</em></small></span></div>
                        <div class="preview-metrics">
                            @foreach ([['Jumlah Guru',$teacherCount ?: 28,'blue','teacher'],['Jumlah Siswa',$studentCount ?: 256,'green','students'],['Jumlah Kelas',$classCount ?: 12,'purple','class'],['Mata Pelajaran',$subjectCount ?: 18,'orange','book']] as [$label,$value,$color,$icon])
                                <div><small>{{ $label }}</small><strong>{{ $value }}</strong><em>{{ $label === 'Mata Pelajaran' ? 'Mapel' : ($label === 'Jumlah Kelas' ? 'Kelas' : 'Orang') }}</em><span class="{{ $color }}"><x-nav-icon :name="$icon" /></span></div>
                            @endforeach
                        </div>
                        <div class="preview-tables">
                            <div class="preview-table"><header><strong>Jadwal Hari Ini</strong><a>Lihat Semua</a></header><table><thead><tr><th>No</th><th>Waktu</th><th>Mata Pelajaran</th><th>Kelas</th><th>Guru</th></tr></thead><tbody><tr><td>1</td><td>07:30 - 08:10</td><td>Matematika</td><td>5A</td><td>Ibu Sulastri</td></tr><tr><td>2</td><td>08:20 - 09:00</td><td>Bahasa Indonesia</td><td>5A</td><td>Bapak Rahman</td></tr><tr><td>3</td><td>09:20 - 10:00</td><td>IPA</td><td>5A</td><td>Ibu Sulastri</td></tr><tr><td>4</td><td>10:10 - 10:50</td><td>PPKn</td><td>5A</td><td>Bapak Joko</td></tr></tbody></table></div>
                            <div class="preview-table"><header><strong>Nilai Terbaru</strong><a>Lihat Semua</a></header><table><thead><tr><th>Siswa</th><th>Mata Pelajaran</th><th>Nilai</th><th>Tanggal</th></tr></thead><tbody><tr><td>Andi Pratama</td><td>Matematika</td><td>85</td><td>20 Mei 2024</td></tr><tr><td>Siti Aisyah</td><td>Bahasa Indonesia</td><td>90</td><td>20 Mei 2024</td></tr><tr><td>Muhammad Rizky</td><td>IPA</td><td>88</td><td>20 Mei 2024</td></tr><tr><td>Fatimah Zahra</td><td>PPKn</td><td>92</td><td>20 Mei 2024</td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="fitur" class="landing-content landing-container">
            <div class="feature-column"><h2>Fitur Unggulan SISDAR</h2><div class="feature-grid">
                @foreach ([['calendar','Jadwal Pelajaran','Kelola jadwal pelajaran secara terstruktur dan mudah diperbarui.'],['grade','Nilai Mingguan','Input dan pantau nilai siswa setiap minggu dengan praktis.'],['report','Laporan PDF','Generate laporan nilai dan rekapitulasi dalam format PDF.'],['students','Multi Role Access','Akses sistem sesuai peran untuk keamanan dan kenyamanan.']] as [$icon,$title,$copy])
                    <article class="feature-card"><span class="blue"><x-nav-icon :name="$icon" /></span><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>
                @endforeach
            </div></div>
            <div id="tentang" class="role-column"><h2>Akses Sesuai Peran</h2><div class="role-grid">
                @foreach ([['Admin','Kelola data master, pengguna, dan konfigurasi sistem.','Akses Penuh','blue','A'],['Guru','Kelola jadwal, input nilai, dan pantau perkembangan siswa.','Akses Terbatas','green','G'],['Siswa','Lihat jadwal pelajaran dan nilai akademik secara pribadi.','Akses Terbatas','purple','S'],['Kepala Sekolah','Pantau laporan akademik dan kinerja sekolah secara menyeluruh.','Akses Laporan','orange','K']] as [$title,$copy,$access,$color,$initial])
                    <article class="role-card"><div class="role-avatar {{ $color }}">{{ $initial }}</div><div><h3>{{ $title }}</h3><p>{{ $copy }}</p><span class="{{ $color }}">{{ $access }}</span></div></article>
                @endforeach
            </div></div>
        </section>

        <section class="landing-stats landing-container" aria-label="Statistik sekolah">
            @foreach ([['teacher',$teacherCount ?: 28,'Jumlah Guru','Tenaga pendidik aktif','blue'],['students',$studentCount ?: 256,'Jumlah Siswa','Siswa aktif','green'],['class',$classCount ?: 12,'Jumlah Kelas','Rombel tersedia','purple'],['book',$subjectCount ?: 18,'Mata Pelajaran','Mapel yang diajarkan','orange']] as [$icon,$value,$label,$copy,$color])
                <div class="stat-item"><span class="{{ $color }}"><x-nav-icon :name="$icon" /></span><strong>{{ $value }}</strong><div><b>{{ $label }}</b><small>{{ $copy }}</small></div></div>
            @endforeach
        </section>
    </main>

    <footer id="kontak" class="landing-footer">
        <div class="landing-container footer-main">
            <div class="footer-brand"><img src="{{ $schoolLogo }}" alt="" width="46" height="56"><div><strong>SISDAR</strong><small>{{ $schoolName }}</small></div></div>
            <p>SISDAR adalah Sistem Informasi Sekolah Dasar terpadu untuk pengelolaan jadwal pelajaran dan nilai siswa secara efektif dan efisien.</p>
            <div><h3>Tautan Cepat</h3><a href="#beranda">Beranda</a><a href="#fitur">Fitur</a><a href="#tentang">Tentang</a><a href="#kontak">Kontak</a></div>
            <div><h3>Bantuan</h3><a href="{{ route('bantuan') }}" target="_blank" rel="noopener">Panduan Penggunaan</a><a href="#kontak">FAQ</a><a href="#kontak">Hubungi Kami</a></div>
            <div><h3>Kontak</h3><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2"/></svg>{{ $schoolName }}<br>{{ $schoolAddress }}</span><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 5h18v14H3zM3 6l9 7 9-7"/></svg>info@sisdar.sch.id</span></div>
        </div>
        <div class="landing-container footer-bottom"><span>&copy; {{ now()->year }} SISDAR - {{ $schoolName }}. All rights reserved.</span><span>Dibuat dengan <b>♥</b> untuk pendidikan Indonesia</span></div>
    </footer>
</body>
</html>
