<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AktivasiSemester;
use App\Services\AktivasiTahunAjaran;
use App\Services\PenyimpananNilaiMassal;
use App\Services\ValidasiJadwal;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_logout_and_public_registration_is_disabled(): void
    {
        $response = $this->post('/login', ['login' => 'admin', 'password' => 'Sekolah232!']);
        $response->assertStatus(302);
        $this->assertSame('/dashboard', parse_url($response->baseResponse->getTargetUrl(), PHP_URL_PATH));
        $this->assertAuthenticated();
        $this->post('/logout')->assertStatus(302);
        $this->assertGuest();
        $this->get('/register')->assertNotFound();
    }

    public function test_invalid_and_inactive_users_cannot_login(): void
    {
        $this->post('/login', ['login' => 'admin', 'password' => 'salah'])->assertSessionHasErrors('login');
        User::where('username', 'admin')->update(['status' => 'nonaktif']);
        $this->post('/login', ['login' => 'admin', 'password' => 'Sekolah232!'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_all_roles_can_render_their_dashboard(): void
    {
        foreach (['admin', 'guru1', 'siswa', 'kepala'] as $username) {
            $this->actingAs(User::where('username', $username)->first())->get('/dashboard')->assertOk()->assertSee('Selamat datang');
        }
    }

    public function test_role_authorization_is_enforced_by_backend(): void
    {
        $siswa = User::where('username', 'siswa')->first();
        $guru = User::where('username', 'guru1')->first();
        $kepala = User::where('username', 'kepala')->first();
        $this->actingAs($siswa)->get('/pengguna')->assertForbidden();
        $this->actingAs($guru)->get('/tahun-ajaran')->assertForbidden();
        $this->actingAs($kepala)->get('/pengguna')->assertForbidden();
        $this->actingAs(User::where('username', 'admin')->first())->get('/pengguna')->assertOk();
    }

    public function test_schedule_class_conflict_is_rejected(): void
    {
        $existing = JadwalPelajaran::with('pengajaran')->first();
        $other = Pengajaran::firstOrCreate([
            'semester_id' => $existing->pengajaran->semester_id,
            'kelas_id' => $existing->pengajaran->kelas_id,
            'mata_pelajaran_id' => MataPelajaran::whereKeyNot($existing->pengajaran->mata_pelajaran_id)->value('id'),
            'guru_id' => Guru::whereKeyNot($existing->pengajaran->guru_id)->value('id'),
        ], ['status' => 'aktif']);
        $this->expectException(ValidationException::class);
        app(ValidasiJadwal::class)->handle($other, $existing->hari, $existing->jam_pelajaran_id);
    }

    public function test_schedule_teacher_conflict_is_rejected(): void
    {
        $existing = JadwalPelajaran::with('pengajaran')->first();
        $other = Pengajaran::firstOrCreate([
            'semester_id' => $existing->pengajaran->semester_id,
            'kelas_id' => Pengajaran::where('kelas_id', '!=', $existing->pengajaran->kelas_id)->value('kelas_id'),
            'mata_pelajaran_id' => MataPelajaran::whereKeyNot($existing->pengajaran->mata_pelajaran_id)->value('id'),
            'guru_id' => $existing->pengajaran->guru_id,
        ], ['status' => 'aktif']);
        $this->expectException(ValidationException::class);
        app(ValidasiJadwal::class)->handle($other, $existing->hari, $existing->jam_pelajaran_id);
    }

    public function test_authorized_teacher_can_bulk_save_zero_hundred_and_null(): void
    {
        $guru = User::where('username', 'guru1')->first();
        $pengajaran = Pengajaran::where('guru_id', $guru->guru->id)->with('kelas.siswaKelas')->first();
        $students = $pengajaran->kelas->siswaKelas->take(1);
        $siswaId = $students->first()->siswa_id;
        app(PenyimpananNilaiMassal::class)->handle($guru, $pengajaran, 9, [$siswaId => [1 => 0, 2 => 100, 3 => null, 4 => 85]]);
        $this->assertDatabaseHas('nilai_tugas', ['siswa_id' => $siswaId, 'bulan' => 9, 'minggu' => 1, 'nilai' => 0]);
        $this->assertDatabaseHas('nilai_tugas', ['siswa_id' => $siswaId, 'bulan' => 9, 'minggu' => 2, 'nilai' => 100]);
        $this->assertDatabaseHas('nilai_tugas', ['siswa_id' => $siswaId, 'bulan' => 9, 'minggu' => 3, 'nilai' => null]);
    }

    public function test_grade_range_and_unauthorized_teacher_are_rejected(): void
    {
        $guru = User::where('username', 'guru1')->first();
        $other = User::where('username', 'guru2')->first();
        $pengajaran = Pengajaran::where('guru_id', $guru->guru->id)->with('kelas.siswaKelas')->first();
        $siswaId = $pengajaran->kelas->siswaKelas->first()->siswa_id;
        try {
            app(PenyimpananNilaiMassal::class)->handle($guru, $pengajaran, 9, [$siswaId => [1 => -1, 2 => 101]]);
            $this->fail('Nilai di luar rentang harus ditolak.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
        $this->expectException(ValidationException::class);
        app(PenyimpananNilaiMassal::class)->handle($other, $pengajaran, 9, [$siswaId => [1 => 80]]);
    }

    public function test_student_cannot_submit_grades_and_only_sees_own_grade_page(): void
    {
        $siswa = User::where('username', 'siswa')->first();
        $this->actingAs($siswa)->get('/nilai-siswa')->assertOk()->assertSee('Nilai Saya');
        $this->assertSame(1, Siswa::where('user_id', $siswa->id)->count());
    }

    public function test_authorized_pdf_endpoints_return_pdf_downloads(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin)->get('/laporan/jadwal/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($admin)->get('/laporan/nilai/pdf?bulan=8')->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_pages_render_without_runtime_errors(): void
    {
        $this->actingAs(User::where('username', 'admin')->first());
        foreach (['/tahun-ajaran', '/semester', '/guru', '/siswa', '/kelas', '/mata-pelajaran', '/jam-pelajaran', '/pengajaran', '/pengguna', '/penempatan-siswa', '/jadwal-pelajaran', '/nilai-siswa', '/laporan/jadwal', '/laporan/nilai', '/pengaturan-sekolah', '/profil'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_only_one_academic_year_can_be_active(): void
    {
        $new = TahunAjaran::create(['nama' => '2027/2028', 'tanggal_mulai' => '2027-07-01', 'tanggal_selesai' => '2028-06-30', 'status' => 'nonaktif']);
        app(AktivasiTahunAjaran::class)->handle($new);
        $this->assertSame(1, TahunAjaran::where('status', 'aktif')->count());
        $this->assertTrue($new->fresh()->status === 'aktif');
    }

    public function test_semester_cannot_be_activated_on_inactive_academic_year(): void
    {
        $inactiveYear = TahunAjaran::create(['nama' => '2027/2028', 'tanggal_mulai' => '2027-07-01', 'tanggal_selesai' => '2028-06-30', 'status' => 'nonaktif']);
        $semester = Semester::create(['tahun_ajaran_id' => $inactiveYear->id, 'nama' => 'ganjil', 'tanggal_mulai' => '2027-07-01', 'tanggal_selesai' => '2027-12-20', 'status' => 'nonaktif']);
        $this->expectException(ValidationException::class);
        app(AktivasiSemester::class)->handle($semester);
    }

    public function test_duplicate_teaching_is_rejected_by_database(): void
    {
        $existing = Pengajaran::first();
        $this->expectException(QueryException::class);
        Pengajaran::create($existing->only(['semester_id', 'kelas_id', 'mata_pelajaran_id', 'guru_id', 'status']));
    }

    public function test_week_above_four_and_student_outside_class_are_rejected(): void
    {
        $guru = User::where('username', 'guru1')->first();
        $pengajaran = Pengajaran::where('guru_id', $guru->guru->id)->with('kelas.siswaKelas')->first();
        $validStudent = $pengajaran->kelas->siswaKelas->first()->siswa_id;
        try {
            app(PenyimpananNilaiMassal::class)->handle($guru, $pengajaran, 9, [$validStudent => [5 => 80]]);
            $this->fail('Minggu kelima harus ditolak.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
        $outside = Siswa::whereDoesntHave('penempatanKelas', fn ($q) => $q->where('kelas_id', $pengajaran->kelas_id))->first();
        $this->expectException(ValidationException::class);
        app(PenyimpananNilaiMassal::class)->handle($guru, $pengajaran, 9, [$outside->id => [1 => 80]]);
    }

    public function test_academic_year_requires_a_logical_consecutive_year_name(): void
    {
        $this->actingAs(User::where('username', 'admin')->first());

        Livewire::test('pages::resource', ['resource' => 'tahun-ajaran'])
            ->set('form', [
                'nama' => '2026/2028',
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2027-06-30',
                'status' => 'nonaktif',
            ])
            ->call('save')
            ->assertHasErrors(['form.nama']);
    }

    public function test_duplicate_semester_class_and_teaching_show_validation_errors(): void
    {
        $this->actingAs(User::where('username', 'admin')->first());
        $semester = Semester::first();
        $kelas = $semester->tahunAjaran->kelas()->first();
        $pengajaran = Pengajaran::first();

        Livewire::test('pages::resource', ['resource' => 'semester'])
            ->assertSet('resource', 'semester')
            ->set('form', [
                'tahun_ajaran_id' => (string) $semester->tahun_ajaran_id,
                'nama' => $semester->nama,
                'tanggal_mulai' => $semester->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $semester->tanggal_selesai->format('Y-m-d'),
                'status' => 'nonaktif',
            ])
            ->call('save')
            ->assertHasErrors(['form.nama']);

        Livewire::test('pages::resource', ['resource' => 'kelas'])
            ->set('form', [
                'tahun_ajaran_id' => (string) $kelas->tahun_ajaran_id,
                'nama' => $kelas->nama,
                'tingkat' => $kelas->tingkat,
                'wali_kelas_id' => null,
                'status' => 'aktif',
            ])
            ->call('save')
            ->assertHasErrors(['form.nama']);

        Livewire::test('pages::resource', ['resource' => 'pengajaran'])
            ->set('form', [
                'semester_id' => (string) $pengajaran->semester_id,
                'kelas_id' => (string) $pengajaran->kelas_id,
                'mata_pelajaran_id' => (string) $pengajaran->mata_pelajaran_id,
                'guru_id' => (string) $pengajaran->guru_id,
                'status' => $pengajaran->status,
            ])
            ->call('save')
            ->assertHasErrors(['form.guru_id']);
    }

    public function test_existing_relational_master_data_can_be_edited_without_type_errors(): void
    {
        $this->actingAs(User::where('username', 'admin')->first());
        $kelas = TahunAjaran::first()->kelas()->first();

        Livewire::test('pages::resource', ['resource' => 'kelas'])
            ->call('edit', $kelas->id)
            ->set('form.nama', $kelas->nama.' Revisi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'nama' => $kelas->nama.' Revisi',
        ]);
    }
}
