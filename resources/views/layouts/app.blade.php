<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0284c7">
    <title>{{ $title ?? 'SD Negeri 232 Maluku Tengah' }} — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192x192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $school = \App\Models\PengaturanSekolah::first();
    $schoolName = $school?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah';
    $schoolLogo = $school?->logo ? \Illuminate\Support\Facades\Storage::url($school->logo) : asset('logo-malteng.png');
    $role = auth()->user()->getRoleNames()->first();
    $roleLabel = match ($role) {
        'admin' => 'Administrator',
        'guru' => 'Guru',
        'siswa' => 'Siswa',
        'kepala_sekolah' => 'Kepala Sekolah',
        default => str($role)->replace('_', ' ')->title(),
    };
    $pageTitle = match (true) {
        request()->routeIs('dashboard') => 'Dashboard',
        request()->routeIs('tahun-ajaran.*') => 'Tahun Ajaran',
        request()->routeIs('semester.*') => 'Semester',
        request()->routeIs('kelas.siswa.*') => 'Anggota Kelas',
        request()->routeIs('kelas.*') => 'Kelas',
        request()->routeIs('mata-pelajaran.*') => 'Mata Pelajaran',
        request()->routeIs('jam-pelajaran.*') => 'Jam Pelajaran',
        request()->routeIs('guru.*') => 'Guru',
        request()->routeIs('siswa-kelas.*') => 'Penempatan Siswa',
        request()->routeIs('siswa.*') => 'Siswa',
        request()->routeIs('pengajaran.*') => 'Pengajaran',
        request()->routeIs('jadwal.*') => 'Jadwal Pelajaran',
        request()->routeIs('nilai.*') => 'Nilai Siswa',
        request()->routeIs('users.*') => 'Pengguna',
        request()->routeIs('pengaturan.*') => 'Pengaturan Sekolah',
        request()->routeIs('laporan.jadwal') => 'Laporan Jadwal',
        request()->routeIs('laporan.nilai') => 'Laporan Nilai',
        request()->routeIs('profile.*') => 'Profil Saya',
        default => 'Sistem Akademik',
    };
@endphp

