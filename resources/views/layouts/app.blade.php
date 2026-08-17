<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SDN 232 MALTENG' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-dvh bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <div class="min-h-dvh lg:grid lg:grid-cols-[17rem_1fr]">
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"
            @click="sidebarOpen=false" aria-hidden="true"></div>
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-dvh lg:w-auto lg:translate-x-0"
            :class="sidebarOpen && 'translate-x-0'" aria-label="Navigasi utama">
            <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-5">
                <div class="grid size-11 place-items-center rounded-xl bg-sky-600 text-sm font-bold text-white">232
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-950">SDN 232 MALTENG</p>
                    <p class="truncate text-xs text-slate-500">Maluku Tengah</p>
                </div>
                <button
                    class="ml-auto grid size-11 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden"
                    @click="sidebarOpen=false" aria-label="Tutup menu">&times;</button>
            </div>
            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5 text-sm">
                <x-nav-link route="dashboard" label="Dashboard" icon="home" />
                @role('admin')
                    <x-nav-group label="Master Data">
                        <x-nav-link route="tahun-ajaran.index" label="Tahun Ajaran" /><x-nav-link route="semester.index"
                            label="Semester" /><x-nav-link route="kelas.index" label="Kelas" /><x-nav-link
                            route="mata-pelajaran.index" label="Mata Pelajaran" /><x-nav-link route="jam-pelajaran.index"
                            label="Jam Pelajaran" /><x-nav-link route="guru.index" label="Guru" /><x-nav-link
                            route="siswa.index" label="Siswa" /><x-nav-link route="siswa-kelas.index"
                            label="Penempatan Siswa" />
                    </x-nav-group>
                    <x-nav-group label="Akademik"><x-nav-link route="pengajaran.index" label="Pengajaran" /><x-nav-link
                            route="jadwal.index" label="Jadwal Pelajaran" /><x-nav-link route="nilai.index"
                            label="Nilai Siswa" /></x-nav-group>
                    <x-nav-group label="Manajemen"><x-nav-link route="users.index" label="Pengguna" /><x-nav-link
                            route="pengaturan.index" label="Pengaturan Sekolah" /></x-nav-group>
                @endrole
                @role('guru')
                    <x-nav-group label="Akademik"><x-nav-link route="jadwal.index" label="Jadwal Mengajar" /><x-nav-link
                            route="nilai.index" label="Input & Riwayat Nilai" /></x-nav-group>
                @endrole
                @role('siswa')
                    <x-nav-group label="Akademik"><x-nav-link route="jadwal.index" label="Jadwal Pelajaran" /><x-nav-link
                            route="nilai.index" label="Nilai Saya" /></x-nav-group>
                @endrole
                @role('kepala_sekolah')
                    <x-nav-group label="Monitoring"><x-nav-link route="jadwal.index" label="Jadwal Pelajaran" /><x-nav-link
                            route="nilai.index" label="Nilai Siswa" /></x-nav-group>
                @endrole
                @can('laporan.view')
                    <x-nav-group label="Laporan"><x-nav-link route="laporan.jadwal" label="Laporan Jadwal" /><x-nav-link
                            route="laporan.nilai" label="Laporan Nilai" /></x-nav-group>
                @endcan
                <x-nav-group label="Akun"><x-nav-link route="profile.edit" label="Profil" /></x-nav-group>
            </nav>
            <div class="border-t border-slate-100 p-3">
                <div class="mb-2 flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <div class="grid size-9 place-items-center rounded-full bg-sky-100 font-semibold text-sky-700">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs capitalize text-slate-500">
                            {{ str(auth()->user()->getRoleNames()->first())->replace('_', ' ') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button
                        class="flex min-h-11 w-full items-center rounded-lg px-3 text-sm font-medium text-rose-700 hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">Keluar</button>
                </form>
            </div>
        </aside>
        <div class="min-w-0">
            <header
                class="sticky top-0 z-30 flex h-16 items-center border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:px-8 print:hidden">
                <button class="grid size-11 place-items-center rounded-xl border border-slate-200 lg:hidden"
                    @click="sidebarOpen=true" aria-label="Buka menu"><svg viewBox="0 0 24 24" class="size-5" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg></button>
                <div class="ml-auto text-right">
                    <p class="text-xs text-slate-500">Waktu Indonesia Timur</p>
                    <p class="text-sm font-medium">{{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </header>
            <main id="main-content" class="mx-auto max-w-[1600px] p-4 lg:p-8" tabindex="-1">{{ $slot }}
            </main>
        </div>
    </div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="toast success" role="status">
            {{ session('success') }}</div>
    @endif
</body>

</html>
