<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'SISDAR' }} — {{ config('app.name') }}</title>
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
    $role = auth()->user()->getRoleNames()->first();
    $roleLabel = match ($role) {
        'admin' => 'Super Admin', 'guru' => 'Guru', 'siswa' => 'Siswa', 'kepala_sekolah' => 'Kepala Sekolah',
        default => str($role)->replace('_', ' ')->title(),
    };
    $pageTitle = match (true) {
        request()->routeIs('dashboard') => 'Dashboard Admin', request()->routeIs('tahun-ajaran.*') => 'Tahun Ajaran',
        request()->routeIs('semester.*') => 'Semester', request()->routeIs('kelas.siswa.*') => 'Anggota Kelas',
        request()->routeIs('kelas.*') => 'Kelas', request()->routeIs('mata-pelajaran.*') => 'Mata Pelajaran',
        request()->routeIs('jam-pelajaran.*') => 'Jam Pelajaran', request()->routeIs('guru.*') => 'Guru',
        request()->routeIs('siswa-kelas.*') => 'Penempatan Siswa', request()->routeIs('siswa.*') => 'Siswa',
        request()->routeIs('pengajaran.*') => 'Pengajaran', request()->routeIs('jadwal.*') => 'Jadwal Pelajaran',
        request()->routeIs('nilai.*') => 'Nilai Siswa', request()->routeIs('users.*') => 'Pengguna',
        request()->routeIs('pengaturan.*') => 'Pengaturan Sekolah', request()->routeIs('laporan.jadwal') => 'Laporan Jadwal',
        request()->routeIs('laporan.nilai') => 'Laporan Nilai', request()->routeIs('profile.*') => 'Profil', default => 'SISDAR',
    };
    if ($role !== 'admin' && request()->routeIs('dashboard')) $pageTitle = 'Dashboard '.str($roleLabel)->title();
@endphp

