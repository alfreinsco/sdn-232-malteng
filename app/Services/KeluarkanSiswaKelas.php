<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use Illuminate\Validation\ValidationException;

class KeluarkanSiswaKelas
{
    public function __construct(private readonly AktivitasLogger $activity) {}

    public function handle(Siswa $siswa, Kelas $kelas): void
    {
        $updated = SiswaKelas::query()
            ->where('siswa_id', $siswa->id)
            ->where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->update(['status' => 'nonaktif']);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'siswa' => 'Siswa tidak tercatat aktif di kelas ini.',
            ]);
        }

        $this->activity->record('ubah', 'Mengeluarkan siswa “'.$siswa->nama_lengkap.'” dari kelas “'.$kelas->nama.'”', $siswa,
            ['kelas_id' => $kelas->id]);
    }
}
