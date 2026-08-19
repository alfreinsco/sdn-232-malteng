<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class PeriodeDanMasterSeeder extends Seeder
{
    public function run(): void
    {
        $year = DemoCatalog::academicYear();
        $tahunAjaran = TahunAjaran::create([
            'nama' => $year['nama'],
            'tanggal_mulai' => $year['tanggal_mulai'],
            'tanggal_selesai' => $year['tanggal_selesai'],
            'status' => 'aktif',
        ]);

        foreach ($year['semesters'] as $semester) {
            Semester::create([
                'tahun_ajaran_id' => $tahunAjaran->id,
                ...$semester,
            ]);
        }

        foreach (DemoCatalog::subjects() as $subject) {
            MataPelajaran::create([
                ...$subject,
                'status' => 'aktif',
            ]);
        }

        foreach (DemoCatalog::lessonPeriods() as $index => $period) {
            JamPelajaran::create([
                ...$period,
                'urutan' => $index + 1,
                'status' => 'aktif',
            ]);
        }

        $waliByClass = collect(DemoCatalog::teachers())
            ->where('peran', 'wali')
            ->mapWithKeys(fn (array $teacher) => [$teacher['wali_kelas'] => $teacher['nama_lengkap']]);

        foreach (DemoCatalog::classes() as $class) {
            $waliNama = $waliByClass[$class['nama']];
            Kelas::create([
                'tahun_ajaran_id' => $tahunAjaran->id,
                'nama' => $class['nama'],
                'tingkat' => $class['tingkat'],
                'wali_kelas_id' => Guru::where('nama_lengkap', $waliNama)->value('id'),
                'status' => 'aktif',
            ]);
        }
    }
}
