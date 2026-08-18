<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('landing');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::livewire('/dashboard', 'pages::dashboard')->middleware('permission:dashboard.view')->name('dashboard');

    Route::livewire('/tahun-ajaran', 'pages::resource')->defaults('resource', 'tahun-ajaran')->middleware('permission:tahun-ajaran.view')->name('tahun-ajaran.index');
    Route::livewire('/semester', 'pages::resource')->defaults('resource', 'semester')->middleware('permission:semester.view')->name('semester.index');
    Route::livewire('/guru', 'pages::resource')->defaults('resource', 'guru')->middleware('permission:guru.view')->name('guru.index');
    Route::livewire('/siswa', 'pages::resource')->defaults('resource', 'siswa')->middleware('permission:siswa.view')->name('siswa.index');
    Route::livewire('/kelas', 'pages::resource')->defaults('resource', 'kelas')->middleware('permission:kelas.view')->name('kelas.index');
    Route::livewire('/kelas/{kelas}/siswa', 'pages::kelas-siswa')->middleware('permission:kelas.view')->name('kelas.siswa.index');
    Route::livewire('/mata-pelajaran', 'pages::resource')->defaults('resource', 'mata-pelajaran')->middleware('permission:mata-pelajaran.view')->name('mata-pelajaran.index');
    Route::livewire('/jam-pelajaran', 'pages::resource')->defaults('resource', 'jam-pelajaran')->middleware('permission:jam-pelajaran.view')->name('jam-pelajaran.index');
    Route::livewire('/pengajaran', 'pages::resource')->defaults('resource', 'pengajaran')->middleware('permission:pengajaran.view')->name('pengajaran.index');
    Route::livewire('/pengguna', 'pages::resource')->defaults('resource', 'pengguna')->middleware('permission:users.view')->name('users.index');
    Route::livewire('/penempatan-siswa', 'pages::penempatan-siswa')->middleware('role:admin')->name('siswa-kelas.index');

    Route::livewire('/jadwal-pelajaran', 'pages::jadwal')->middleware('permission:jadwal.view')->name('jadwal.index');
    Route::livewire('/nilai-siswa', 'pages::nilai')->middleware('permission:nilai.view')->name('nilai.index');
    Route::livewire('/laporan/jadwal', 'pages::laporan')->defaults('jenis', 'jadwal')->middleware('permission:laporan.view')->name('laporan.jadwal');
    Route::livewire('/laporan/nilai', 'pages::laporan')->defaults('jenis', 'nilai')->middleware('permission:laporan.view')->name('laporan.nilai');
    Route::get('/laporan/{jenis}/pdf', [ReportController::class, 'pdf'])->whereIn('jenis', ['jadwal', 'nilai'])->middleware('permission:laporan.pdf')->name('laporan.pdf');

    Route::livewire('/pengaturan-sekolah', 'pages::pengaturan')->middleware('permission:pengaturan.view')->name('pengaturan.index');
    Route::livewire('/profil', 'pages::profil')->name('profile.edit');
});
