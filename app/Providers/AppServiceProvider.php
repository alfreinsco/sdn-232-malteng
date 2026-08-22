<?php

namespace App\Providers;

use App\Models\{Guru, JadwalPelajaran, JamPelajaran, Kelas, MataPelajaran, NilaiTugas, Pengajaran, PengaturanSekolah, Semester, Siswa, SiswaKelas, TahunAjaran, User};
use App\Observers\AktivitasModelObserver;
use App\Services\AktivitasLogger;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(AktivitasLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([User::class, PengaturanSekolah::class, TahunAjaran::class, Semester::class, Guru::class, Siswa::class,
            Kelas::class, SiswaKelas::class, MataPelajaran::class, JamPelajaran::class, Pengajaran::class,
            JadwalPelajaran::class, NilaiTugas::class] as $model) {
            $model::observe(AktivitasModelObserver::class);
        }
    }
}
