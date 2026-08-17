<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\TahunAjaran;

class PeriodeAktif
{
    public function tahunAjaran(): ?TahunAjaran
    {
        return TahunAjaran::aktif()->first();
    }

    public function semester(): ?Semester
    {
        return Semester::with('tahunAjaran')->aktif()->first();
    }
}
