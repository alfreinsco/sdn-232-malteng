<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\NilaiTugas;

class GenerateLaporan
{
    public function jadwal(array $filter)
    {
        return JadwalPelajaran::with(['pengajaran.semester.tahunAjaran', 'pengajaran.kelas', 'pengajaran.guru', 'pengajaran.mataPelajaran', 'jamPelajaran'])
            ->when($filter['semester_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('semester_id', $v)))
            ->when($filter['kelas_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('kelas_id', $v)))
            ->when($filter['guru_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('guru_id', $v)))
            ->when($filter['hari'] ?? null, fn ($q, $v) => $q->where('hari', $v))->get();
    }

    public function nilai(array $filter)
    {
        return NilaiTugas::with(['siswa', 'pengajaran.semester.tahunAjaran', 'pengajaran.kelas', 'pengajaran.guru', 'pengajaran.mataPelajaran'])
            ->when($filter['bulan'] ?? null, fn ($q, $v) => $q->where('bulan', $v))
            ->when($filter['siswa_id'] ?? null, fn ($q, $v) => $q->where('siswa_id', $v))
            ->when($filter['pengajaran_id'] ?? null, fn ($q, $v) => $q->where('pengajaran_id', $v))->get()
            ->groupBy(fn ($row) => $row->pengajaran_id.'-'.$row->siswa_id);
    }
}
