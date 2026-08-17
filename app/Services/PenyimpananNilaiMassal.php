<?php

namespace App\Services;

use App\Models\Pengajaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PenyimpananNilaiMassal
{
    public function handle(User $user, Pengajaran $pengajaran, int $bulan, array $nilai): void
    {
        Validator::make(['bulan' => $bulan, 'nilai' => $nilai], [
            'bulan' => ['required', 'integer', 'between:1,12'], 'nilai' => ['array'], 'nilai.*' => ['array:1,2,3,4'],
            'nilai.*.*' => ['nullable', 'numeric', 'between:0,100'],
        ])->validate();
        if ($user->hasRole('guru') && $user->guru?->id !== $pengajaran->guru_id) {
            throw ValidationException::withMessages(['pengajaran' => 'Anda tidak berwenang mengisi nilai pengajaran ini.']);
        }
        $siswaValid = $pengajaran->kelas->siswaKelas()->where('status', 'aktif')->pluck('siswa_id')->map(fn ($id) => (int) $id)->all();
        $rows = [];
        foreach ($nilai as $siswaId => $mingguan) {
            if (! in_array((int) $siswaId, $siswaValid, true)) {
                throw ValidationException::withMessages(['siswa' => 'Terdapat siswa yang bukan anggota kelas pengajaran.']);
            }
            foreach (range(1, 4) as $minggu) {
                $value = $mingguan[$minggu] ?? null;
                $rows[] = ['pengajaran_id' => $pengajaran->id, 'siswa_id' => (int) $siswaId, 'bulan' => $bulan,
                    'minggu' => $minggu, 'nilai' => $value === '' ? null : $value, 'dibuat_oleh' => $user->id,
                    'created_at' => now(), 'updated_at' => now()];
            }
        }
        DB::transaction(fn () => DB::table('nilai_tugas')->upsert($rows,
            ['pengajaran_id', 'siswa_id', 'bulan', 'minggu'], ['nilai', 'dibuat_oleh', 'updated_at']));
    }
}
