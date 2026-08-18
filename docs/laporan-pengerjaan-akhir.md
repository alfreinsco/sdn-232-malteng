# Laporan Penyelesaian Pengerjaan dan Demonstrasi Tahap Akhir

## Sistem Informasi Jadwal Pelajaran dan Nilai Siswa Berbasis Web

**Instansi:** SD Negeri 232 Maluku Tengah  
**Ditujukan kepada:** Awati Fujihani Lessy  
**Tanggal laporan:** 18 Agustus 2026  
**Status:** Selesai secara teknis, siap demonstrasi akhir dan verifikasi penerimaan client

## 1. Tujuan Dokumen

Dokumen ini mencatat hasil penyelesaian pengembangan aplikasi berdasarkan ruang lingkup Proposal Penawaran Pengembangan Sistem Informasi. Laporan digunakan sebagai bahan demonstrasi tahap akhir, pemeriksaan bersama client, pencatatan revisi minor apabila ada, dan serah terima setelah hasil demonstrasi diterima.

## 2. Ringkasan Hasil

Aplikasi telah mengintegrasikan pengelolaan data akademik, jadwal pelajaran, nilai tugas Minggu 1-4, monitoring, laporan, print, dan PDF dalam satu sistem berbasis web. Hak akses dibedakan untuk Admin, Guru, Siswa, dan Kepala Sekolah melalui autentikasi serta authorization server-side.

Status implementasi internal telah mencapai kondisi siap demonstrasi akhir. Penerimaan final tetap dilakukan melalui pemeriksaan dan persetujuan client; laporan ini tidak menggantikan berita acara penerimaan.

## 3. Ruang Lingkup yang Diselesaikan

| Kelompok | Hasil Implementasi | Status |
|---|---|---|
| Autentikasi | Login, logout, akun nonaktif ditolak, registrasi publik dinonaktifkan | Selesai |
| Hak akses | Role Admin, Guru, Siswa, Kepala Sekolah dan permission tiap modul | Selesai |
| Master akademik | Pengguna, guru, siswa, kelas, mata pelajaran, jam pelajaran | Selesai |
| Periode akademik | Tahun ajaran, semester, periode aktif, histori periode | Selesai |
| Penempatan siswa | Penempatan massal, indikator sudah/belum memiliki kelas, halaman anggota kelas, dan histori kelas | Selesai |
| Pengajaran | Relasi guru, kelas, mata pelajaran, semester, filter periode, dan form pilihan bertingkat | Selesai |
| Jadwal | CRUD, filter, tampilan mingguan, validasi bentrok kelas/guru/duplikat | Selesai |
| Nilai | Input massal, M1-M4, bulan, nilai 0-100/NULL, rata-rata nilai terisi | Selesai |
| Dashboard | Dashboard role-aware, metrik utama, status akademik, progres nilai, jadwal, dan akses cepat | Selesai |
| Monitoring | Monitoring jadwal dan nilai untuk Admin/Kepala Sekolah | Selesai |
| Laporan | Laporan jadwal dan nilai dengan filter, print, serta PDF | Selesai |
| Pengaturan | Identitas sekolah, logo, kepala sekolah, dan profil pengguna | Selesai |
| Antarmuka | Responsif desktop/mobile, tabel reusable, URL state, bulk action, drag-and-drop jam, loading dan empty state | Selesai |
| Dokumentasi | ERD, database, role, alur, deployment, pengujian, panduan, presentasi | Selesai |

## 4. Ketentuan Bisnis Penting

- Satu kelas tidak dapat memiliki dua jadwal pada hari dan jam yang sama.
- Seorang guru tidak dapat mengajar dua kelas pada waktu yang sama.
- Jadwal identik ditolak.
- Guru hanya dapat mengelola nilai pada pengajaran yang menjadi tanggung jawabnya.
- Siswa hanya dapat memperoleh jadwal kelas dan nilai miliknya sendiri.
- Nilai `0` merupakan nilai yang sah, sedangkan kolom kosong disimpan sebagai `NULL`.
- Rata-rata dihitung hanya dari minggu yang telah memiliki nilai.
- Riwayat kelas dan data akademik periode lama tetap dapat dibaca.
- Urutan jam pelajaran diberikan otomatis dan dapat diubah melalui drag-and-drop tanpa input nomor manual.
- Aksi Nonaktifkan hanya mengubah status dan tidak menghapus record.
- Seluruh tabel operasional memiliki pencarian, filter searchable, sorting, pagination, column toggle, dan state URL.
- Tabel nilai dan laporan bersifat read-only dari sisi struktur tabel: tidak memiliki checkbox atau kolom Aksi.

