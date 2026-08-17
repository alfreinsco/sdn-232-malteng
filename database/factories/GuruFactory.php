<?php

namespace Database\Factories;

use App\Models\Guru;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        return ['nama_lengkap' => fake()->name(), 'nip' => null, 'nuptk' => fake()->unique()->numerify('################'), 'jenis_kelamin' => fake()->randomElement(['laki-laki', 'perempuan']), 'telepon' => fake()->phoneNumber(), 'alamat' => fake()->address(), 'status' => 'aktif'];
    }
}
