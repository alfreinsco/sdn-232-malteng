<?php

namespace Database\Seeders;

use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\Pengajaran;
use App\Models\Semester;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Database\Seeder;
use RuntimeException;

class JadwalPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::aktif()->firstOrFail();
        $slots = JamPelajaran::query()->where('jenis', 'pelajaran')->orderBy('urutan')->get();
        $hariList = DemoCatalog::weekdays();
        $need = DemoCatalog::periodsPerSubject();
        $occupiedTeacher = [];
        $occupiedClass = [];

        $pengajaran = Pengajaran::query()
            ->with(['kelas', 'mataPelajaran', 'guru'])
            ->where('semester_id', $semester->id)
            ->get();

        $kelasPerGuru = $pengajaran->groupBy('guru_id')->map(
            fn ($items) => $items->pluck('kelas_id')->unique()->count()
        );

        $pengajaran = $pengajaran->sortBy(fn (Pengajaran $item) => sprintf(
            '%d-%d-%s',
            ($kelasPerGuru[$item->guru_id] ?? 1) > 1 ? 0 : 1,
            $item->kelas->tingkat,
            $item->mataPelajaran->nama,
        ));

        foreach ($pengajaran as $item) {
            $placed = 0;

            for ($pass = 0; $pass < $need; $pass++) {
                foreach ($hariList as $hari) {
                    if ($placed >= $need) {
                        break 2;
                    }

                    foreach ($slots as $slot) {
                        $teacherKey = $hari.'|'.$slot->id.'|'.$item->guru_id;
                        $classKey = $hari.'|'.$slot->id.'|'.$item->kelas_id;

                        if (isset($occupiedTeacher[$teacherKey]) || isset($occupiedClass[$classKey])) {
                            continue;
                        }

                        JadwalPelajaran::create([
                            'pengajaran_id' => $item->id,
                            'hari' => $hari,
                            'jam_pelajaran_id' => $slot->id,
                        ]);

                        $occupiedTeacher[$teacherKey] = true;
                        $occupiedClass[$classKey] = true;
                        $placed++;
                        break;
                    }
                }
            }

            if ($placed < $need) {
                throw new RuntimeException("Jadwal tidak cukup untuk {$item->kelas->nama} · {$item->mataPelajaran->nama} ({$item->guru->nama_lengkap}).");
            }
        }
    }
}
