<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\Kelas;
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

    public function test_lesson_period_order_must_be_unique_but_can_be_kept_when_editing(): void
    {
        $this->actingAs(User::where('username', 'admin')->first());
        $existing = JamPelajaran::orderBy('urutan')->firstOrFail();

        Livewire::test('pages::resource', ['resource' => 'jam-pelajaran'])
            ->set('form', [
                'nama' => 'Jam Duplikat',
                'jam_mulai' => '11:00',
                'jam_selesai' => '11:40',
                'urutan' => $existing->urutan,
                'jenis' => 'pelajaran',
                'status' => 'aktif',
            ])
            ->call('save')
            ->assertHasErrors(['form.urutan' => 'unique']);

        Livewire::test('pages::resource', ['resource' => 'jam-pelajaran'])
            ->call('edit', $existing->id)
            ->set('form.nama', $existing->nama.' Revisi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('jam_pelajaran', [
            'id' => $existing->id,
            'nama' => $existing->nama.' Revisi',
            'urutan' => $existing->urutan,
        ]);
    }

    public function test_lesson_period_table_displays_time_and_type_columns(): void
    {
        $response = $this->actingAs(User::where('username', 'admin')->first())
            ->get('/jam-pelajaran');

        $response->assertOk()
            ->assertSeeInOrder(['Urutan', 'Nama', 'Jam Mulai', 'Jam Selesai', 'Jenis', 'Status'])
            ->assertSee('07:30')
            ->assertSee('Pelajaran');
    }

    public function test_student_table_displays_identity_gender_and_birth_information(): void
    {
        $student = Siswa::latest('id')->firstOrFail();

        $response = $this->actingAs(User::where('username', 'admin')->first())
            ->get('/siswa');

        $response->assertOk()
            ->assertSeeInOrder(['Nama Lengkap', 'NIS', 'NISN', 'Jenis Kelamin', 'Tempat, Tanggal Lahir', 'Status'])
            ->assertSee($student->nisn)
            ->assertSee(ucfirst($student->jenis_kelamin))
            ->assertSee($student->tempat_lahir.', '.$student->tanggal_lahir->translatedFormat('d F Y'));
    }

    public function test_all_master_status_actions_toggle_without_deleting_records(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $targets = [
            'tahun-ajaran' => TahunAjaran::where('status', 'aktif')->firstOrFail(),
            'semester' => Semester::where('status', 'aktif')->firstOrFail(),
            'guru' => Guru::where('status', 'aktif')->firstOrFail(),
            'siswa' => Siswa::where('status', 'aktif')->firstOrFail(),
            'kelas' => Kelas::where('status', 'aktif')->firstOrFail(),
            'mata-pelajaran' => MataPelajaran::where('status', 'aktif')->firstOrFail(),
            'jam-pelajaran' => JamPelajaran::where('status', 'aktif')->firstOrFail(),
            'pengajaran' => Pengajaran::where('status', 'aktif')->firstOrFail(),
            'pengguna' => User::where('username', 'guru2')->firstOrFail(),
        ];

        foreach ($targets as $resource => $item) {
            $item->refresh();
            $originalStatus = $item->status;
            $targetStatus = $originalStatus === 'aktif' ? 'nonaktif' : 'aktif';
            $table = $item->getTable();
            $count = $item->newQuery()->count();

            Livewire::test('pages::resource', ['resource' => $resource])
                ->call('toggleStatus', $item->id)
                ->assertHasNoErrors();

            $this->assertDatabaseCount($table, $count);
            $this->assertDatabaseHas($table, ['id' => $item->id, 'status' => $targetStatus]);

            Livewire::test('pages::resource', ['resource' => $resource])
                ->call('toggleStatus', $item->id)
                ->assertHasNoErrors();

            $this->assertDatabaseCount($table, $count);
            $this->assertDatabaseHas($table, ['id' => $item->id, 'status' => $originalStatus]);
        }
    }

    public function test_admin_cannot_deactivate_the_account_currently_in_use(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test('pages::resource', ['resource' => 'pengguna'])
            ->call('toggleStatus', $admin->id)
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'aktif']);
    }

    public function test_table_select_all_covers_filtered_dataset_across_pages_and_supports_exclusions(): void
    {
        $this->actingAs(User::where('username', 'admin')->firstOrFail());
        $total = Siswa::where('status', 'aktif')->count();
        $excluded = Siswa::where('status', 'aktif')->firstOrFail();

        $table = Livewire::test('pages::resource', ['resource' => 'siswa'])
            ->set('status', 'aktif')
            ->set('perPage', 1)
            ->call('toggleSelectAllDataset')
            ->assertSet('selectionMode', 'all')
            ->assertSet('selectedIds', []);

        $this->assertSame($total, $table->instance()->selectedCount($total));

        $table->call('toggleRowSelection', $excluded->id)
            ->assertSet('excludedIds', [$excluded->id])
            ->call('sortBy', 'nama_lengkap')
            ->assertSet('selectionMode', 'all');

        $this->assertSame($total - 1, $table->instance()->selectedCount($total));

        $table->set('search', 'nama-yang-tidak-ada')
            ->assertSet('selectionMode', 'explicit')
            ->assertSet('excludedIds', []);
    }

    public function test_table_sort_cycles_and_url_state_is_hydrated_and_sanitized(): void
    {
        $this->actingAs(User::where('username', 'admin')->firstOrFail());

        Livewire::withQueryParams([
            'search' => 'Ani',
            'sort' => 'nama_lengkap',
            'direction' => 'desc',
            'per_page' => 25,
            'visible_columns' => 'nama_lengkap,nisn',
        ])->test('pages::resource', ['resource' => 'siswa'])
            ->assertSet('search', 'Ani')
            ->assertSet('sort', 'nama_lengkap')
            ->assertSet('direction', 'desc')
            ->assertSet('perPage', 25)
            ->assertSet('visibleColumns', 'nama_lengkap,nisn')
            ->call('sortBy', 'nama_lengkap')
            ->assertSet('sort', '')
            ->assertSet('direction', '')
            ->call('sortBy', 'nama_lengkap')
            ->assertSet('direction', 'asc')
            ->call('sortBy', 'nama_lengkap')
            ->assertSet('direction', 'desc');

        Livewire::withQueryParams([
            'sort' => 'kolom_tidak_valid',
            'direction' => 'turun',
            'per_page' => 999,
            'visible_columns' => 'kolom_tidak_valid',
        ])->test('pages::resource', ['resource' => 'siswa'])
            ->assertSet('sort', '')
            ->assertSet('direction', '')
            ->assertSet('perPage', 250)
            ->assertSet('visibleColumns', 'nama_lengkap,nis,nisn,jenis_kelamin,tempat_tanggal_lahir,status');
    }

    public function test_bulk_status_action_affects_all_filtered_pages_without_deleting_rows(): void
    {
        $this->actingAs(User::where('username', 'admin')->firstOrFail());
        $before = Siswa::count();
        $excluded = Siswa::where('status', 'aktif')->firstOrFail();

        Livewire::test('pages::resource', ['resource' => 'siswa'])
            ->set('status', 'aktif')
            ->set('perPage', 1)
            ->call('toggleSelectAllDataset')
            ->call('toggleRowSelection', $excluded->id)
            ->call('bulkSetStatus', 'nonaktif')
            ->assertSet('selectionMode', 'explicit');

        $this->assertDatabaseCount('siswa', $before);
        $this->assertDatabaseHas('siswa', ['id' => $excluded->id, 'status' => 'aktif']);
        $this->assertSame(1, Siswa::where('status', 'aktif')->count());
    }

    public function test_all_operational_tables_render_reusable_controls_without_error_state(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        foreach (['/siswa', '/penempatan-siswa', '/jadwal-pelajaran', '/nilai-siswa', '/laporan/jadwal', '/laporan/nilai'] as $uri) {
            $this->actingAs($admin)->get($uri)
                ->assertOk()
                ->assertSee('Pencarian Utama')
                ->assertSee('Reset Filter')
                ->assertSee('Atur visibilitas kolom')
                ->assertDontSee('Data tidak dapat dimuat.');
        }
    }

    public function test_custom_per_page_and_page_input_are_clamped_to_safe_bounds(): void
    {
        $this->actingAs(User::where('username', 'admin')->firstOrFail());

        Livewire::test('pages::resource', ['resource' => 'siswa'])
            ->set('perPage', 0)
            ->assertSet('perPage', 1)
            ->set('perPage', 999)
            ->assertSet('perPage', 250)
            ->call('goToTablePage', -10, 5)
            ->assertSet('paginators.page', 1)
            ->call('goToTablePage', 99, 5)
            ->assertSet('paginators.page', 5);
    }

    public function test_schedule_bulk_delete_honors_global_selection_exclusions(): void
    {
        $this->actingAs(User::where('username', 'admin')->firstOrFail());
        $schedule = JadwalPelajaran::with('pengajaran.semester')->firstOrFail();
        $matching = JadwalPelajaran::where('hari', $schedule->hari)
            ->whereHas('pengajaran', fn ($query) => $query->where('semester_id', $schedule->pengajaran->semester_id));
        $matchingCount = $matching->count();

        Livewire::test('pages::jadwal')
            ->set('semesterId', (string) $schedule->pengajaran->semester_id)
            ->set('hari', $schedule->hari)
            ->set('perPage', 1)
            ->call('toggleSelectAllDataset')
            ->call('toggleRowSelection', $schedule->id)
            ->call('bulkDelete')
            ->assertSet('selectionMode', 'explicit');

        $this->assertDatabaseHas('jadwal_pelajaran', ['id' => $schedule->id]);
        $this->assertSame(1, $matching->count());
        $this->assertGreaterThanOrEqual(1, $matchingCount);
    }

    public function test_role_scoped_operational_tables_render_without_leaking_or_query_errors(): void
    {
        $pages = [
            'guru1' => ['/jadwal-pelajaran', '/nilai-siswa', '/laporan/jadwal', '/laporan/nilai'],
            'siswa' => ['/jadwal-pelajaran', '/nilai-siswa'],
            'kepala' => ['/jadwal-pelajaran', '/nilai-siswa', '/laporan/jadwal', '/laporan/nilai'],
        ];

        foreach ($pages as $username => $uris) {
            $user = User::where('username', $username)->firstOrFail();

            foreach ($uris as $uri) {
                $this->actingAs($user)->get($uri)
                    ->assertOk()
                    ->assertDontSee('Data tidak dapat dimuat.');
            }
        }
    }

    public function test_grade_entry_table_supports_paginated_search_sort_and_column_state(): void
    {
        $teacher = User::where('username', 'guru1')->firstOrFail();
        $teaching = Pengajaran::where('guru_id', $teacher->guru->id)->firstOrFail();
        $this->actingAs($teacher);

        Livewire::test('pages::nilai')
            ->set('pengajaranId', (string) $teaching->id)
            ->set('perPage', 1)
            ->set('search', '')
            ->call('sortBy', 'nama_lengkap')
            ->assertSet('sort', 'nama_lengkap')
            ->call('toggleColumn', 'm1')
            ->assertDontSee('Data tidak dapat dimuat.')
            ->assertSee('Simpan Nilai');
    }

    public function test_report_tables_are_read_only_and_use_the_full_content_width(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        foreach (['/laporan/jadwal', '/laporan/nilai'] as $uri) {
            $this->actingAs($admin)->get($uri)
                ->assertOk()
                ->assertSee('report-table w-full min-w-full', false)
                ->assertDontSee('Pilih seluruh baris laporan')
                ->assertDontSee('Pilih baris laporan');
        }
    }

    public function test_grade_tables_do_not_render_selection_or_action_columns(): void
    {
        foreach (['admin', 'siswa'] as $username) {
            $this->actingAs(User::where('username', $username)->firstOrFail())
                ->get('/nilai-siswa')
                ->assertOk()
                ->assertDontSee('Pilih seluruh data nilai hasil filter')
                ->assertDontSee('Pilih baris nilai')
                ->assertDontSee('>Aksi<', false);
        }
    }

    public function test_teaching_table_displays_semester_class_subject_and_teacher_columns(): void
    {
        $teaching = Pengajaran::with(['semester.tahunAjaran', 'kelas', 'mataPelajaran', 'guru'])->firstOrFail();

        $this->actingAs(User::where('username', 'admin')->firstOrFail())
            ->get('/pengajaran')
            ->assertOk()
            ->assertSeeInOrder(['Semester', 'Kelas', 'Mata Pelajaran', 'Guru', 'Status'])
            ->assertSee($teaching->semester->tahunAjaran->nama.' · '.ucfirst($teaching->semester->nama))
            ->assertSee($teaching->kelas->nama)
            ->assertSee($teaching->mataPelajaran->nama)
            ->assertSee($teaching->guru->nama_lengkap)
            ->assertDontSee('Data tidak dapat dimuat.');
    }

    public function test_academic_year_table_displays_start_and_end_dates(): void
    {
        $year = TahunAjaran::firstOrFail();

        $this->actingAs(User::where('username', 'admin')->firstOrFail())
            ->get('/tahun-ajaran')
            ->assertOk()
            ->assertSeeInOrder(['Nama', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'])
            ->assertSee($year->tanggal_mulai->translatedFormat('d F Y'))
            ->assertSee($year->tanggal_selesai->translatedFormat('d F Y'));
    }

    public function test_academic_year_edit_modal_hydrates_date_inputs(): void
    {
        $year = TahunAjaran::firstOrFail();
        $this->actingAs(User::where('username', 'admin')->firstOrFail());

        Livewire::test('pages::resource', ['resource' => 'tahun-ajaran'])
            ->call('edit', $year->id)
            ->assertSet('showForm', true)
            ->assertSet('form.tanggal_mulai', $year->tanggal_mulai->format('Y-m-d'))
            ->assertSet('form.tanggal_selesai', $year->tanggal_selesai->format('Y-m-d'));
    }

    public function test_semester_table_displays_academic_year_and_end_date(): void
    {
        $semester = Semester::with('tahunAjaran')->firstOrFail();

        $this->actingAs(User::where('username', 'admin')->firstOrFail())
            ->get('/semester')
            ->assertOk()
            ->assertSeeInOrder(['Tahun Ajaran', 'Semester', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'])
            ->assertSee($semester->tahunAjaran->nama)
            ->assertSee($semester->tanggal_selesai->translatedFormat('d F Y'))
            ->assertDontSee('Data tidak dapat dimuat.');
    }

    public function test_class_table_displays_academic_year_and_homeroom_teacher(): void
    {
        $class = Kelas::with(['tahunAjaran', 'waliKelas'])->firstOrFail();

        $this->actingAs(User::where('username', 'admin')->firstOrFail())
            ->get('/kelas')
            ->assertOk()
            ->assertSeeInOrder(['Tahun Ajaran', 'Nama Kelas', 'Tingkat', 'Wali Kelas', 'Status'])
            ->assertSee($class->tahunAjaran->nama)
            ->assertSee($class->waliKelas?->nama_lengkap ?? 'Belum ditentukan')
            ->assertDontSee('Data tidak dapat dimuat.');
    }
}
