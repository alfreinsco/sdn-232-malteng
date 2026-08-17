<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use Illuminate\Support\Facades\DB;

class PenempatanSiswaKelas
{
    public function handle(Siswa $siswa, Kelas $kelas): SiswaKelas
    {
        return DB::transaction(function () use ($siswa, $kelas): SiswaKelas {
            SiswaKelas::where('siswa_id', $siswa->id)
                ->whereHas('kelas', fn ($query) => $query->where('tahun_ajaran_id', $kelas->tahun_ajaran_id))
                ->update(['status' => 'nonaktif']);

            return SiswaKelas::updateOrCreate(['siswa_id' => $siswa->id, 'kelas_id' => $kelas->id], ['status' => 'aktif']);
        });
    }
}
