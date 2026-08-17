<?php

namespace Database\Factories;

use App\Models\NilaiTugas;
use Illuminate\Database\Eloquent\Factories\Factory;

class NilaiTugasFactory extends Factory
{
    protected $model = NilaiTugas::class;

    public function definition(): array
    {
        return ['bulan' => fake()->numberBetween(1, 12), 'minggu' => fake()->numberBetween(1, 4), 'nilai' => fake()->optional(.9)->numberBetween(60, 100)];
    }
}
