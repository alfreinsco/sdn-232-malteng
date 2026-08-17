<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UbahStatusMasterData
{
    public function __construct(
        private readonly AktivasiTahunAjaran $aktivasiTahunAjaran,
        private readonly AktivasiSemester $aktivasiSemester,
    ) {}

    public function handle(Model $item, string $status): void
    {
        if (! in_array($status, ['aktif', 'nonaktif'], true)) {
            throw ValidationException::withMessages(['status' => 'Status data tidak valid.']);
        }

        if ($item instanceof TahunAjaran) {
            if ($status === 'aktif') {
                $this->aktivasiTahunAjaran->handle($item);

                return;
            }

            DB::transaction(function () use ($item): void {
                $item->semester()->where('status', 'aktif')->update(['status' => 'nonaktif']);
                $item->update(['status' => 'nonaktif']);
            });

            return;
        }

        if ($item instanceof Semester && $status === 'aktif') {
            $this->aktivasiSemester->handle($item);

            return;
        }

        $item->update(['status' => $status]);
    }
}
