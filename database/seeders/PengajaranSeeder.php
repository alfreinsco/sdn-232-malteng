<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\Semester;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Database\Seeder;
use RuntimeException;

class PengajaranSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::aktif()->firstOrFail();
        $kelas = Kelas::query()->where('tahun_ajaran_id', $semester->tahun_ajaran_id)->get()->keyBy('nama');
        $mapel = MataPelajaran::query()->get()->keyBy('nama');
        $guru = Guru::query()->get()->keyBy('nama_lengkap');

        foreach (DemoCatalog::teachingAssignments() as $assignment) {
            $kelasId = $kelas->get($assignment['kelas'])?->id;
            $mapelId = $mapel->get($assignment['mapel'])?->id;
            $guruId = $guru->get($assignment['guru'])?->id;

            if (! $kelasId || ! $mapelId || ! $guruId) {
                throw new RuntimeException("Penugasan tidak dapat dihubungkan: {$assignment['guru']} mengajar {$assignment['mapel']} di {$assignment['kelas']}.");
            }

            $exists = Pengajaran::query()
                ->where('semester_id', $semester->id)
                ->where('kelas_id', $kelasId)
                ->where('mata_pelajaran_id', $mapelId)
                ->exists();

            if ($exists) {
                throw new RuntimeException("Kelas {$assignment['kelas']} sudah memiliki guru untuk {$assignment['mapel']}.");
            }

            Pengajaran::create([
                'semester_id' => $semester->id,
                'kelas_id' => $kelasId,
                'mata_pelajaran_id' => $mapelId,
                'guru_id' => $guruId,
                'status' => 'aktif',
            ]);
        }
    }
}