<body class="min-h-dvh bg-slate-50 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.08),_transparent_30rem)] text-slate-800 antialiased"
    x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <div class="min-h-dvh w-full max-w-full overflow-x-clip lg:grid lg:h-dvh lg:min-h-0 lg:grid-cols-[18rem_minmax(0,1fr)] lg:overflow-hidden">
        <div x-show="sidebarOpen" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-[2px] lg:hidden"
            @click="sidebarOpen=false" aria-hidden="true"></div>
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-[min(18rem,calc(100vw-1rem))] max-w-full -translate-x-full flex-col border-r border-slate-200/80 bg-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-dvh lg:w-auto lg:translate-x-0 print:hidden"
            :class="sidebarOpen && 'translate-x-0'" :aria-hidden="(!sidebarOpen && window.innerWidth < 1024).toString()" aria-label="Navigasi utama">
            <div class="relative overflow-hidden border-b border-sky-100 px-5 pb-5 pt-4">
                <div class="pointer-events-none absolute -right-10 -top-16 size-40 rounded-full bg-sky-100/70 blur-2xl" aria-hidden="true"></div>
                <div class="relative flex min-h-14 items-center gap-3">
                    <img src="{{ $schoolLogo }}" alt="Logo {{ $schoolName }}" width="40" height="48"
                        class="h-12 w-10 shrink-0 object-contain">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold leading-5 text-slate-950">{{ $schoolName }}</p>
                        <p class="mt-0.5 text-xs font-medium text-sky-700">Sistem Informasi Akademik</p>
                    </div>
                    <button type="button"
                        class="grid size-11 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-white/80 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 lg:hidden"
                        @click="sidebarOpen=false" aria-label="Tutup menu">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>
                <div class="relative mt-4 flex items-center gap-2 rounded-xl border border-sky-100 bg-white/70 px-3 py-2 text-xs text-slate-600">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-50 motion-reduce:animate-none"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span>
                    <span>Portal sekolah aktif</span>
                    <span class="ml-auto font-semibold text-slate-700">WIT</span>
                </div>
            </div>
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5 text-sm [scrollbar-color:theme(colors.slate.300)_transparent] [scrollbar-width:thin]">
                <x-nav-link route="dashboard" label="Dashboard" icon="home" />
                @role('admin')
                    <x-nav-group label="Master Data">
                        <x-nav-link route="tahun-ajaran.index" label="Tahun Ajaran" icon="calendar" /><x-nav-link route="semester.index"
                            label="Semester" icon="semester" /><x-nav-link route="kelas.index" label="Kelas" icon="class" /><x-nav-link
                            route="mata-pelajaran.index" label="Mata Pelajaran" icon="book" /><x-nav-link route="jam-pelajaran.index"
                            label="Jam Pelajaran" icon="clock" /><x-nav-link route="guru.index" label="Guru" icon="teacher" /><x-nav-link
                            route="siswa.index" label="Siswa" icon="students" /><x-nav-link route="siswa-kelas.index"
                            label="Penempatan Siswa" icon="placement" />
                    </x-nav-group>
                    <x-nav-group label="Akademik"><x-nav-link route="pengajaran.index" label="Pengajaran" icon="teaching" /><x-nav-link
                            route="jadwal.index" label="Jadwal Pelajaran" icon="schedule" /><x-nav-link route="nilai.index"
                            label="Nilai Siswa" icon="grade" /></x-nav-group>
                    <x-nav-group label="Manajemen"><x-nav-link route="users.index" label="Pengguna" icon="users" /><x-nav-link
                            route="pengaturan.index" label="Pengaturan Sekolah" icon="settings" /></x-nav-group>
                @endrole
                @role('guru')
                    <x-nav-group label="Akademik"><x-nav-link route="jadwal.index" label="Jadwal Mengajar" icon="schedule" /><x-nav-link
                            route="nilai.index" label="Input & Riwayat Nilai" icon="grade" /></x-nav-group>
                @endrole
                @role('siswa')
                    <x-nav-group label="Akademik"><x-nav-link route="jadwal.index" label="Jadwal Pelajaran" icon="schedule" /><x-nav-link
                            route="nilai.index" label="Nilai Saya" icon="grade" /></x-nav-group>
                @endrole
                @role('kepala_sekolah')
                    <x-nav-group label="Monitoring"><x-nav-link route="jadwal.index" label="Jadwal Pelajaran" icon="schedule" /><x-nav-link
                            route="nilai.index" label="Nilai Siswa" icon="grade" /></x-nav-group>
                @endrole
                @can('laporan.view')
                    <x-nav-group label="Laporan"><x-nav-link route="laporan.jadwal" label="Laporan Jadwal" icon="report" /><x-nav-link
                            route="laporan.nilai" label="Laporan Nilai" icon="report" /></x-nav-group>
                @endcan
                <x-nav-group label="Akun"><x-nav-link route="profile.edit" label="Profil Saya" icon="profile" /></x-nav-group>
            </nav>
            <div class="border-t border-slate-100 bg-slate-50/70 p-3">
                <a href="{{ route('profile.edit') }}" wire:navigate class="group mb-2 flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-sky-200 hover:bg-sky-50/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                    <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-sky-600 font-bold text-white ring-4 ring-sky-100">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $roleLabel }}</p>
                    </div>
                    <svg viewBox="0 0 24 24" class="size-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-sky-600 motion-reduce:transform-none" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"
                        class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H9"/></svg>
                        Keluar dari Aplikasi
                    </button>
                </form>
            </div>
        </aside>
        <div class="w-full min-w-0 max-w-full lg:flex lg:h-dvh lg:min-h-0 lg:flex-col lg:overflow-hidden">
            <header
                class="sticky top-0 z-30 flex min-h-[4.5rem] w-full min-w-0 max-w-full items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl lg:shrink-0 lg:px-8 print:hidden">
                <button type="button" class="grid size-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 lg:hidden"
                    @click="sidebarOpen=true" :aria-expanded="sidebarOpen.toString()" aria-controls="sidebar" aria-label="Buka menu">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                </button>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-sky-700">Sistem Akademik</p>
                    <p class="truncate text-base font-bold text-slate-950">{{ $pageTitle }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2 sm:gap-3">
                    <div class="hidden items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 sm:flex">
                        <span class="grid size-8 place-items-center rounded-lg bg-sky-50 text-sky-700"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3v3M17 3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/></svg></span>
                        <div><p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Waktu Indonesia Timur</p><p class="text-sm font-semibold text-slate-700">{{ now()->translatedFormat('d F Y') }}</p></div>
                    </div>
                    <a href="{{ route('profile.edit') }}" wire:navigate class="flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 transition hover:border-sky-200 hover:bg-sky-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500" aria-label="Buka profil {{ auth()->user()->name }}">
                        <span class="grid size-8 place-items-center rounded-lg bg-sky-600 text-xs font-bold text-white">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                        <span class="hidden max-w-36 truncate text-sm font-semibold text-slate-700 xl:block">{{ auth()->user()->name }}</span>
                    </a>
                </div>
            </header>
            <main id="main-content" class="relative mx-auto w-full min-w-0 max-w-[1600px] p-4 pb-10 sm:p-6 sm:pb-12 lg:min-h-0 lg:flex-1 lg:overflow-y-auto lg:p-8 lg:pb-10" tabindex="-1">
                <div class="content-stage">{{ $slot }}</div>
            </main>
        </div>
    </div>
    <div x-data="{ show: false, type: 'success', message: '', timer: null }"
        @notify.window="type = $event.detail.type; message = $event.detail.message; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, type === 'error' ? 6000 : 4000)"
        x-show="show" x-transition x-cloak class="toast" :class="type" :role="type === 'error' ? 'alert' : 'status'"
        x-text="message"></div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="toast success" role="status">
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" class="toast error" role="alert">
            {{ session('error') }}</div>
    @endif
</body>

</html>
