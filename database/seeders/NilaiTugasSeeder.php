<?php

namespace Database\Seeders;

use App\Models\NilaiTugas;
use App\Models\Pengajaran;
use Illuminate\Database\Seeder;

class NilaiTugasSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        $pengajaran = Pengajaran::query()
            ->with(['kelas.siswaKelas' => fn ($query) => $query->where('status', 'aktif'), 'guru'])
            ->where('status', 'aktif')
            ->get();

        foreach ($pengajaran as $item) {
            foreach ($item->kelas->siswaKelas as $penempatan) {
                for ($minggu = 1; $minggu <= 4; $minggu++) {
                    $rows[] = [
                        'pengajaran_id' => $item->id,
                        'siswa_id' => $penempatan->siswa_id,
                        'bulan' => 8,
                        'minggu' => $minggu,
                        'nilai' => $minggu === 4 && $penempatan->siswa_id % 5 === 0
                            ? null
                            : 65 + (($penempatan->siswa_id * 7 + $item->id * 3 + $minggu * 11) % 31),
                        'dibuat_oleh' => $item->guru->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            NilaiTugas::insert($chunk);
        }
    }
}
