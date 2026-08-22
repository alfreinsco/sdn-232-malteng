<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_landing_page_is_public(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Kelola Jadwal Pelajaran')
            ->assertSee('Nilai Siswa dengan')
            ->assertSee('Masuk Sistem')
            ->assertSee('Sistem Informasi Sekolah Dasar')
            ->assertSee('Fitur Unggulan SISDAR')
            ->assertSee('Akses Sesuai Peran')
            ->assertSee('SD Negeri 232 Maluku Tengah')
            ->assertSee('SISDAR');

        $this->assertFileExists(public_path('images/landing/kids-learning.svg'));
        $this->assertFileExists(public_path('images/landing/mobile-dashboard.webp'));
    }

    public function test_authenticated_users_are_redirected_to_the_dashboard(): void
    {
        $this->actingAs(User::where('username', 'admin')->first())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_landing_page_can_always_be_opened_from_the_back_button(): void
    {
        $this->get('/awal')->assertOk()->assertSee('Kelola Jadwal Pelajaran');

        $this->actingAs(User::where('username', 'admin')->first())
            ->get('/awal')
            ->assertOk()
            ->assertSee('Kelola Jadwal Pelajaran');
    }

    public function test_user_guide_pdf_can_be_opened(): void
    {
        $this->get('/bantuan')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
