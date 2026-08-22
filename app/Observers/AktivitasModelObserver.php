<?php

namespace App\Observers;

use App\Services\AktivitasLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AktivitasModelObserver
{
    public function __construct(private readonly AktivitasLogger $logger) {}

    public function created(Model $model): void
    {
        $this->record('tambah', 'Menambahkan '.$this->label($model), $model);
    }

    public function updated(Model $model): void
    {
        $changes = $this->safe($model->getChanges());
        unset($changes['updated_at'], $changes['last_login_at']);
        if ($changes === []) {
            return;
        }

        $this->record('ubah', 'Memperbarui '.$this->label($model), $model, ['changed_fields' => array_keys($changes)]);
    }

    public function deleted(Model $model): void
    {
        $this->record('hapus', 'Menghapus '.$this->label($model), $model);
    }

    private function record(string $type, string $description, Model $model, array $properties = []): void
    {
        if (! auth()->check()) {
            return;
        }

        $this->logger->record($type, $description, $model, $properties);
    }

    private function label(Model $model): string
    {
        $names = [
            'App\\Models\\TahunAjaran' => 'tahun ajaran', 'App\\Models\\Semester' => 'semester',
            'App\\Models\\Guru' => 'guru', 'App\\Models\\Siswa' => 'siswa', 'App\\Models\\Kelas' => 'kelas',
            'App\\Models\\SiswaKelas' => 'penempatan siswa', 'App\\Models\\MataPelajaran' => 'mata pelajaran',
            'App\\Models\\JamPelajaran' => 'jam pelajaran', 'App\\Models\\Pengajaran' => 'pengajaran',
            'App\\Models\\JadwalPelajaran' => 'jadwal pelajaran', 'App\\Models\\NilaiTugas' => 'nilai siswa',
            'App\\Models\\PengaturanSekolah' => 'pengaturan sekolah', 'App\\Models\\User' => 'pengguna',
        ];
        $name = $model->nama_lengkap ?? $model->nama ?? $model->name ?? null;

        return ($names[$model::class] ?? Str::headline(class_basename($model))).($name ? ' “'.$name.'”' : ' #'.$model->getKey());
    }

    private function safe(array $values): array
    {
        foreach (['password', 'remember_token'] as $hidden) {
            unset($values[$hidden]);
        }

        return $values;
    }
}
