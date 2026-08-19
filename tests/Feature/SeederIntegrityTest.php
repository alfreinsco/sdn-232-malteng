<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiTugas;
use App\Models\Pengajaran;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_accounts_and_homeroom_teachers_are_connected_to_their_own_class(): void
    {
        $this->assertNotNull(User::where('username', 'admin')->first());
        $this->assertNotNull(User::where('username', 'kepala')->first());
        $this->assertNotNull(User::where('username', 'guru1')->first()?->guru);
        $this->assertNotNull(User::where('username', 'siswa')->first()?->siswa);

        $wali = User::where('username', 'guru1')->first()->guru;
        $kelas = Kelas::where('nama', 'I A')->firstOrFail();
        $this->assertSame($wali->id, $kelas->wali_kelas_id);
        $this->assertTrue(Pengajaran::where('guru_id', $wali->id)->where('kelas_id', $kelas->id)->exists());
        $this->assertFalse(Pengajaran::where('guru_id', $wali->id)->where('kelas_id', '!=', $kelas->id)->exists());
    }

    public function test_each_class_has_exactly_one_teacher_per_subject(): void
    {
        $subjects = MataPelajaran::count();

        foreach (Kelas::all() as $kelas) {
            $teachings = Pengajaran::where('kelas_id', $kelas->id)->get();
            $this->assertSame($subjects, $teachings->count());
            $this->assertSame($subjects, $teachings->pluck('mata_pelajaran_id')->unique()->count());
        }
    }

    public function test_specialist_teachers_only_teach_their_assigned_subject(): void
    {
        $pjok = MataPelajaran::where('nama', 'PJOK')->firstOrFail();
        $fitriyani = Guru::where('nama_lengkap', 'Fitriyani, S.Pd')->firstOrFail();

        $this->assertSame(
            Kelas::count(),
            Pengajaran::where('guru_id', $fitriyani->id)->where('mata_pelajaran_id', $pjok->id)->count()
        );
        $this->assertFalse(
            Pengajaran::where('guru_id', $fitriyani->id)->where('mata_pelajaran_id', '!=', $pjok->id)->exists()
        );
    }

    public function test_schedules_have_no_class_or_teacher_conflicts(): void
    {
        $schedules = JadwalPelajaran::with('pengajaran')->get();

        $classSlots = $schedules->map(fn ($item) => $item->hari.'|'.$item->jam_pelajaran_id.'|'.$item->pengajaran->kelas_id);
        $teacherSlots = $schedules->map(fn ($item) => $item->hari.'|'.$item->jam_pelajaran_id.'|'.$item->pengajaran->guru_id);

        $this->assertSame($classSlots->count(), $classSlots->unique()->count());
        $this->assertSame($teacherSlots->count(), $teacherSlots->unique()->count());
        $this->assertSame(Kelas::count() * MataPelajaran::count() * DemoCatalog::periodsPerSubject(), $schedules->count());
    }

    public function test_students_are_placed_in_one_age_appropriate_class_and_grades_follow_that_class(): void
    {
        $yearStart = (int) substr(DemoCatalog::academicYear()['tanggal_mulai'], 0, 4);

        foreach (Siswa::with('penempatanKelas.kelas')->get() as $siswa) {
            $aktif = $siswa->penempatanKelas->where('status', 'aktif');
            $this->assertCount(1, $aktif);
            $kelas = $aktif->first()->kelas;
            $this->assertSame($yearStart - 5 - $kelas->tingkat, (int) $siswa->tanggal_lahir->format('Y'));
        }

        foreach (NilaiTugas::with(['pengajaran.kelas.siswaKelas' => fn ($query) => $query->where('status', 'aktif')])->get() as $nilai) {
            $this->assertTrue(
                $nilai->pengajaran->kelas->siswaKelas->contains('siswa_id', $nilai->siswa_id)
            );
        }
    }
}
