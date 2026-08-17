<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 16mm 12mm; }
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1, h2, p { margin: 0; text-align: center; }
        h1 { font-size: 15px; } h2 { margin-top: 6px; font-size: 13px; }
        .logo { display: block; width: 52px; height: 52px; margin: 0 auto 6px; object-fit: contain; }
        .meta { margin: 10px 0; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px 6px; border: 1px solid #94a3b8; text-align: left; }
        th { background: #e0f2fe; font-size: 9px; text-transform: uppercase; }
        .num { text-align: center; } .footer { margin-top: 10px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    @if($sekolah?->logo && is_file(public_path('storage/'.$sekolah->logo)))
        <img class="logo" src="{{ public_path('storage/'.$sekolah->logo) }}" alt="Logo sekolah">
    @endif
    <h1>{{ strtoupper($sekolah?->nama_sekolah ?? 'SD Negeri 232 Maluku Tengah') }}</h1>
    <p>{{ $sekolah?->alamat }}</p>
    <h2>LAPORAN {{ strtoupper($jenis) }} {{ $jenis === 'nilai' ? 'TUGAS SISWA' : '' }}</h2>
    <p class="meta">Tanggal cetak: {{ now()->translatedFormat('d F Y H:i') }} WIT</p>
    <table>
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
                <tr><td>{{ ucfirst($row->hari) }}</td><td>{{ substr($row->jamPelajaran->jam_mulai, 0, 5) }}-{{ substr($row->jamPelajaran->jam_selesai, 0, 5) }}</td><td>{{ $row->pengajaran->kelas->nama }}</td><td>{{ $row->pengajaran->mataPelajaran->nama }}</td><td>{{ $row->pengajaran->guru->nama_lengkap }}</td></tr>
            @endforeach
        @else
            @foreach($rows as $group)
                @php($first = $group->first())
                @php($available = $group->whereNotNull('nilai')->pluck('nilai'))
                <tr><td class="num">{{ $loop->iteration }}</td><td>{{ $first->siswa->nis ?? $first->siswa->nisn ?? '-' }}</td><td>{{ $first->siswa->nama_lengkap }}</td><td>{{ $first->pengajaran->mataPelajaran->nama }}</td>@foreach(range(1, 4) as $week)<td class="num">{{ $group->firstWhere('minggu', $week)?->nilai ?? '-' }}</td>@endforeach<td class="num">{{ $available->isEmpty() ? '-' : number_format($available->avg(), 2) }}</td></tr>
            @endforeach
        @endif
        </tbody>
    </table>
    <p class="footer">SISD 232 - Halaman laporan resmi sekolah</p>
</body>
</html>
