# Alur Sistem

## Setup Akademik

1. Admin mengaktifkan tahun ajaran.
2. Admin membuat dan mengaktifkan semester pada tahun ajaran aktif.
3. Admin membuat kelas, guru, siswa, mata pelajaran, dan jam pelajaran.
4. Admin menempatkan siswa ke kelas; histori tahun sebelumnya tetap tersimpan.
5. Admin membuat pengajaran (guru + mapel + kelas + semester).
6. Admin menyusun jadwal; service menolak bentrok kelas dan guru.

## Nilai

1. Guru memilih pengajaran dan bulan.
2. Sistem memuat seluruh siswa aktif dalam satu query/batch.
3. Guru mengisi Minggu 1–4 dan menyimpan massal.
4. Service memvalidasi kepemilikan pengajaran, anggota kelas, bulan, minggu, dan rentang nilai.
5. Siswa melihat nilai melalui relasi user login ke profil siswa, tanpa parameter siswa dari browser.

## Laporan

Filter role-aware menghasilkan preview web. Print stylesheet menyembunyikan navigasi/filter. Endpoint PDF mengulang pembatasan guru/siswa dan memakai informasi pengaturan sekolah.
