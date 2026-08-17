<?php

namespace App\Services;

use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class AktivasiTahunAjaran
{
    public function handle(TahunAjaran $tahunAjaran): void
    {
        DB::transaction(function () use ($tahunAjaran): void {
            TahunAjaran::whereKeyNot($tahunAjaran->id)->where('status', 'aktif')->update(['status' => 'nonaktif']);
            $tahunAjaran->update(['status' => 'aktif']);
        });
    }
}
