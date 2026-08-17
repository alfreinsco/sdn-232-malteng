<?php

namespace Database\Factories;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class MataPelajaranFactory extends Factory
{
    protected $model = MataPelajaran::class;

    public function definition(): array
    {
        return ['kode' => fake()->unique()->bothify('MP-###'), 'nama' => fake()->unique()->words(2, true), 'status' => 'aktif'];
    }
}
