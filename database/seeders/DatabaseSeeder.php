<?php

namespace Database\Seeders;

use Database\Seeders\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DemoCatalog::assertConsistent();

        $this->call([
            RolePermissionSeeder::class,
            AkunDanGuruSeeder::class,
            PeriodeDanMasterSeeder::class,
            SiswaSeeder::class,
            PengajaranSeeder::class,
            JadwalPelajaranSeeder::class,
            NilaiTugasSeeder::class,
        ]);
    }
}
