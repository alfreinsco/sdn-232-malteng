# Role dan Permission

| Area | Admin | Guru | Siswa | Kepala Sekolah |
|---|---:|---:|---:|---:|
| Dashboard | Penuh | Milik sendiri | Milik sendiri | Monitoring |
| Pengguna/master | CRUD | Baca terbatas | Tidak | Baca terbatas |
| Pengajaran | CRUD | Pengajaran sendiri | Tidak | Baca |
| Jadwal | CRUD | Jadwal sendiri | Jadwal kelas | Seluruh jadwal |
| Nilai | Monitoring | Input/update pengajaran sendiri | Nilai sendiri | Monitoring |
| Laporan/PDF | Seluruh data | Data pengajaran sendiri | Data sendiri | Seluruh data |
| Pengaturan sekolah | CRUD | Tidak | Tidak | Tidak |

## Batasan Aksi pada Antarmuka

- Checkbox dan toolbar aksi massal hanya tampil pada tabel yang memang memiliki aksi kelola.
- Tabel nilai dan laporan tidak menampilkan checkbox maupun kolom Aksi.
- Admin dapat membuka halaman Anggota Kelas, menambah siswa, dan mengeluarkan siswa dari kelas tanpa menghapus data siswa.
- Kepala Sekolah menggunakan tabel monitoring dan laporan dalam mode baca.
- Guru hanya memperoleh pilihan pengajaran miliknya pada filter/input nilai.
- Siswa selalu memperoleh jadwal dan nilai melalui relasi akun login, bukan ID siswa dari browser.

Permission granular disimpan oleh Spatie Laravel Permission. Route memakai middleware `permission`/`role`; service nilai dan query role-aware memberi lapisan authorization tambahan untuk mencegah IDOR.
