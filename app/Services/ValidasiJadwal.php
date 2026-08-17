<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\Pengajaran;
use Illuminate\Validation\ValidationException;

class ValidasiJadwal
{
    public function handle(Pengajaran $pengajaran, string $hari, int $jamPelajaranId, ?int $abaikanId = null): void
    {
        $base = JadwalPelajaran::query()->where('hari', $hari)->where('jam_pelajaran_id', $jamPelajaranId)
            ->whereHas('pengajaran', fn ($query) => $query->where('semester_id', $pengajaran->semester_id));
        if ($abaikanId) {
            $base->whereKeyNot($abaikanId);
        }
        if ((clone $base)->whereHas('pengajaran', fn ($q) => $q->where('kelas_id', $pengajaran->kelas_id))->exists()) {
            throw ValidationException::withMessages(['jadwal' => 'Jadwal bentrok: kelas sudah memiliki pelajaran pada hari dan jam tersebut.']);
        }
        if ((clone $base)->whereHas('pengajaran', fn ($q) => $q->where('guru_id', $pengajaran->guru_id))->exists()) {
            throw ValidationException::withMessages(['jadwal' => 'Jadwal bentrok: guru sudah mengajar pada hari dan jam tersebut.']);
        }
    }
}
