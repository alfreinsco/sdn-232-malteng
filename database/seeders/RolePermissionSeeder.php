<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'guru.view', 'guru.create', 'guru.update', 'guru.delete',
            'siswa.view', 'siswa.create', 'siswa.update', 'siswa.delete',
            'kelas.view', 'kelas.create', 'kelas.update', 'kelas.delete',
            'mata-pelajaran.view', 'mata-pelajaran.create', 'mata-pelajaran.update', 'mata-pelajaran.delete',
            'tahun-ajaran.view', 'tahun-ajaran.manage',
            'semester.view', 'semester.manage',
            'jam-pelajaran.view', 'jam-pelajaran.manage',
            'pengajaran.view', 'pengajaran.manage',
            'jadwal.view', 'jadwal.create', 'jadwal.update', 'jadwal.delete',
            'nilai.view', 'nilai.create', 'nilai.update',
            'laporan.view', 'laporan.print', 'laporan.pdf',
            'pengaturan.view', 'pengaturan.update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $guru = Role::findOrCreate('guru');
        $siswa = Role::findOrCreate('siswa');
        $kepala = Role::findOrCreate('kepala_sekolah');

        $admin->syncPermissions($permissions);
        $guru->syncPermissions([
            'dashboard.view', 'siswa.view', 'kelas.view', 'mata-pelajaran.view', 'pengajaran.view',
            'jadwal.view', 'nilai.view', 'nilai.create', 'nilai.update',
            'laporan.view', 'laporan.print', 'laporan.pdf',
        ]);
        $siswa->syncPermissions([
            'dashboard.view', 'jadwal.view', 'nilai.view', 'laporan.view', 'laporan.print', 'laporan.pdf',
        ]);
        $kepala->syncPermissions([
            'dashboard.view', 'guru.view', 'siswa.view', 'kelas.view', 'mata-pelajaran.view',
            'pengajaran.view', 'jadwal.view', 'nilai.view', 'laporan.view', 'laporan.print', 'laporan.pdf',
        ]);
    }
}
