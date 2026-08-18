<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f0f9ff">
    <meta name="description"
        content="Sistem Informasi Jadwal Pelajaran dan Nilai Siswa SD Negeri 232 Maluku Tengah.">
    <title>SD Negeri 232 Maluku Tengah — Sistem Akademik</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $school = \App\Models\PengaturanSekolah::first();
    $schoolName = $school?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah';
    $schoolLogo = $school?->logo ? \Illuminate\Support\Facades\Storage::url($school->logo) : asset('logo-malteng.png');
    $schoolAddress = $school?->alamat ?? 'Kabupaten Maluku Tengah, Maluku';
@endphp

<body class="landing-modern bg-white text-slate-900 antialiased">
    <a href="#konten-utama" class="skip-link">Lewati ke konten utama</a>

    <header class="fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-5 sm:pt-4">
        <nav class="modern-nav mx-auto flex min-h-16 max-w-7xl items-center justify-between px-4 sm:px-5"
            aria-label="Navigasi utama">
            <a href="#beranda" class="flex min-w-0 items-center gap-3 rounded-xl">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-sky-50 ring-1 ring-sky-100">
                    <img src="{{ $schoolLogo }}" alt="Logo {{ $schoolName }}" width="32" height="39"
                        class="h-9 w-8 object-contain">
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-extrabold leading-5 tracking-tight text-slate-950">SD Negeri 232 Maluku Tengah</span>
                    <span class="hidden truncate text-[10px] font-semibold uppercase tracking-[.12em] text-slate-500 sm:block">Sistem Akademik</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 lg:flex">
                <a href="#fitur" class="modern-nav-link">Fitur</a>
                <a href="#alur" class="modern-nav-link">Cara Kerja</a>
                <a href="#pengguna" class="modern-nav-link">Untuk Siapa</a>
                <a href="#tentang" class="modern-nav-link">Tentang</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                    class="group inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-bold text-white transition duration-200 hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 sm:px-5">
                    Masuk
                    <svg viewBox="0 0 24 24" class="size-4 transition group-hover:translate-x-0.5" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </a>
                <details class="relative lg:hidden">
                    <summary
                        class="grid size-11 cursor-pointer list-none place-items-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden"
                        aria-label="Buka menu navigasi">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </summary>
                    <div class="absolute right-0 top-14 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                        <a href="#fitur" class="modern-mobile-link">Fitur</a>
                        <a href="#alur" class="modern-mobile-link">Cara Kerja</a>
                        <a href="#pengguna" class="modern-mobile-link">Untuk Siapa</a>
                        <a href="#tentang" class="modern-mobile-link">Tentang</a>
                    </div>
                </details>
            </div>
        </nav>
    </header>

    <main id="konten-utama">
        <section id="beranda" class="hero-canvas relative isolate overflow-hidden px-4 pb-20 pt-32 sm:px-6 sm:pb-28 sm:pt-40">
            <div class="hero-dots pointer-events-none absolute inset-0 -z-20" aria-hidden="true"></div>
            <div class="hero-blob hero-blob-one pointer-events-none absolute -z-10" aria-hidden="true"></div>
            <div class="hero-blob hero-blob-two pointer-events-none absolute -z-10" aria-hidden="true"></div>

            <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[.9fr_1.1fr] lg:gap-8 xl:gap-14">
                <div class="max-w-2xl">
                    <div data-reveal class="inline-flex items-center gap-2.5 rounded-full border border-amber-200 bg-amber-50/90 px-3.5 py-2 text-xs font-extrabold text-amber-800 shadow-sm backdrop-blur">
                        <span class="grid size-6 place-items-center rounded-full bg-amber-400 text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1m0-12.8-2.1 2.1m-8.6 8.6-2.1 2.1"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                        Teman belajar siswa sekolah dasar
                    </div>

                    <h1 data-reveal data-reveal-delay="80"
                        class="mt-7 text-[clamp(2.8rem,5.7vw,5rem)] font-black leading-[.98] tracking-[-.055em] text-slate-950">
                        Belajar lebih <span class="text-sky-600">seru,</span> sekolah lebih <span class="text-orange-500">teratur.</span>
                    </h1>

                    <p data-reveal data-reveal-delay="160" class="mt-7 max-w-xl text-lg leading-8 text-slate-600 sm:text-xl">
                        Siswa mudah melihat jadwal dan hasil belajar. Guru lebih mudah mendampingi. Sekolah pun
                        dapat memantau perkembangan setiap siswa dalam satu tempat.
                    </p>

                    <div data-reveal data-reveal-delay="240" class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('login') }}"
                            class="group inline-flex min-h-14 items-center justify-center gap-3 rounded-2xl bg-sky-600 px-6 text-base font-extrabold text-white shadow-[0_14px_32px_rgba(2,132,199,.25)] transition duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-[0_18px_38px_rgba(2,132,199,.3)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 active:translate-y-0">
                            Ayo Masuk
                            <span class="grid size-8 place-items-center rounded-lg bg-white/15 transition group-hover:translate-x-1">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>
                        <a href="#fitur"
                            class="inline-flex min-h-14 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/85 px-6 text-base font-extrabold text-slate-700 shadow-sm backdrop-blur transition duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:text-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 active:translate-y-0">
                            Kenali Sekolah Kami
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </a>
                    </div>

                    <div data-reveal data-reveal-delay="320" class="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-slate-600">
                        @foreach (['Mudah digunakan', 'Ramah di ponsel', 'Informasi lebih jelas'] as $benefit)
                            <span class="flex items-center gap-2">
                                <svg viewBox="0 0 24 24" class="size-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.3" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                {{ $benefit }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div data-reveal="scale" data-reveal-delay="140" class="hero-product kids-hero relative mx-auto w-full max-w-2xl" data-parallax>
                    <div class="absolute left-2 top-[14%] z-20 hidden items-center gap-3 rounded-2xl border border-white bg-white/95 p-3 shadow-xl shadow-slate-900/10 backdrop-blur sm:flex lg:-left-2">
                        <span class="grid size-10 place-items-center rounded-xl bg-violet-100 text-violet-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 5a3 3 0 0 1 3-3h5v18H7a3 3 0 0 0-3 3V5Z"/><path d="M20 5a3 3 0 0 0-3-3h-5v18h5a3 3 0 0 1 3 3V5Z"/></svg>
                        </span>
                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hari ini</p><p class="text-sm font-extrabold text-slate-900">Ayo belajar bersama!</p></div>
                    </div>

                    <div class="kids-illustration relative z-10 overflow-hidden rounded-[3rem] border border-white/80 bg-white/55 p-3 shadow-[0_30px_75px_rgba(14,116,144,.16)] backdrop-blur-sm sm:p-5">
                        <img src="{{ asset('images/landing/kids-learning.svg') }}"
                            alt="Ilustrasi anak-anak belajar bersama di sekolah" width="800" height="650"
                            fetchpriority="high" class="h-auto w-full">
                    </div>

                    <div class="kid-sticker kid-sticker-star absolute -right-2 top-5 z-20 grid size-16 place-items-center rounded-[1.4rem] bg-amber-400 text-white shadow-xl shadow-amber-500/25 sm:right-4 sm:size-20" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="size-8 sm:size-10" fill="currentColor"><path d="m12 2.5 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.1l6.2-.9L12 2.5Z"/></svg>
                    </div>

                    <div class="absolute -bottom-3 right-5 z-20 hidden items-center gap-3 rounded-2xl bg-sky-600 px-4 py-3 text-white shadow-xl shadow-sky-500/25 sm:flex">
                        <span class="grid size-9 place-items-center rounded-xl bg-white/15"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m2 10 10-5 10 5-10 5L2 10Z"/><path d="M6 12.5V17c3 2 9 2 12 0v-4.5"/></svg></span>
                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-sky-100">Kelas I hingga VI</p><p class="mt-0.5 text-sm font-extrabold">Tumbuh dan berprestasi</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative z-10 -mt-4 px-4 sm:px-6">
            <div data-reveal class="mx-auto grid max-w-6xl grid-cols-2 gap-px overflow-hidden rounded-3xl border border-slate-200 bg-slate-200 shadow-[0_15px_45px_rgba(15,23,42,.08)] sm:grid-cols-4">
                @foreach ([
                    ['calendar', 'Lihat Jadwal', 'Tahu pelajaran hari ini', 'sky'],
                    ['star', 'Lihat Nilai', 'Pantau hasil belajar', 'amber'],
                    ['phone', 'Mudah Diakses', 'Bisa dari ponsel', 'violet'],
                    ['smile', 'Untuk Kelas 1–6', 'Sesuai kebutuhan siswa SD', 'emerald'],
                ] as [$icon, $title, $label, $color])
                    <div class="bg-white px-4 py-5 sm:px-5 sm:py-6">
                        <span class="mx-auto grid size-11 place-items-center rounded-2xl bg-{{ $color }}-100 text-{{ $color }}-700">
                            @if ($icon === 'calendar')
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z"/><path d="M3 9h18M8 2v4m8-4v4"/></svg>
                            @elseif ($icon === 'star')
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                            @elseif ($icon === 'phone')
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/></svg>
                            @endif
                        </span>
                        <p class="mt-3 text-center text-sm font-extrabold text-slate-950">{{ $title }}</p>
                        <p class="mt-1 text-center text-xs font-medium leading-5 text-slate-500">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="fitur" class="scroll-mt-24 px-4 py-24 sm:px-6 sm:py-32">
            <div class="mx-auto max-w-7xl">
                <div data-reveal class="mx-auto max-w-3xl text-center">
                    <p class="modern-kicker">Belajar Jadi Lebih Mudah</p>
                    <h2 class="modern-heading">Jadwal jelas, nilai mudah dilihat, anak lebih siap belajar.</h2>
                    <p class="modern-copy mx-auto">Informasi penting disajikan dengan rapi agar anak, guru, dan sekolah tidak perlu bingung mencari data akademik.</p>
                </div>

                <div data-reveal="scale" class="mt-14 overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 p-2 shadow-[0_25px_70px_rgba(15,23,42,.18)] sm:p-3">
                    <div class="grid overflow-hidden rounded-[1.5rem] bg-white lg:grid-cols-[.34fr_.66fr]">
                        <div class="flex flex-col justify-between bg-slate-50 p-5 sm:p-7 lg:p-8">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-sky-700">Yuk, lihat fiturnya</p>
                                <h3 class="mt-4 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Semua kebutuhan sekolah ada di sini.</h3>
                                <div class="mt-7 space-y-2" role="tablist" aria-label="Pratinjau fitur SD Negeri 232 Maluku Tengah">
                                    <button type="button" class="showcase-tab is-active" data-showcase-tab
                                        data-image="{{ asset('images/landing/jadwal.webp') }}"
                                        data-alt="Tampilan pengelolaan jadwal pelajaran SD Negeri 232 Maluku Tengah"
                                        data-title="Tahu jadwal belajar hari ini"
                                        data-copy="Anak dan guru dapat melihat pelajaran berdasarkan kelas, hari, dan semester aktif."
                                        aria-selected="true" role="tab">
                                        <span class="showcase-number">01</span>
                                        <span><strong>Jadwal Pelajaran</strong><small>Lihat kegiatan hari ini</small></span>
                                    </button>
                                    <button type="button" class="showcase-tab" data-showcase-tab
                                        data-image="{{ asset('images/landing/nilai.webp') }}"
                                        data-alt="Tampilan pengisian nilai siswa SD Negeri 232 Maluku Tengah"
                                        data-title="Perkembangan belajar lebih jelas"
                                        data-copy="Guru mencatat hasil tugas secara teratur dan siswa dapat melihat perkembangan belajarnya."
                                        aria-selected="false" role="tab">
                                        <span class="showcase-number">02</span>
                                        <span><strong>Nilai Siswa</strong><small>Lihat perkembangan belajar</small></span>
                                    </button>
                                    <button type="button" class="showcase-tab" data-showcase-tab
                                        data-image="{{ asset('images/landing/laporan.webp') }}"
                                        data-alt="Tampilan laporan nilai SD Negeri 232 Maluku Tengah"
                                        data-title="Sekolah mudah memantau"
                                        data-copy="Laporan yang rapi membantu guru dan kepala sekolah memberikan pendampingan yang lebih tepat."
                                        aria-selected="false" role="tab">
                                        <span class="showcase-number">03</span>
                                        <span><strong>Laporan Akademik</strong><small>Pantau tumbuh kembang siswa</small></span>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-8 border-t border-slate-200 pt-6">
                                <p id="showcase-title" class="font-extrabold text-slate-950">Tahu jadwal belajar hari ini</p>
                                <p id="showcase-copy" class="mt-2 text-sm leading-6 text-slate-600">Anak dan guru dapat melihat pelajaran berdasarkan kelas, hari, dan semester aktif.</p>
                            </div>
                        </div>
                        <div class="relative min-h-[340px] overflow-hidden bg-sky-50 sm:min-h-[520px]">
                            <div class="absolute inset-x-8 top-8 h-24 rounded-full bg-sky-300/30 blur-3xl" aria-hidden="true"></div>
                            <img id="showcase-image" src="{{ asset('images/landing/jadwal.webp') }}"
                                alt="Tampilan pengelolaan jadwal pelajaran SD Negeri 232 Maluku Tengah" width="1120" height="1055"
                                loading="lazy" class="showcase-image absolute left-5 top-7 w-[calc(100%-1.25rem)] max-w-none rounded-l-2xl border border-slate-200 shadow-2xl shadow-slate-900/15 sm:left-10 sm:top-10 sm:w-[calc(100%-2.5rem)]">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-sky-50 px-4 py-24 sm:px-6 sm:py-32">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-12 lg:grid-cols-[.82fr_1.18fr] lg:items-center">
                    <div data-reveal="left">
                        <p class="modern-kicker">Semua Saling Terhubung</p>
                        <h2 class="modern-heading text-left">Dari jadwal belajar sampai hasil yang membanggakan.</h2>
                        <p class="modern-copy">Guru menyiapkan kegiatan belajar, anak mengikuti jadwalnya, lalu perkembangan belajar dapat dipantau dengan lebih mudah.</p>
                        <div class="mt-8 flex flex-wrap gap-2">
                            @foreach (['Kelas', 'Jadwal', 'Mata pelajaran', 'Tugas', 'Nilai', 'Laporan'] as $item)
                                <span class="rounded-full border border-sky-200 bg-white px-3.5 py-2 text-xs font-bold text-sky-800 shadow-sm">{{ $item }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div data-reveal="right" class="relative min-h-[430px] sm:min-h-[520px]">
                        <div class="absolute left-[5%] top-[4%] w-[76%] rotate-[-3deg] overflow-hidden rounded-2xl border border-white bg-white p-2 shadow-2xl shadow-sky-950/10 transition duration-500 hover:z-20 hover:rotate-0 hover:scale-[1.02]">
                            <img src="{{ asset('images/landing/jadwal.webp') }}" alt="Jadwal pelajaran SD Negeri 232 Maluku Tengah" width="1120" height="1055" loading="lazy" class="aspect-[16/10] w-full rounded-xl object-cover object-top">
                        </div>
                        <div class="absolute bottom-[3%] right-[2%] w-[74%] rotate-[3deg] overflow-hidden rounded-2xl border border-white bg-white p-2 shadow-2xl shadow-sky-950/10 transition duration-500 hover:z-20 hover:rotate-0 hover:scale-[1.02]">
                            <img src="{{ asset('images/landing/nilai.webp') }}" alt="Pengisian nilai siswa SD Negeri 232 Maluku Tengah" width="1120" height="890" loading="lazy" class="aspect-[16/10] w-full rounded-xl object-cover object-top">
                        </div>
                        <div class="absolute left-[8%] top-[42%] z-10 rounded-2xl bg-slate-950 px-4 py-3 text-white shadow-xl">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-sky-300">Perjalanan belajar</p>
                            <p class="mt-1 text-sm font-extrabold">Mudah diikuti setiap hari</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="alur" class="scroll-mt-24 bg-slate-950 px-4 py-24 text-white sm:px-6 sm:py-32">
            <div class="mx-auto max-w-7xl">
                <div data-reveal class="max-w-3xl">
                    <p class="modern-kicker text-amber-300">Perjalanan Belajar</p>
                    <h2 class="modern-heading text-white">Tiga langkah sederhana untuk hari sekolah yang menyenangkan.</h2>
                    <p class="modern-copy text-violet-100/70">Informasi yang jelas membantu anak datang lebih siap, belajar lebih tenang, dan memahami perkembangannya.</p>
                </div>

                <div class="relative mt-14 grid gap-4 lg:grid-cols-3">
                    <div class="absolute left-[16%] right-[16%] top-10 hidden h-px bg-gradient-to-r from-sky-500 via-cyan-400 to-orange-400 lg:block" aria-hidden="true"></div>
                    @foreach ([
                        ['01', 'Lihat jadwal hari ini', 'Anak mengetahui pelajaran dan kegiatan yang akan diikuti bersama teman-temannya.', 'sky'],
                        ['02', 'Belajar bersama guru', 'Guru mendampingi pembelajaran dan mencatat hasil tugas secara teratur.', 'cyan'],
                        ['03', 'Lihat perkembangannya', 'Nilai dan laporan membantu sekolah memberikan dukungan yang tepat untuk setiap anak.', 'orange'],
                    ] as [$number, $title, $description, $color])
                        <article data-reveal data-reveal-delay="{{ $loop->index * 100 }}" class="relative rounded-3xl border border-white/10 bg-white/[.06] p-6 backdrop-blur sm:p-8">
                            <span class="relative z-10 grid size-20 place-items-center rounded-2xl bg-{{ $color }}-500 text-xl font-black text-white shadow-lg shadow-black/25">{{ $number }}</span>
                            <h3 class="mt-8 text-2xl font-black tracking-tight">{{ $title }}</h3>
                            <p class="mt-4 leading-7 text-slate-400">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="pengguna" class="scroll-mt-24 px-4 py-24 sm:px-6 sm:py-32">
            <div class="mx-auto max-w-7xl">
                <div data-reveal class="mx-auto max-w-3xl text-center">
                    <p class="modern-kicker">Tumbuh Bersama</p>
                    <h2 class="modern-heading">Anak menjadi pusat, semua ikut mendampingi.</h2>
                    <p class="modern-copy mx-auto">SD Negeri 232 Maluku Tengah membantu siswa, guru, dan seluruh warga sekolah bekerja bersama agar kegiatan belajar terasa lebih terarah.</p>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Siswa', 'Melihat jadwal kelas dan mengenali perkembangan hasil belajar dengan mudah.', 'S', 'sky'],
                        ['Guru', 'Menyiapkan pembelajaran, melihat jadwal, dan mencatat hasil belajar anak.', 'G', 'violet'],
                        ['Administrator', 'Menjaga data kelas, pengguna, dan kegiatan akademik tetap tertata.', 'A', 'emerald'],
                        ['Kepala Sekolah', 'Memantau kegiatan sekolah dan memastikan setiap anak mendapat dukungan.', 'K', 'orange'],
                    ] as [$role, $description, $initial, $color])
                        <article data-reveal data-reveal-delay="{{ $loop->index * 80 }}" class="role-card group">
                            <div class="flex items-center justify-between">
                                <span class="grid size-12 place-items-center rounded-2xl bg-{{ $color }}-100 text-lg font-black text-{{ $color }}-700 transition duration-300 group-hover:-rotate-6 group-hover:scale-110">{{ $initial }}</span>
                                <span class="text-xs font-bold text-slate-300">0{{ $loop->iteration }}</span>
                            </div>
                            <h3 class="mt-8 text-xl font-black text-slate-950">{{ $role }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $description }}</p>
                            <div class="mt-7 h-1 w-10 rounded-full bg-{{ $color }}-500 transition-all duration-300 group-hover:w-full"></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="tentang" class="scroll-mt-24 px-4 pb-24 sm:px-6 sm:pb-32">
            <div data-reveal="scale" class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-slate-950 text-white">
                <div class="grid lg:grid-cols-[.72fr_1.28fr]">
                    <div class="school-visual relative flex min-h-[360px] items-center justify-center overflow-hidden p-10">
                        <div class="absolute inset-0 bg-sky-600/90"></div>
                        <div class="school-rings absolute inset-0" aria-hidden="true"></div>
                        <div class="relative text-center">
                            <div class="mx-auto grid size-36 place-items-center rounded-[2rem] bg-white p-5 shadow-2xl shadow-sky-950/25 ring-8 ring-white/15">
                                <img src="{{ $schoolLogo }}" alt="Logo {{ $schoolName }}" width="100" height="123" class="h-full w-full object-contain">
                            </div>
                            <p class="mt-7 text-xs font-extrabold uppercase tracking-[.2em] text-sky-100">Identitas Sekolah</p>
                            <p class="mt-2 text-xl font-black">{{ $schoolName }}</p>
                        </div>
                    </div>
                    <div class="p-7 sm:p-10 lg:p-14">
                        <p class="modern-kicker text-amber-300">Tentang SD Negeri 232 Maluku Tengah</p>
                        <h2 class="mt-5 max-w-3xl text-3xl font-black leading-tight tracking-[-.035em] sm:text-4xl">Ruang digital yang ikut menjaga perjalanan belajar setiap anak.</h2>
                        <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-300">Sistem akademik ini dikembangkan untuk membantu SD Negeri 232 Maluku Tengah menciptakan kegiatan belajar yang lebih tertib, hangat, dan mudah dipahami oleh siswa kelas I hingga VI.</p>
                        <div class="mt-9 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/[.06] p-4"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi</p><p class="mt-2 font-bold leading-6">{{ $schoolAddress }}</p></div>
                            <div class="rounded-2xl border border-white/10 bg-white/[.06] p-4"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Zona Waktu</p><p class="mt-2 font-bold leading-6">Waktu Indonesia Timur</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 pb-6 sm:px-6 sm:pb-8">
            <div data-reveal class="cta-panel relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] px-6 py-16 text-center sm:px-10 sm:py-20">
                <div class="absolute -left-16 -top-20 size-64 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                <div class="absolute -bottom-24 -right-12 size-72 rounded-full bg-orange-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative mx-auto max-w-3xl text-white">
                    <p class="text-sm font-extrabold uppercase tracking-[.18em] text-cyan-100">Sampai Jumpa di Dalam</p>
                    <h2 class="mt-5 text-4xl font-black tracking-[-.04em] sm:text-5xl">Ayo mulai hari belajar yang menyenangkan!</h2>
                    <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-sky-100">Masuk menggunakan akun dari sekolah untuk melihat jadwal, nilai, dan informasi sesuai peranmu.</p>
                    <a href="{{ route('login') }}" class="mt-9 inline-flex min-h-14 items-center justify-center gap-3 rounded-2xl bg-white px-7 text-base font-extrabold text-slate-950 shadow-xl transition duration-200 hover:-translate-y-1 hover:bg-cyan-50 hover:text-sky-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-sky-600 active:translate-y-0">
                        Ayo Masuk
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="px-4 py-10 sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-5 border-t border-slate-200 pt-8 text-center sm:flex-row sm:text-left">
            <div class="flex items-center gap-3">
                <img src="{{ $schoolLogo }}" alt="" width="30" height="37" class="h-10 w-8 object-contain">
                <div><p class="font-extrabold text-slate-950">SD Negeri 232 Maluku Tengah</p><p class="text-xs font-medium text-slate-500">Sistem Informasi Akademik</p></div>
            </div>
            <p class="text-xs leading-5 text-slate-500">&copy; {{ now()->year }} {{ $schoolName }}. Sistem Informasi Akademik.</p>
        </div>
    </footer>
</body>

</html>
