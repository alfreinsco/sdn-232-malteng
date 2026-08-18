# Alur Sistem

## Setup Akademik

1. Admin mengaktifkan tahun ajaran.
2. Admin membuat dan mengaktifkan semester pada tahun ajaran aktif.
3. Admin membuat kelas, guru, siswa, mata pelajaran, dan jam pelajaran.
4. Admin mengatur urutan jam pelajaran melalui drag-and-drop; urutan tersimpan otomatis.
5. Admin menempatkan siswa melalui halaman Penempatan Siswa atau halaman Anggota Kelas; histori tahun sebelumnya tetap tersimpan.
6. Admin membuat pengajaran dengan memilih tahun ajaran, semester, dan kelas secara bertingkat, lalu memilih mata pelajaran serta guru.
7. Admin menyusun jadwal dengan pilihan periode/kelas bertingkat; service menolak bentrok kelas dan guru.

## Nilai

1. Guru memilih pengajaran dan bulan.
2. Sistem memuat seluruh siswa aktif dalam satu query/batch.
3. Guru mengisi Minggu 1–4 dan menyimpan massal.
4. Service memvalidasi kepemilikan pengajaran, anggota kelas, bulan, minggu, dan rentang nilai.
5. Siswa melihat nilai melalui relasi user login ke profil siswa, tanpa parameter siswa dari browser.

## Laporan

Filter role-aware menghasilkan preview web. Print stylesheet menyembunyikan navigasi/filter. Endpoint PDF mengulang pembatasan guru/siswa dan memakai informasi pengaturan sekolah.

## Tabel Data

1. State pencarian, filter, sort, halaman, per-page, dan kolom terlihat dibaca dari URL.
2. Query server-side menerapkan state yang sudah divalidasi dan mengambil satu halaman data.
3. Pemilihan seluruh data menggunakan mode seluruh dataset hasil filter dengan daftar pengecualian, bukan mengambil semua ID ke browser.
4. Perubahan filter membersihkan selection agar aksi massal tidak memakai dataset lama.
5. Pada mobile, pengguna menggeser tabel secara horizontal; kolom Aksi tidak menutupi kolom utama.
