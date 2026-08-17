<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0369a1">
    <title>Masuk — SDN 232 MALTENG</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-dvh bg-slate-950 text-slate-900">
    <main class="grid min-h-dvh lg:grid-cols-[1.1fr_.9fr]">
        <section
            class="relative hidden overflow-hidden bg-sky-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-20 pattern-grid"></div>
            <div class="relative flex items-center gap-3">
                <div class="grid size-14 place-items-center">
                    <img src="{{ asset('logo-malteng.png') }}" alt="Logo Kabupaten Maluku Tengah"
                        width="42" height="52" class="h-full w-full object-contain">
                </div>
                <div>
                    <p class="font-semibold">SD Negeri 232</p>
                    <p class="text-sm text-sky-100">Maluku Tengah</p>
                </div>
            </div>
            <div class="relative max-w-xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[.2em] text-sky-200">Sistem Informasi Akademik
                </p>
                <h1 class="text-5xl font-semibold leading-tight">Jadwal dan nilai siswa, tersusun dalam satu ruang
                    kerja.</h1>
                <p class="mt-6 text-lg leading-relaxed text-sky-100">Akses aman untuk admin, guru, siswa, dan kepala
                    sekolah.</p>
            </div>
            <p class="relative text-sm text-sky-200">Waktu Indonesia Timur · Asia/Jayapura</p>
        </section>
        <section class="flex items-center justify-center bg-slate-50 p-5 sm:p-10">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-9">
                <div class="mb-8 lg:hidden">
                    <div class="mb-4 grid size-16 place-items-center">
                        <img src="{{ asset('logo-malteng.png') }}" alt="Logo Kabupaten Maluku Tengah"
                            width="48" height="58" class="h-full w-full object-contain">
                    </div>
                </div>
                <p class="text-sm font-semibold text-sky-700">Selamat datang</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Masuk ke akun Anda</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan username atau email yang diberikan sekolah.</p>
                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">@csrf
                    <div><label for="login" class="form-label">Username atau email <span>*</span></label><input
                            id="login" name="login" value="{{ old('login') }}" class="form-input"
                            autocomplete="username" autofocus required>
                        @error('login')
                            <p class="form-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div><label for="password" class="form-label">Password <span>*</span></label><input id="password"
                            name="password" type="password" class="form-input" autocomplete="current-password" required>
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-slate-600"><input
                            type="checkbox" name="remember" value="1"
                            class="size-4 rounded border-slate-300 text-sky-600"> Ingat saya</label>
                    <button class="btn-primary w-full" type="submit">Masuk</button>
                </form>
                <p class="mt-7 text-center text-xs leading-5 text-slate-400">Registrasi publik dinonaktifkan. Hubungi
                    administrator sekolah untuk akun baru.</p>
            </div>
        </section>
    </main>
</body>

</html>
