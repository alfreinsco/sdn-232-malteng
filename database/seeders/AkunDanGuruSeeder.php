<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\PengaturanSekolah;
use App\Models\User;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class AkunDanGuruSeeder extends Seeder
{
    public function run(): void
    {
        $password = DemoCatalog::PASSWORD;

        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@sisd232.test',
            'password' => $password,
            'status' => 'aktif',
        ]);
        $admin->assignRole('admin');

        $kepala = User::create([
            'name' => 'Kepala Sekolah',
            'username' => 'kepala',
            'email' => 'kepala@sisd232.test',
            'password' => $password,
            'status' => 'aktif',
        ]);
        $kepala->assignRole('kepala_sekolah');

        PengaturanSekolah::create([
            ...DemoCatalog::school(),
            'kepala_sekolah_user_id' => $kepala->id,
        ]);

        foreach (DemoCatalog::teachers() as $index => $teacher) {
            $number = $index + 1;
            $user = User::create([
                'name' => $teacher['nama_lengkap'],
                'username' => 'guru'.$number,
                'email' => 'guru'.$number.'@sisd232.test',
                'password' => $password,
                'status' => 'aktif',
            ]);
            $user->assignRole('guru');

            Guru::create([
                'user_id' => $user->id,
                'nama_lengkap' => $teacher['nama_lengkap'],
                'nip' => $teacher['nip'],
                'jenis_kelamin' => $teacher['jenis_kelamin'],
                'status' => 'aktif',
            ]);
        }
    }
}
