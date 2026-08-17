<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        $t = fake()->numberBetween(1, 6);

        return ['tahun_ajaran_id' => TahunAjaran::factory(), 'nama' => $t.' A', 'tingkat' => $t, 'status' => 'aktif'];
    }
}
