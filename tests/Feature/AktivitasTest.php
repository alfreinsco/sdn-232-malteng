<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\MataPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Support\DemoCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AktivitasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_and_logout_are_recorded_without_storing_failed_attempts(): void
    {
        $this->post('/login', ['login' => 'admin', 'password' => DemoCatalog::PASSWORD])->assertRedirect('/dashboard');
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->assertDatabaseHas('aktivitas', ['user_id' => $admin->id, 'type' => 'login']);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertDatabaseHas('aktivitas', ['user_id' => $admin->id, 'type' => 'logout']);

        $this->post('/login', ['login' => 'admin', 'password' => 'salah'])->assertSessionHasErrors('login');
        $this->assertDatabaseMissing('aktivitas', ['type' => 'login_gagal']);
    }

    public function test_page_access_is_ignored_while_model_changes_are_recorded_compactly(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin)->get('/mata-pelajaran')->assertOk();
        $this->assertDatabaseMissing('aktivitas', ['user_id' => $admin->id, 'type' => 'akses']);

        $subject = MataPelajaran::create(['kode' => 'AUD', 'nama' => 'Audit Sistem', 'status' => 'aktif']);
        $this->assertDatabaseHas('aktivitas', ['user_id' => $admin->id, 'type' => 'tambah', 'subject_id' => $subject->id]);
        $this->assertNull(Aktivitas::where('type', 'tambah')->where('subject_id', $subject->id)->value('properties'));
        $subject->update(['nama' => 'Audit Aktivitas']);
        $this->assertDatabaseHas('aktivitas', ['user_id' => $admin->id, 'type' => 'ubah', 'subject_id' => $subject->id]);
        $this->assertSame(['nama'], Aktivitas::where('type', 'ubah')->where('subject_id', $subject->id)->value('properties')['changed_fields']);
        $subject->delete();
        $this->assertDatabaseHas('aktivitas', ['user_id' => $admin->id, 'type' => 'hapus', 'subject_id' => $subject->id]);
    }

    public function test_activity_visibility_is_scoped_by_role(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $student = User::where('username', 'siswa')->firstOrFail();
        Aktivitas::create(['user_id' => $admin->id, 'actor_name' => $admin->name, 'role' => 'admin', 'type' => 'aksi', 'description' => 'Aktivitas rahasia admin']);
        Aktivitas::create(['user_id' => $student->id, 'actor_name' => $student->name, 'role' => 'siswa', 'type' => 'login', 'description' => 'Aktivitas pribadi siswa']);

        $this->actingAs($student)->get('/aktivitas')->assertOk()->assertSee('Aktivitas pribadi siswa')->assertDontSee('Aktivitas rahasia admin');
        $this->actingAs($admin)->get('/aktivitas')->assertOk()->assertSee('Aktivitas pribadi siswa')->assertSee('Aktivitas rahasia admin');
    }

    public function test_every_role_can_open_their_activity_page(): void
    {
        foreach (['admin', 'guru1', 'siswa', 'kepala'] as $username) {
            $this->actingAs(User::where('username', $username)->firstOrFail())
                ->get('/aktivitas')
                ->assertOk();
        }
    }

    public function test_old_activity_is_pruned_according_to_retention_policy(): void
    {
        Aktivitas::create(['type' => 'login', 'description' => 'Aktivitas lama', 'created_at' => now()->subDays(181), 'updated_at' => now()->subDays(181)]);
        Aktivitas::create(['type' => 'login', 'description' => 'Aktivitas baru']);

        $this->artisan('model:prune', ['--model' => [Aktivitas::class]])->assertSuccessful();

        $this->assertDatabaseMissing('aktivitas', ['description' => 'Aktivitas lama']);
        $this->assertDatabaseHas('aktivitas', ['description' => 'Aktivitas baru']);
    }
}