## 5. Teknologi dan Arsitektur

| Komponen | Implementasi |
|---|---|
| Backend | PHP 8.4 dan Laravel 13 |
| Antarmuka | Livewire 4 single-file components, Blade, Tailwind CSS 4 |
| Basis data | MySQL untuk development/production; SQLite in-memory untuk test terisolasi |
| Hak akses | Spatie Laravel Permission |
| PDF | DomPDF |
| Build frontend | Vite 8 |
| Pengujian | PHPUnit 12 |

## 6. Verifikasi Teknis Terakhir

Verifikasi ulang dilakukan pada 18 Agustus 2026.

| Pemeriksaan | Perintah | Hasil |
|---|---|---|
| Automated test | `php artisan test` | Lulus: 51 test, 371 assertion |
| Production build | `npm run build` | Berhasil tanpa error |
| Pemeriksaan route | `php artisan route:list` | Route aplikasi terdaftar dan dilindungi middleware |
| Dokumen Word skripsi | Pemeriksaan struktur dan render | Valid, 50 halaman |

Automated test mencakup autentikasi, akun nonaktif, registrasi publik, authorization, aktivasi periode, pengajaran duplikat, bentrok jadwal, batas nilai, nilai kosong, siswa di luar kelas, akses nilai siswa, endpoint PDF, aksi status tanpa penghapusan, tabel reusable, selection lintas halaman, URL state, pagination aman, urutan jam, pilihan bertingkat, anggota kelas, dan penempatan siswa. Pemeriksaan penerimaan pengguna tetap dilakukan bersama client pada demonstrasi akhir.

## 7. Materi Demonstrasi Akhir

1. Login menggunakan empat role.
2. Dashboard dan navigasi sesuai role.
3. Pengelolaan master data dan periode akademik.
4. Penempatan siswa, halaman anggota kelas, dan pengajaran bertingkat.
5. Penyusunan jadwal serta simulasi penolakan bentrok.
6. Input nilai massal M1-M4 dan perhitungan rata-rata.
7. Akses jadwal/nilai dari akun Guru dan Siswa.
8. Monitoring Kepala Sekolah.
9. Filter laporan, print, dan download PDF.
10. Pengaturan sekolah, profil, tabel mobile, dan dokumentasi terbaru.

## 8. Dokumen dan Berkas Serah Terima

- Source code aplikasi dan konfigurasi contoh `.env.example`.
- Migration, seeder, factory, dan data demonstrasi.
- Dokumentasi pada direktori `docs/`.
- Buku panduan penggunaan dan screenshot halaman utama.
- Dokumen black box testing dalam Markdown dan PDF.
- Presentasi aplikasi dalam PowerPoint dan PDF.
- Naskah presentasi dan urutan demonstrasi.
- Draf skripsi lengkap dalam Word serta pratinjau PDF.
- Panduan deployment Linux/Nginx dan konfigurasi production.

## 9. Hal yang Diperiksa Bersama Client

- Kesesuaian nama, logo, dan identitas sekolah.
- Kesesuaian data demonstrasi dengan kebutuhan presentasi.
- Alur penggunaan tiap role.
- Tampilan laporan dan kebutuhan cetak.
- Deployment pada domain/hosting apabila akses telah tersedia.
- Daftar revisi minor dalam batas ruang lingkup proposal.

## 10. Catatan Penerimaan

Secara teknis aplikasi telah siap untuk demonstrasi akhir. Status diterima/serah terima ditetapkan setelah client menyelesaikan pemeriksaan. Perubahan fitur besar di luar ruang lingkup awal dibicarakan sebagai pekerjaan tambahan sesuai ketentuan proposal.

| Pihak | Nama | Tanggal | Tanda Tangan |
|---|---|---|---|
| Client/Penerima |  |  |  |
| Pengembang |  |  |  |

### Catatan/Revisi Minor

..............................................................................................................................

..............................................................................................................................
