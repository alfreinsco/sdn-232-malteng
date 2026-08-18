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
            ->assertSee('Belajar lebih')
            ->assertSee('siswa sekolah dasar')
            ->assertSee('Ayo Masuk')
            ->assertSee('images/landing/kids-learning.svg')
            ->assertSee('SD Negeri 232 Maluku Tengah')
            ->assertDontSee('SISD'.' 232');

        $this->assertFileExists(public_path('images/landing/kids-learning.svg'));
        $this->assertFileExists(public_path('images/landing/mobile-dashboard.webp'));
    }

    public function test_authenticated_users_are_redirected_to_the_dashboard(): void
    {
        $this->actingAs(User::where('username', 'admin')->first())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
