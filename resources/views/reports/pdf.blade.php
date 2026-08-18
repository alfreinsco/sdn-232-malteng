<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 13mm 11mm; }
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1, h2, p { margin: 0; }
        h1 { color: #0f172a; font-size: 15px; line-height: 1.25; }
        h2 { color: #0f172a; font-size: 13px; }
        .school-header { width: 100%; margin-bottom: 9px; border-collapse: collapse; }
        .school-header td { padding: 0; border: 0; vertical-align: middle; }
        .logo-cell { width: 62px; }
        .logo { display: block; width: 48px; height: 58px; object-fit: contain; }
        .eyebrow { margin-bottom: 3px; color: #0369a1; font-size: 8px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .address { margin-top: 3px; color: #64748b; font-size: 8px; }
        .title-band { margin: 9px 0; padding: 9px 10px; border-top: 2px solid #075985; border-bottom: 1px solid #cbd5e1; }
        .title-band table { width: 100%; border-collapse: collapse; }
        .title-band td { padding: 0; border: 0; vertical-align: bottom; }
        .printed { color: #64748b; font-size: 8px; text-align: right; }
        .filter-grid { width: 100%; margin-bottom: 10px; border: 1px solid #cbd5e1; border-collapse: collapse; }
        .filter-grid td { width: 25%; padding: 6px 7px; border-right: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .filter-grid td:last-child { border-right: 0; }
        .filter-label { display: block; margin-bottom: 2px; color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .filter-value { color: #1e293b; font-size: 8px; font-weight: bold; }
        .report-table { width: 100%; border: 1px solid #94a3b8; border-collapse: collapse; }
        .report-table th, .report-table td { padding: 6px 6px; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; text-align: left; vertical-align: middle; }
        .report-table th { background: #075985; color: #fff; font-size: 8px; text-transform: uppercase; }
        .report-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .report-table tr { page-break-inside: avoid; }
        .num { text-align: center !important; font-variant-numeric: tabular-nums; }
        .average { background: #e0f2fe !important; color: #075985; font-weight: bold; }
        .day { color: #075985; font-weight: bold; text-transform: capitalize; }
        .subject, .student { color: #0f172a; font-weight: bold; }
        .footer { margin-top: 9px; color: #64748b; font-size: 8px; text-align: right; }
    </style>
</head>
<body>
    @php
        $semester = $filterLabels['semester'];
        $semesterText = $semester ? $semester->tahunAjaran->nama.' - '.ucfirst($semester->nama) : 'Semua semester';
        $kelasText = $filterLabels['kelas']?->nama ?? 'Semua kelas';
        $thirdText = $jenis === 'jadwal' ? ($filterLabels['guru']?->nama_lengkap ?? 'Semua guru') : ($filterLabels['mapel']?->nama ?? 'Semua mata pelajaran');
        $periodText = $jenis === 'jadwal' ? (isset($filter['hari']) && $filter['hari'] ? ucfirst($filter['hari']) : 'Semua hari') : \Carbon\Carbon::create()->month((int)($filter['bulan'] ?? now()->month))->translatedFormat('F');
    @endphp
    <table class="school-header"><tr><td class="logo-cell"><img class="logo" src="{{ $sekolah?->logo && is_file(public_path('storage/'.$sekolah->logo)) ? public_path('storage/'.$sekolah->logo) : public_path('logo-malteng.png') }}" alt="Logo sekolah"></td><td><p class="eyebrow">Dokumen Akademik Sekolah</p><h1>{{ strtoupper($sekolah?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah') }}</h1><p class="address">{{ $sekolah?->alamat }}</p></td></tr></table>
    <div class="title-band"><table><tr><td><h2>LAPORAN {{ strtoupper($jenis) }} {{ $jenis === 'nilai' ? 'TUGAS SISWA' : '' }}</h2></td><td class="printed">Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIT</td></tr></table></div>
    <table class="filter-grid"><tr><td><span class="filter-label">Semester</span><span class="filter-value">{{ $semesterText }}</span></td><td><span class="filter-label">Kelas</span><span class="filter-value">{{ $kelasText }}</span></td><td><span class="filter-label">{{ $jenis === 'jadwal' ? 'Guru' : 'Mata Pelajaran' }}</span><span class="filter-value">{{ $thirdText }}</span></td><td><span class="filter-label">{{ $jenis === 'jadwal' ? 'Hari' : 'Bulan' }}</span><span class="filter-value">{{ $periodText }}</span></td></tr></table>
    <table class="report-table">
        <thead>
        @if($jenis === 'jadwal')
            <tr><th>Hari</th><th>Jam</th><th>Kelas</th><th>Mata Pelajaran</th><th>Guru</th></tr>
        @else
            <tr><th>No</th><th>NIS/NISN</th><th>Nama Siswa</th><th>Mata Pelajaran</th><th>M1</th><th>M2</th><th>M3</th><th>M4</th><th>Rata-rata</th></tr>
        @endif
        </thead>
        <tbody>
        @if($jenis === 'jadwal')
            @foreach($rows as $row)
                <tr><td class="day">{{ ucfirst($row->hari) }}</td><td class="num">{{ substr($row->jamPelajaran->jam_mulai, 0, 5) }}-{{ substr($row->jamPelajaran->jam_selesai, 0, 5) }}</td><td class="num">{{ $row->pengajaran->kelas->nama }}</td><td class="subject">{{ $row->pengajaran->mataPelajaran->nama }}</td><td>{{ $row->pengajaran->guru->nama_lengkap }}</td></tr>
            @endforeach
        @else
            @foreach($rows as $group)
                @php($first = $group->first())
                @php($available = $group->whereNotNull('nilai')->pluck('nilai'))
                <tr><td class="num">{{ $loop->iteration }}</td><td>{{ $first->siswa->nis ?? $first->siswa->nisn ?? '-' }}</td><td class="student">{{ $first->siswa->nama_lengkap }}</td><td class="subject">{{ $first->pengajaran->mataPelajaran->nama }}</td>@foreach(range(1, 4) as $week)<td class="num">{{ $group->firstWhere('minggu', $week)?->nilai ?? '-' }}</td>@endforeach<td class="num average">{{ $available->isEmpty() ? '-' : number_format($available->avg(), 2) }}</td></tr>
            @endforeach
        @endif
        </tbody>
    </table>
    <p class="footer">SD Negeri 232 Maluku Tengah - Halaman laporan resmi sekolah</p>
</body>
</html>
