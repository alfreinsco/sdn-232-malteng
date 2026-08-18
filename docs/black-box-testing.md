# Black Box Testing

| No | Fitur | Skenario | Input | Hasil yang Diharapkan | Status |
|---:|---|---|---|---|---|
| 1 | Login | Kredensial benar | admin / password demo | Masuk dashboard | Lulus otomatis |
| 2 | Login | Password salah | password invalid | Ditolak dengan pesan | Lulus otomatis |
| 3 | Login | User nonaktif | akun nonaktif | Login ditolak | Lulus otomatis |
| 4 | Registrasi | Akses `/register` | URL langsung | 404 | Lulus otomatis |
| 5 | Role | Siswa buka pengguna | URL `/pengguna` | 403 | Lulus otomatis |
| 6 | Role | Guru buka tahun ajaran | URL langsung | 403 | Lulus otomatis |
| 7 | Guru | Tambah/ubah data valid | form guru | Data tersimpan | Siap uji manual |
| 8 | Siswa | NIS/NISN duplikat | identitas sama | Validasi gagal | Siap uji manual |
| 9 | Kelas | Penempatan bulk | kelas + beberapa siswa | Satu kelas aktif/tahun | Siap uji manual |
| 10 | Mapel | Kode duplikat | kode yang sama | Validasi gagal | Siap uji manual |
| 11 | Jadwal | Bentrok kelas | hari/jam/kelas sama | Ditolak | Lulus otomatis |
| 12 | Jadwal | Bentrok guru | hari/jam/guru sama | Ditolak | Lulus otomatis |
| 13 | Nilai | Input 0 dan 100 | M1=0, M2=100 | Keduanya tersimpan | Lulus otomatis |
| 14 | Nilai | Nilai kosong | M3 kosong | Tersimpan NULL | Lulus otomatis |
| 15 | Nilai | Nilai -1/101 | di luar rentang | Ditolak | Lulus otomatis |
| 16 | Nilai | Minggu 5 | key minggu 5 | Ditolak | Lulus otomatis |
| 17 | Nilai | Siswa luar kelas | ID siswa lain | Ditolak | Lulus otomatis |
| 18 | Siswa | Lihat nilai sendiri | login siswa | Hanya nilai terkait user | Lulus otomatis |
| 19 | Laporan | Filter jadwal | semester/kelas/hari | Preview sesuai filter | Siap uji manual |
| 20 | PDF | Unduh jadwal/nilai | endpoint PDF | File PDF A4 | Lulus otomatis |
| 21 | Print | Cetak laporan | tombol Cetak | Sidebar/filter tersembunyi | Siap uji manual |
| 22 | Mobile | Input nilai | viewport 375px | Tabel scroll, nama sticky | Siap uji manual |
| 23 | Jam Pelajaran | Membuat jam tanpa mengisi urutan | nama dan rentang jam valid | Urutan berikutnya terisi otomatis | Lulus otomatis |
| 24 | Jam Pelajaran | Menggeser urutan | drag baris jam | Urutan baru tersimpan tanpa duplicate key | Lulus otomatis |
| 25 | Status Master | Nonaktifkan guru/siswa/master | tombol Nonaktifkan | Status berubah, record tidak terhapus | Lulus otomatis |
| 26 | Tabel | Select All pada hasil filter lebih dari satu halaman | filter + checkbox header | Seluruh dataset hasil filter terpilih | Lulus otomatis |
| 27 | Tabel | Kecualikan satu baris setelah Select All | uncheck satu baris | Jumlah terpilih berkurang satu lintas halaman | Lulus otomatis |
| 28 | Tabel | Refresh URL berisi state tabel | search/filter/sort/page/per_page/visible_columns | State valid dipulihkan dan nilai tidak aman disanitasi | Lulus otomatis |
| 29 | Pagination | Nomor halaman dan per-page di luar batas | halaman 0 / per-page terlalu besar | Nilai dibatasi ke rentang aman | Lulus otomatis |
| 30 | Nilai/Laporan | Periksa checkbox dan Aksi | halaman nilai dan laporan | Checkbox/Aksi tidak ditampilkan | Lulus otomatis |
| 31 | Pengajaran | Filter bertingkat | tahun ajaran, semester, kelas, mapel | Hasil sesuai filter dan state tersimpan di URL | Lulus otomatis |
| 32 | Jadwal | Pilihan pengajaran bertingkat | tahun ajaran → semester → kelas | Hanya pengajaran yang sesuai yang tampil | Lulus otomatis |
| 33 | Kelas | Lihat anggota kelas | tombol Lihat Siswa | Halaman roster kelas tampil | Lulus otomatis |
| 34 | Kelas | Tambah dan keluarkan siswa | siswa valid | Keanggotaan berubah tanpa menghapus siswa | Lulus otomatis |
| 35 | Penempatan | Filter siswa sudah/belum memiliki kelas | status penempatan | Warna, kelas aktif, dan hasil filter sesuai | Lulus otomatis |
| 36 | Dashboard | Render dashboard seluruh role | admin/guru/siswa/kepala | Metrik dan akses cepat sesuai role tampil | Lulus otomatis |
| 37 | Mobile | Geser tabel sampai kolom Aksi | viewport 390px | Data utama tidak tertutup; tombol aksi ikon tampil di ujung kanan | Siap uji manual |
