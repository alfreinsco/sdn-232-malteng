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

Permission granular disimpan oleh Spatie Laravel Permission. Route memakai middleware `permission`/`role`; service nilai dan query role-aware memberi lapisan authorization tambahan untuk mencegah IDOR.
