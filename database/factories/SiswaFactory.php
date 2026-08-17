<?php

namespace Database\Factories;

use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        return ['nis' => fake()->unique()->numerify('26####'), 'nisn' => fake()->unique()->numerify('##########'), 'nama_lengkap' => fake()->name(), 'jenis_kelamin' => fake()->randomElement(['laki-laki', 'perempuan']), 'tempat_lahir' => fake()->city(), 'tanggal_lahir' => fake()->dateTimeBetween('-12 years', '-6 years'), 'alamat' => fake()->address(), 'status' => 'aktif'];
    }
}
