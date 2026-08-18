# Database

Database utama adalah MySQL. Semua konfigurasi berasal dari environment variable. Migration mendukung SQLite in-memory untuk automated test.

## Integritas

- Foreign key akademik menggunakan `restrictOnDelete` agar histori tidak hilang.
- Relasi akun opsional menggunakan `nullOnDelete`.
- Username, kode identitas opsional, dan kombinasi domain penting memakai unique index.
- `siswa_kelas` menyimpan histori kelas; service penempatan menonaktifkan penempatan lain pada tahun ajaran yang sama.
- Aktivasi tahun ajaran dan semester dijalankan dalam transaction.
- Bulk nilai menggunakan transaction dan `upsert` pada unique key pengajaran+siswa+bulan+minggu.
- Validasi bentrok jadwal memeriksa kelas dan guru dalam semester yang sama.
- `jam_pelajaran.urutan` tetap unik di database; antarmuka mengelolanya otomatis melalui proses reorder transactional sehingga pengguna tidak mengetik urutan manual.
- Jumlah siswa pada daftar kelas dihitung dari `siswa_kelas` berstatus aktif, bukan kolom duplikat pada tabel `kelas`.
- Filter tahun ajaran pada kelas, pengajaran, jadwal, nilai, dan laporan memakai relasi periode yang sudah tersedia sehingga histori tetap dapat dibaca.

## Nilai

Kolom `nilai` nullable `decimal(5,2)`. Aplikasi menerima 0–100 dan tidak mengubah nilai kosong menjadi nol.

Struktur lengkap dan relasi dapat dibuka melalui `docs/erd.dbml`.
