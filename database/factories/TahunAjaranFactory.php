<?php

namespace Database\Factories;

use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class TahunAjaranFactory extends Factory
{
    protected $model = TahunAjaran::class;

    public function definition(): array
    {
        $y = fake()->numberBetween(2020, 2030);

        return ['nama' => $y.'/'.($y + 1), 'tanggal_mulai' => "$y-07-01", 'tanggal_selesai' => ($y + 1).'-06-30', 'status' => 'nonaktif'];
    }
}
