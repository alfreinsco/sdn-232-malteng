# Ringkasan Requirement

SD Negeri 232 Maluku Tengah mengelola pengguna, guru, siswa, kelas dan histori penempatan, mata pelajaran, jam pelajaran, tahun ajaran, semester, pengajaran, jadwal, nilai tugas mingguan, pengaturan sekolah, serta laporan.

## Aturan Utama

- Role: `admin`, `guru`, `siswa`, `kepala_sekolah`.
- Satu tahun ajaran dan satu semester aktif pada satu waktu.
- Guru dan kelas tidak boleh mengalami bentrok jadwal pada semester, hari, dan jam yang sama.
- Nilai disimpan per pengajaran, siswa, bulan 1–12, dan minggu 1–4.
- `NULL` berarti belum dinilai; nilai `0` adalah nilai sah.
- Rata-rata hanya memakai minggu yang memiliki nilai.
- Guru hanya mengelola nilai pengajarannya; siswa hanya melihat data sendiri.
- Data akademik historis dipertahankan melalui status aktif/nonaktif dan foreign key restriktif.
- Public registration dinonaktifkan.

## Modul

Dashboard role-aware, master data, penempatan siswa, pengajaran, jadwal, bulk input nilai, monitoring nilai, laporan web, print, PDF, pengaturan sekolah, dan profil.

## Standar Tabel dan Antarmuka Terbaru

- Tabel utama mendukung pencarian global, filter searchable, pengurutan tiga keadaan, pagination, input nomor halaman, jumlah data per halaman, dan pengaturan visibilitas kolom.
- State tabel disimpan pada query parameter URL sehingga refresh dan tautan bersama mempertahankan pencarian, filter, urutan, halaman, per-page, dan kolom terlihat.
- Tabel operasional mendukung pemilihan seluruh dataset hasil filter, toolbar aksi massal, loading skeleton, empty state, filtered-empty state, dan error state.
- Kolom Aksi sticky pada desktop dan menjadi kolom biasa dengan tombol ikon ringkas pada mobile agar tidak menutupi data.
- Tabel mobile menggunakan petunjuk geser horizontal dan scroll vertikal halaman.
- Dashboard menampilkan metrik role-aware, status akademik, progres kelengkapan nilai, jadwal hari ini, dan akses cepat.
- Urutan jam pelajaran diatur melalui drag-and-drop tabel; formulir tidak meminta nomor urutan.
- Kelas memiliki filter tahun ajaran/tingkat, jumlah siswa, dan halaman anggota kelas untuk menambah atau mengeluarkan siswa.
- Pengajaran dan jadwal memakai pilihan bertingkat tahun ajaran → semester → kelas agar opsi selalu konsisten.
