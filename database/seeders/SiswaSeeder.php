<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\User;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $demo = DemoCatalog::demoStudent();
        $sequence = 1;
        $yearStart = (int) substr(DemoCatalog::academicYear()['tanggal_mulai'], 0, 4);

        foreach (Kelas::orderBy('tingkat')->get() as $kelas) {
            $birthYear = $yearStart - 5 - $kelas->tingkat;

            for ($n = 1; $n <= DemoCatalog::studentsPerClass(); $n++, $sequence++) {
                $isDemo = $kelas->nama === $demo['kelas'] && $n === 1;
                $siswa = Siswa::factory()->create([
                    'nis' => '26'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                    'nama_lengkap' => $isDemo ? $demo['nama_lengkap'] : fake()->name(),
                    'jenis_kelamin' => $isDemo ? $demo['jenis_kelamin'] : ($n % 2 === 1 ? 'laki-laki' : 'perempuan'),
                    'tempat_lahir' => $isDemo ? $demo['tempat_lahir'] : 'Masohi',
                    'tanggal_lahir' => sprintf('%d-%02d-%02d', $birthYear, min(12, $n * 2), min(28, 3 + $n * 4)),
                    'alamat' => 'Kabupaten Maluku Tengah, Maluku',
                    'status' => 'aktif',
                ]);

                if ($isDemo) {
                    $user = User::create([
                        'name' => $siswa->nama_lengkap,
                        'username' => $demo['username'],
                        'email' => $demo['email'],
                        'password' => DemoCatalog::PASSWORD,
                        'status' => 'aktif',
                    ]);
                    $user->assignRole('siswa');
                    $siswa->update(['user_id' => $user->id]);
                }

                SiswaKelas::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $kelas->id,
                    'status' => 'aktif',
                ]);
            }
        }
    }
}
