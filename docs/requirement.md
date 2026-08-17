# Ringkasan Requirement

SISD 232 mengelola pengguna, guru, siswa, kelas dan histori penempatan, mata pelajaran, jam pelajaran, tahun ajaran, semester, pengajaran, jadwal, nilai tugas mingguan, pengaturan sekolah, serta laporan.

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
