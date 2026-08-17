<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiTugas;
use App\Models\PengaturanSekolah;
use App\Models\Semester;
use App\Models\Siswa;
use App\Services\GenerateLaporan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function pdf(Request $request, string $jenis)
    {
        $user = $request->user();
        $filter = $request->only(['semester_id', 'kelas_id', 'guru_id', 'mapel_id', 'hari', 'bulan', 'siswa_id']);
        if ($user->hasRole('guru')) {
            $filter['guru_id'] = $user->guru?->id;
        }
        if ($user->hasRole('siswa')) {
            $filter['siswa_id'] = $user->siswa?->id;
            $filter['kelas_id'] = $user->siswa?->penempatanKelas()->where('status', 'aktif')->value('kelas_id');
        }

        $rows = $jenis === 'jadwal'
            ? app(GenerateLaporan::class)->jadwal($filter)
            : NilaiTugas::with(['siswa', 'pengajaran.semester.tahunAjaran', 'pengajaran.kelas', 'pengajaran.guru', 'pengajaran.mataPelajaran'])
                ->where('bulan', $filter['bulan'] ?? now()->month)
                ->when($filter['semester_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('semester_id', $v)))
                ->when($filter['kelas_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('kelas_id', $v)))
                ->when($filter['guru_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('guru_id', $v)))
                ->when($filter['mapel_id'] ?? null, fn ($q, $v) => $q->whereHas('pengajaran', fn ($x) => $x->where('mata_pelajaran_id', $v)))
                ->when($filter['siswa_id'] ?? null, fn ($q, $v) => $q->where('siswa_id', $v))
                ->get()->groupBy(fn ($nilai) => $nilai->pengajaran_id.'-'.$nilai->siswa_id);

        return Pdf::loadView('reports.pdf', [
            'jenis' => $jenis,
            'rows' => $rows,
            'sekolah' => PengaturanSekolah::first(),
            'filter' => $filter,
            'filterLabels' => [
                'semester' => isset($filter['semester_id']) ? Semester::with('tahunAjaran')->find($filter['semester_id']) : null,
                'kelas' => isset($filter['kelas_id']) ? Kelas::find($filter['kelas_id']) : null,
                'guru' => isset($filter['guru_id']) ? Guru::find($filter['guru_id']) : null,
                'mapel' => isset($filter['mapel_id']) ? MataPelajaran::find($filter['mapel_id']) : null,
                'siswa' => isset($filter['siswa_id']) ? Siswa::find($filter['siswa_id']) : null,
            ],
        ])->setPaper('a4', $jenis === 'nilai' ? 'landscape' : 'portrait')
            ->download('laporan-'.$jenis.'-'.now()->format('Ymd').'.pdf');
    }
}