<body class="sisdar-app" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <div class="app-shell">
        <div x-show="sidebarOpen" x-transition.opacity x-cloak class="app-overlay" @click="sidebarOpen=false" aria-hidden="true"></div>
        <aside id="sidebar" class="app-sidebar" :class="sidebarOpen && 'open'" aria-label="Navigasi utama">
            <div class="sidebar-brand">
                <img src="{{ $schoolLogo }}" alt="Logo {{ $schoolName }}" width="61" height="72">
                <div><strong>SISDAR</strong><span>{{ $schoolName }}</span></div>
                <button type="button" @click="sidebarOpen=false" aria-label="Tutup menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            </div>
            <nav class="sidebar-nav">
                <x-nav-link route="dashboard" label="Dashboard" icon="home" />
                @role('admin')
                    <x-nav-group label="Akademik">
                        <x-nav-link route="tahun-ajaran.index" label="Tahun Ajaran" icon="calendar" />
                        <x-nav-link route="semester.index" label="Semester" icon="semester" />
                        <x-nav-link route="kelas.index" label="Kelas" icon="class" />
                        <x-nav-link route="mata-pelajaran.index" label="Mata Pelajaran" icon="book" />
                        <x-nav-link route="jam-pelajaran.index" label="Jam Pelajaran" icon="clock" />
                        <x-nav-link route="guru.index" label="Guru" icon="teacher" />
                        <x-nav-link route="siswa.index" label="Siswa" icon="students" />
                    </x-nav-group>
                    <x-nav-group label="Proses Belajar">
                        <x-nav-link route="pengajaran.index" label="Pengajaran" icon="teaching" />
                        <x-nav-link route="jadwal.index" label="Jadwal Pelajaran" icon="schedule" />
                        <x-nav-link route="nilai.index" label="Nilai Siswa" icon="grade" />
                    </x-nav-group>
                    <x-nav-group label="Administrasi">
                        <x-nav-link route="laporan.jadwal" label="Laporan" icon="report" />
                        <x-nav-link route="pengaturan.index" label="Pengaturan Sekolah" icon="settings" />
                        <x-nav-link route="profile.edit" label="Profil" icon="profile" />
                    </x-nav-group>
                @endrole
                @role('guru')<x-nav-group label="Proses Belajar"><x-nav-link route="jadwal.index" label="Jadwal Mengajar" icon="schedule" /><x-nav-link route="nilai.index" label="Input & Riwayat Nilai" icon="grade" /></x-nav-group>@endrole
                @role('siswa')<x-nav-group label="Akademik"><x-nav-link route="jadwal.index" label="Jadwal Pelajaran" icon="schedule" /><x-nav-link route="nilai.index" label="Nilai Saya" icon="grade" /></x-nav-group>@endrole
                @role('kepala_sekolah')<x-nav-group label="Monitoring"><x-nav-link route="jadwal.index" label="Jadwal Pelajaran" icon="schedule" /><x-nav-link route="nilai.index" label="Nilai Siswa" icon="grade" /><x-nav-link route="laporan.jadwal" label="Laporan" icon="report" /></x-nav-group>@endrole
                @unlessrole('admin')<x-nav-group label="Akun"><x-nav-link route="profile.edit" label="Profil" icon="profile" /></x-nav-group>@endunlessrole
            </nav>
            <div class="sidebar-help"><a href="{{ route('bantuan') }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9.7 9a2.5 2.5 0 1 1 3.4 2.34c-.72.33-1.1.77-1.1 1.66M12 17h.01"/></svg>Bantuan</a></div>
        </aside>

        <div class="app-workspace">
            <header class="app-header">
                <button type="button" class="mobile-menu" @click="sidebarOpen=true" aria-controls="sidebar" aria-label="Buka menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
                <div class="header-title"><h1>{{ $pageTitle }}</h1><p>{{ request()->routeIs('dashboard') ? 'Selamat datang, '.auth()->user()->name.'. Kelola data sekolah dengan mudah.' : 'Kelola informasi SISDAR dengan mudah dan terstruktur.' }}</p></div>
                <div class="header-tools">
                    <a href="{{ route('landing') }}" class="header-home" aria-label="Kembali ke halaman awal"><x-nav-icon name="home" /><span>Halaman Awal</span></a>
                    <div class="header-account" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" class="header-profile" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="menu" aria-controls="account-menu">
                            <span>{{ str(auth()->user()->name)->substr(0,1)->upper() }}</span>
                            <div><strong>{{ auth()->user()->name }}</strong><small>{{ $roleLabel }}</small></div>
                            <svg :class="open && 'rotate'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg>
                        </button>
                        <div id="account-menu" x-show="open" x-transition.origin.top.right x-cloak class="account-dropdown" role="menu">
                            <div class="account-dropdown-user"><span>{{ str(auth()->user()->name)->substr(0,1)->upper() }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div></div>
                            <a href="{{ route('profile.edit') }}" wire:navigate @click="open = false" role="menuitem"><x-nav-icon name="profile" />Profil Saya</a>
                            <a href="{{ route('bantuan') }}" target="_blank" rel="noopener" @click="open = false" role="menuitem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9.7 9a2.5 2.5 0 1 1 3.4 2.34c-.72.33-1.1.77-1.1 1.66M12 17h.01"/></svg>Bantuan</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" role="menuitem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H9"/></svg>Keluar</button></form>
                        </div>
                    </div>
                </div>
                <div class="academic-selector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>Tahun Ajaran <strong>{{ app(\App\Services\PeriodeAktif::class)->tahunAjaran()?->nama ?? 'Belum Aktif' }}</strong><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m8 10 4 4 4-4"/></svg></div>
            </header>
            <main id="main-content" class="app-main" tabindex="-1"><div class="content-stage">{{ $slot }}</div></main>
        </div>
    </div>

    <div x-data="{ show:false,type:'success',message:'',timer:null }" @notify.window="type=$event.detail.type;message=$event.detail.message;show=true;clearTimeout(timer);timer=setTimeout(()=>show=false,type==='error'?6000:4000)" x-show="show" x-transition x-cloak class="toast" :class="type" :role="type === 'error' ? 'alert' : 'status'" x-text="message"></div>
    @if(session('success'))<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)" class="toast success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,6000)" class="toast error" role="alert">{{ session('error') }}</div>@endif
</body>
</html>
