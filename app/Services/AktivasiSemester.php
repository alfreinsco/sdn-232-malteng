<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AktivasiSemester
{
    public function handle(Semester $semester): void
    {
        if ($semester->tahunAjaran->status !== 'aktif') {
            throw ValidationException::withMessages(['semester' => 'Semester hanya dapat diaktifkan pada tahun ajaran aktif.']);
        }
        DB::transaction(function () use ($semester): void {
            Semester::whereKeyNot($semester->id)->where('status', 'aktif')->update(['status' => 'nonaktif']);
            $semester->update(['status' => 'aktif']);
        });
    }
}
