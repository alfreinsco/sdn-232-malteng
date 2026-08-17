<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiTugas;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['dashboard.view', 'users.view', 'users.create', 'users.update', 'users.delete', 'guru.view', 'guru.create', 'guru.update', 'guru.delete', 'siswa.view', 'siswa.create', 'siswa.update', 'siswa.delete', 'kelas.view', 'kelas.create', 'kelas.update', 'kelas.delete', 'mata-pelajaran.view', 'mata-pelajaran.create', 'mata-pelajaran.update', 'mata-pelajaran.delete', 'tahun-ajaran.view', 'tahun-ajaran.manage', 'semester.view', 'semester.manage', 'jam-pelajaran.view', 'jam-pelajaran.manage', 'pengajaran.view', 'pengajaran.manage', 'jadwal.view', 'jadwal.create', 'jadwal.update', 'jadwal.delete', 'nilai.view', 'nilai.create', 'nilai.update', 'laporan.view', 'laporan.print', 'laporan.pdf', 'pengaturan.view', 'pengaturan.update'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $adminRole = Role::findOrCreate('admin');
        $guruRole = Role::findOrCreate('guru');
        $siswaRole = Role::findOrCreate('siswa');
        $kepalaRole = Role::findOrCreate('kepala_sekolah');
        $adminRole->syncPermissions($permissions);
        $guruRole->syncPermissions(['dashboard.view', 'siswa.view', 'kelas.view', 'mata-pelajaran.view', 'pengajaran.view', 'jadwal.view', 'nilai.view', 'nilai.create', 'nilai.update', 'laporan.view', 'laporan.print', 'laporan.pdf']);
        $siswaRole->syncPermissions(['dashboard.view', 'jadwal.view', 'nilai.view', 'laporan.view', 'laporan.print', 'laporan.pdf']);
        $kepalaRole->syncPermissions(['dashboard.view', 'guru.view', 'siswa.view', 'kelas.view', 'mata-pelajaran.view', 'pengajaran.view', 'jadwal.view', 'nilai.view', 'laporan.view', 'laporan.print', 'laporan.pdf']);

        $password = 'Sekolah232!';
        $admin = User::create(['name' => 'Administrator', 'username' => 'admin', 'email' => 'admin@sisd232.test', 'password' => $password, 'status' => 'aktif']);
        $admin->assignRole('admin');
        $kepala = User::create(['name' => 'Kepala Sekolah', 'username' => 'kepala', 'email' => 'kepala@sisd232.test', 'password' => $password, 'status' => 'aktif']);
        $kepala->assignRole('kepala_sekolah');
        $guru = collect();
        foreach (range(1, 8) as $i) {
            $u = User::create(['name' => fake()->name(), 'username' => 'guru'.$i, 'email' => 'guru'.$i.'@sisd232.test', 'password' => $password, 'status' => 'aktif']);
            $u->assignRole('guru');
            $guru->push(Guru::factory()->create(['user_id' => $u->id, 'nama_lengkap' => $u->name]));
        }

        $tahun = TahunAjaran::create(['nama' => '2026/2027', 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2027-06-30', 'status' => 'aktif']);
        $semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama' => 'ganjil', 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2026-12-20', 'status' => 'aktif']);
        Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama' => 'genap', 'tanggal_mulai' => '2027-01-05', 'tanggal_selesai' => '2027-06-30', 'status' => 'nonaktif']);
        PengaturanSekolah::create(['nama_sekolah' => 'SD Negeri 232 Maluku Tengah', 'npsn' => '60100000', 'alamat' => 'Kabupaten Maluku Tengah, Maluku', 'kepala_sekolah_user_id' => $kepala->id]);
        $kelas = collect(['I A', 'II A', 'III A', 'IV A', 'V A', 'VI A'])->map(fn ($nama, $i) => Kelas::create(['tahun_ajaran_id' => $tahun->id, 'nama' => $nama, 'tingkat' => $i + 1, 'wali_kelas_id' => $guru[$i]->id, 'status' => 'aktif']));
        $mapel = collect(['Pendidikan Agama', 'Pendidikan Pancasila', 'Bahasa Indonesia', 'Matematika', 'IPAS', 'Seni Budaya', 'PJOK', 'Bahasa Inggris', 'Muatan Lokal', 'Literasi'])->map(fn ($nama, $i) => MataPelajaran::create(['kode' => 'MP-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 'nama' => $nama, 'status' => 'aktif']));
        $jam = collect([['Jam 1', '07:30', '08:10', 'pelajaran'], ['Jam 2', '08:10', '08:50', 'pelajaran'], ['Istirahat', '08:50', '09:10', 'istirahat'], ['Jam 3', '09:10', '09:50', 'pelajaran'], ['Jam 4', '09:50', '10:30', 'pelajaran']])->map(fn ($r, $i) => JamPelajaran::create(['nama' => $r[0], 'jam_mulai' => $r[1], 'jam_selesai' => $r[2], 'urutan' => $i + 1, 'jenis' => $r[3], 'status' => 'aktif']));
        foreach (range(1, 30) as $i) {
            $s = Siswa::factory()->create();
            if ($i === 1) {
                $u = User::create(['name' => $s->nama_lengkap, 'username' => 'siswa', 'email' => 'siswa@sisd232.test', 'password' => $password, 'status' => 'aktif']);
                $u->assignRole('siswa');
                $s->update(['user_id' => $u->id]);
            } SiswaKelas::create(['siswa_id' => $s->id, 'kelas_id' => $kelas[($i - 1) % 6]->id, 'status' => 'aktif']);
        }

        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $slots = $jam->where('jenis', 'pelajaran')->values();
        foreach ($hari as $d => $namaHari) {
            foreach ($slots as $s => $slot) {
                foreach ($kelas as $c => $room) {
                    $teacher = $guru[($c + $s + $d) % 8];
                    $subject = $mapel[($c * 2 + $s + $d) % 10];
                    $teaching = Pengajaran::firstOrCreate(['semester_id' => $semester->id, 'kelas_id' => $room->id, 'mata_pelajaran_id' => $subject->id, 'guru_id' => $teacher->id], ['status' => 'aktif']);
                    JadwalPelajaran::create(['pengajaran_id' => $teaching->id, 'hari' => $namaHari, 'jam_pelajaran_id' => $slot->id]);
                }
            }
        }
        foreach (Pengajaran::with('kelas.siswaKelas', 'guru')->take(12)->get() as $p) {
            foreach ($p->kelas->siswaKelas as $sk) {
                foreach (range(1, 4) as $w) {
                    NilaiTugas::create(['pengajaran_id' => $p->id, 'siswa_id' => $sk->siswa_id, 'bulan' => 8, 'minggu' => $w, 'nilai' => $w === 4 && $sk->siswa_id % 4 === 0 ? null : fake()->numberBetween(65, 100), 'dibuat_oleh' => $p->guru->user_id]);
                }
            }
        }
    }
}
