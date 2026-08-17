# Buku Panduan Penggunaan Aplikasi

## Sistem Informasi Jadwal Pelajaran dan Nilai Siswa

**SD Negeri 232 Maluku Tengah**  
Versi panduan: 1.0  
Bahasa: Indonesia

---

## Tentang panduan ini

Panduan ini ditujukan untuk Admin, Guru, Siswa, Kepala Sekolah, dan pengguna yang belum terbiasa menggunakan aplikasi komputer. Setiap bagian menggunakan langkah singkat, istilah sederhana, dan gambar halaman yang sebenarnya.

> **Catatan penting:** tampilan nama siswa, guru, dan nilai pada gambar berasal dari data contoh. Data sekolah yang sebenarnya akan mengikuti data yang dimasukkan oleh Admin.

## Daftar isi

1. Mengenal jenis pengguna
2. Masuk dan keluar aplikasi
3. Mengenal tampilan aplikasi
4. Panduan Admin
5. Panduan Guru
6. Panduan Siswa
7. Panduan Kepala Sekolah
8. Menggunakan aplikasi di telepon genggam
9. Mencetak dan mengunduh PDF
10. Pesan kesalahan dan cara mengatasinya
11. Kebiasaan penggunaan yang aman
12. Tanya jawab singkat

---

# 1. Mengenal jenis pengguna

Setiap akun hanya melihat menu yang sesuai dengan tugasnya.

| Pengguna | Kegunaan utama |
|---|---|
| **Admin** | Mengatur pengguna, guru, siswa, kelas, mata pelajaran, periode akademik, jadwal, nilai, laporan, dan identitas sekolah. |
| **Guru** | Melihat jadwal mengajar, mengisi nilai Minggu 1–4, melihat riwayat, dan mencetak laporan nilai kelas yang diajar. |
| **Siswa** | Melihat jadwal kelas dan nilai milik sendiri. |
| **Kepala Sekolah** | Memantau data, jadwal, nilai, statistik, dan laporan tanpa mengubah master data. |

Jika suatu menu tidak terlihat, kemungkinan akun memang tidak memiliki izin untuk menu tersebut. Hubungi Admin jika peran akun tidak sesuai.

---

# 2. Masuk dan keluar aplikasi

## 2.1 Masuk

![Halaman masuk](screenshots/00-login.png)

1. Buka alamat aplikasi yang diberikan sekolah.
2. Isi **Username atau Email**.
3. Isi **Password**.
4. Tekan tombol **Masuk**.
5. Setelah berhasil, aplikasi membuka Dashboard sesuai jenis akun.

Jika gagal masuk:

- periksa huruf besar dan kecil pada password;
- pastikan tidak ada spasi tambahan;
- pastikan akun masih aktif;
- hubungi Admin untuk mengatur ulang password.

## 2.2 Keluar

1. Lihat bagian bawah menu sebelah kiri.
2. Tekan **Keluar**.
3. Pastikan halaman kembali ke halaman masuk.

Selalu keluar jika menggunakan komputer bersama.

---

# 3. Mengenal tampilan aplikasi

Bagian utama aplikasi terdiri dari:

- **Menu samping:** berpindah ke halaman lain.
- **Bagian isi:** menampilkan dashboard, tabel, formulir, atau laporan.
- **Pencarian:** mencari data berdasarkan nama atau nomor identitas.
- **Filter:** membatasi data berdasarkan status, kelas, periode, guru, atau bulan.
- **Tombol Tambah/Ubah:** membuka formulir data.
- **Tombol Nonaktifkan:** menghentikan penggunaan data tanpa merusak riwayat.
- **Pagination:** berpindah halaman jika data sangat banyak.

## Arti status

- **Aktif:** data atau akun masih digunakan.
- **Nonaktif:** data disimpan sebagai riwayat, tetapi tidak digunakan untuk kegiatan baru.

## Arti nilai kosong

Kolom nilai kosong berarti **belum dinilai**. Nilai kosong tidak sama dengan nilai nol.

---

# 4. Panduan Admin

## 4.1 Dashboard Admin

![Dashboard Admin](screenshots/admin-dashboard.png)

Dashboard menampilkan jumlah guru, siswa, kelas, mata pelajaran, jadwal, nilai yang sudah diisi, periode aktif, dan jadwal hari ini. Gunakan tombol **Input Nilai** sebagai jalan cepat ke halaman nilai.

## 4.2 Mengelola Tahun Ajaran

![Data tahun ajaran](screenshots/admin-tahun-ajaran.png)

1. Buka **Master Data → Tahun Ajaran**.
2. Tekan **Tambah Tahun Ajaran**.
3. Isi nama, misalnya `2026/2027`.
4. Isi tanggal mulai dan selesai.
5. Pilih status, kemudian tekan **Simpan**.

Saat tahun ajaran baru diaktifkan, tahun ajaran aktif sebelumnya otomatis dinonaktifkan. Data lama tetap dapat dibaca.

## 4.3 Mengelola Semester

![Data semester](screenshots/admin-semester.png)

1. Buka **Master Data → Semester**.
2. Pilih tahun ajaran.
3. Pilih **Ganjil** atau **Genap**.
4. Isi tanggal mulai dan selesai.
5. Simpan data.

Semester hanya dapat diaktifkan jika tahun ajarannya aktif.

## 4.4 Mengelola Kelas

![Data kelas](screenshots/admin-kelas.png)

1. Buka **Master Data → Kelas**.
2. Tekan **Tambah Kelas**.
3. Pilih tahun ajaran.
4. Isi nama kelas, misalnya `VI A`.
5. Isi tingkat dan pilih wali kelas jika sudah diketahui.
6. Tekan **Simpan**.

Nama kelas tidak boleh sama dalam tahun ajaran yang sama.

## 4.5 Mengelola Mata Pelajaran

![Data mata pelajaran](screenshots/admin-mata-pelajaran.png)

1. Buka **Master Data → Mata Pelajaran**.
2. Isi kode jika sekolah menggunakannya.
3. Isi nama mata pelajaran.
4. Pilih status dan simpan.

Gunakan kotak pencarian untuk menemukan mata pelajaran berdasarkan kode atau nama.

## 4.6 Mengelola Jam Pelajaran

![Data jam pelajaran](screenshots/admin-jam-pelajaran.png)

1. Buka **Master Data → Jam Pelajaran**.
2. Isi nama, jam mulai, jam selesai, dan urutan.
3. Pilih jenis **Pelajaran** atau **Istirahat**.
4. Simpan.

Jam selesai harus lebih besar daripada jam mulai.

## 4.7 Mengelola Guru

![Daftar guru](screenshots/admin-guru.png)

Gunakan pencarian untuk mencari nama, NIP, atau NUPTK. Gunakan filter status untuk menampilkan guru aktif atau nonaktif.

![Form tambah guru](screenshots/admin-form-tambah-guru.png)

1. Tekan **Tambah Guru**.
2. Pilih akun pengguna jika akun guru sudah dibuat. Bagian ini boleh dikosongkan sementara.
3. Isi nama lengkap.
4. Isi NIP/NUPTK jika tersedia.
5. Lengkapi jenis kelamin, tempat lahir, tanggal lahir, telepon, dan alamat.
6. Tekan **Simpan**.

NIP dan NUPTK tidak wajib, tetapi tidak boleh digunakan oleh dua guru.

## 4.8 Mengelola Siswa

![Daftar siswa](screenshots/admin-siswa.png)

1. Buka **Master Data → Siswa**.
2. Tekan **Tambah Siswa**.
3. Hubungkan akun jika siswa membutuhkan akses login.
4. Isi nama lengkap, NIS/NISN jika tersedia, dan biodata.
5. Simpan.

Siswa yang sudah memiliki nilai sebaiknya dinonaktifkan, bukan dihapus, agar riwayat tetap tersedia.

## 4.9 Menempatkan Siswa ke Kelas

![Penempatan siswa](screenshots/admin-penempatan-siswa.png)

1. Buka **Master Data → Penempatan Siswa**.
2. Pilih tahun ajaran.
3. Pilih kelas tujuan.
4. Cari siswa bila diperlukan.
5. Centang satu atau beberapa siswa.
6. Tekan **Tempatkan Siswa**.

Satu siswa tidak dapat memiliki dua kelas aktif dalam tahun ajaran yang sama. Ketika siswa naik kelas, buat penempatan pada tahun ajaran baru agar riwayat kelas lama tetap tersimpan.

## 4.10 Mengatur Pengajaran

![Data pengajaran](screenshots/admin-pengajaran.png)

Pengajaran adalah hubungan antara semester, kelas, mata pelajaran, dan guru.

1. Buka **Akademik → Pengajaran**.
2. Tekan **Tambah Pengajaran**.
3. Pilih semester, kelas, mata pelajaran, dan guru.
4. Simpan.

Pengajaran yang sama tidak dapat dibuat dua kali.

## 4.11 Mengatur Jadwal Pelajaran

![Jadwal pelajaran Admin](screenshots/admin-jadwal-pelajaran.png)

1. Buka **Akademik → Jadwal Pelajaran**.
2. Pilih pengajaran yang sudah dibuat.
3. Pilih hari dan jam pelajaran.
4. Simpan.

Aplikasi otomatis menolak:

- dua pelajaran pada kelas yang sama di waktu yang sama;
- satu guru mengajar dua kelas pada waktu yang sama;
- jadwal yang benar-benar sama.

Jika muncul pesan **Jadwal bentrok**, ubah hari, jam, kelas, atau guru.

## 4.12 Memantau Nilai

![Monitoring nilai Admin](screenshots/admin-nilai-siswa.png)

Admin dapat memilih pengajaran dan bulan untuk melihat nilai Minggu 1–4. Gunakan halaman ini untuk memastikan pengisian nilai berjalan, bukan untuk menggantikan tanggung jawab guru.

## 4.13 Mengelola Pengguna

![Manajemen pengguna](screenshots/admin-pengguna.png)

1. Buka **Manajemen → Pengguna**.
2. Tekan **Tambah Pengguna**.
3. Isi nama, username, email bila digunakan, role, password, dan status.
4. Simpan.

Username harus unik. Saat mengubah pengguna, password boleh dikosongkan jika tidak ingin diganti. Admin dapat memasukkan password baru untuk melakukan reset password.

## 4.14 Pengaturan Sekolah

![Pengaturan sekolah](screenshots/admin-pengaturan-sekolah.png)

Isi nama sekolah, NPSN, alamat, telepon, email, kepala sekolah, dan logo. Data ini digunakan pada laporan dan PDF.

Untuk logo:

- gunakan gambar JPG, JPEG, atau PNG;
- gunakan gambar yang jelas;
- jangan memakai file yang terlalu besar;
- tekan **Simpan** setelah memilih file.

## 4.15 Laporan Jadwal

![Laporan jadwal Admin](screenshots/admin-laporan-jadwal.png)

1. Buka **Laporan → Laporan Jadwal**.
2. Pilih semester, kelas, guru, atau hari jika diperlukan.
3. Periksa preview.
4. Tekan **Cetak** atau **Unduh PDF**.

## 4.16 Laporan Nilai

![Laporan nilai Admin](screenshots/admin-laporan-nilai.png)

Pilih semester, kelas, guru, mata pelajaran, bulan, atau siswa. Filter dapat dikombinasikan untuk menghasilkan laporan yang lebih khusus.

## 4.17 Profil Admin

![Profil Admin](screenshots/admin-profil.png)

Pada halaman Profil, pengguna dapat memperbarui nama/email yang diizinkan serta mengganti password. Penggantian password meminta password saat ini untuk mencegah penyalahgunaan.

---

# 5. Panduan Guru

## 5.1 Dashboard Guru

![Dashboard Guru](screenshots/guru-dashboard.png)

Dashboard Guru menampilkan periode aktif, jadwal hari ini, jumlah kelas dan mata pelajaran yang diajar, serta jalan cepat untuk mengisi nilai.

## 5.2 Melihat Jadwal Mengajar

![Jadwal mengajar Guru](screenshots/guru-jadwal-mengajar.png)

Buka **Akademik → Jadwal Mengajar**. Guru hanya melihat jadwal miliknya. Gunakan filter hari atau semester untuk mempersempit tampilan.

## 5.3 Mengisi Nilai Minggu 1–4

![Halaman awal input nilai](screenshots/guru-input-nilai.png)

1. Buka **Akademik → Input & Riwayat Nilai**.
2. Pilih kelas, mata pelajaran, dan pengajaran.
3. Pilih bulan.
4. Daftar seluruh siswa akan muncul.

![Input nilai yang sudah terisi](screenshots/guru-input-nilai-terisi.png)

5. Isi nilai pada kolom **Minggu 1**, **Minggu 2**, **Minggu 3**, dan **Minggu 4**.
6. Gunakan tombol `Tab` pada keyboard untuk berpindah cepat ke kolom berikutnya.
7. Nilai harus berada antara 0 dan 100.
8. Kosongkan kolom jika siswa belum dinilai. Jangan mengisi nol untuk menandai nilai yang belum ada.
9. Periksa rata-rata yang dihitung otomatis.
10. Tekan **Simpan Nilai** satu kali dan tunggu pesan berhasil.

Guru hanya dapat mengubah nilai pada kelas dan mata pelajaran yang menjadi tanggung jawabnya.

## 5.4 Laporan Nilai Guru

![Laporan nilai Guru](screenshots/guru-laporan-nilai.png)

Guru dapat menyaring laporan berdasarkan semester, kelas, mata pelajaran, bulan, dan siswa dalam pengajaran miliknya. Gunakan **Cetak** atau **Unduh PDF** untuk menyimpan laporan.

## 5.5 Profil Guru

![Profil Guru](screenshots/guru-profil.png)

Gunakan halaman Profil untuk memperbarui informasi akun dan mengganti password sendiri.

---

# 6. Panduan Siswa

## 6.1 Dashboard Siswa

![Dashboard Siswa](screenshots/siswa-dashboard.png)

Dashboard menampilkan nama siswa, kelas aktif, periode akademik, jadwal hari ini, dan ringkasan nilai.

## 6.2 Melihat Jadwal Pelajaran

![Jadwal pelajaran Siswa](screenshots/siswa-jadwal-pelajaran.png)

Buka **Akademik → Jadwal Pelajaran**. Jadwal yang muncul berasal dari kelas aktif siswa. Siswa tidak dapat mengubah jadwal.

## 6.3 Melihat Nilai Sendiri

![Nilai Saya](screenshots/siswa-nilai-saya.png)

1. Buka **Akademik → Nilai Saya**.
2. Pilih bulan.
3. Lihat nilai M1 sampai M4 dan rata-rata setiap mata pelajaran.

Tanda `-` berarti nilai belum tersedia. Siswa hanya dapat melihat nilainya sendiri.

## 6.4 Mengunduh Laporan Nilai

![Laporan nilai Siswa](screenshots/siswa-laporan-nilai.png)

Pilih periode atau bulan, lalu gunakan **Cetak** atau **Unduh PDF**. Sistem otomatis membatasi laporan pada siswa yang sedang masuk.

## 6.5 Profil Siswa

![Profil Siswa](screenshots/siswa-profil.png)

Siswa dapat memperbarui bagian profil yang diizinkan dan mengganti password dengan memasukkan password saat ini.

---

# 7. Panduan Kepala Sekolah

## 7.1 Dashboard Monitoring

![Dashboard Kepala Sekolah](screenshots/kepala-dashboard.png)

Dashboard memberikan ringkasan jumlah guru, siswa, kelas, mata pelajaran, jadwal, dan pengisian nilai.

## 7.2 Monitoring Jadwal

![Monitoring jadwal](screenshots/kepala-monitoring-jadwal.png)

Gunakan filter semester, kelas, guru, hari, dan mata pelajaran untuk memeriksa pelaksanaan jadwal tanpa mengubah data.

## 7.3 Monitoring Nilai

![Monitoring nilai](screenshots/kepala-monitoring-nilai.png)

Pilih periode, kelas, mata pelajaran, guru, bulan, atau siswa untuk memantau kelengkapan nilai Minggu 1–4.

## 7.4 Laporan Jadwal

![Laporan jadwal Kepala Sekolah](screenshots/kepala-laporan-jadwal.png)

Atur filter sesuai kebutuhan, periksa preview, kemudian cetak atau unduh PDF.

## 7.5 Laporan Nilai

![Laporan nilai Kepala Sekolah](screenshots/kepala-laporan-nilai.png)

Laporan dapat digunakan untuk melihat nilai per kelas, siswa, guru, atau mata pelajaran. Kepala Sekolah memiliki akses baca dan tidak mengubah nilai.

## 7.6 Profil Kepala Sekolah

![Profil Kepala Sekolah](screenshots/kepala-profil.png)

Gunakan halaman Profil untuk memperbarui akun dan mengganti password.

---

# 8. Menggunakan aplikasi di telepon genggam

## 8.1 Membuka menu

Tekan tombol bergambar tiga garis di kiri atas. Menu akan muncul dari sisi kiri.

![Menu Admin di telepon genggam](screenshots/mobile-admin-menu.png)

Tekan tanda `×` atau area gelap di luar menu untuk menutupnya.

## 8.2 Tampilan Guru

![Dashboard Guru mobile](screenshots/mobile-guru-dashboard.png)

![Jadwal Guru mobile](screenshots/mobile-guru-jadwal-mengajar.png)

Pada tabel yang lebar, geser tabel ke kiri atau kanan dengan jari.

![Input nilai Guru mobile](screenshots/mobile-guru-input-nilai-terisi.png)

Nama siswa tetap berada di sisi kiri. Geser tabel ke samping untuk membuka Minggu 2, Minggu 3, Minggu 4, dan rata-rata.

## 8.3 Tampilan Siswa

![Dashboard Siswa mobile](screenshots/mobile-siswa-dashboard.png)

![Jadwal Siswa mobile](screenshots/mobile-siswa-jadwal-pelajaran.png)

![Nilai Siswa mobile](screenshots/mobile-siswa-nilai-saya.png)

Gunakan telepon dalam posisi mendatar jika ingin melihat lebih banyak kolom sekaligus.

---

# 9. Mencetak dan mengunduh PDF

## Mencetak

1. Buka halaman laporan.
2. Atur filter sampai data yang diperlukan tampil.
3. Tekan **Cetak**.
4. Pilih printer dan ukuran kertas A4.
5. Periksa preview sebelum mencetak.

Menu, tombol, filter, dan pagination otomatis disembunyikan pada hasil cetak.

## Mengunduh PDF

1. Buka halaman laporan.
2. Atur filter.
3. Tekan **Unduh PDF**.
4. File akan tersimpan pada folder unduhan perangkat.

Untuk laporan nilai yang lebar, PDF menggunakan posisi kertas mendatar agar tabel tidak terpotong.

---

# 10. Pesan kesalahan dan cara mengatasinya

| Pesan/keadaan | Arti | Tindakan |
|---|---|---|
| Data wajib diisi | Ada bagian formulir yang masih kosong. | Isi kolom yang ditandai, lalu simpan kembali. |
| Data sudah digunakan | Nomor atau kombinasi data sudah ada. | Periksa data lama atau gunakan nilai yang berbeda. |
| Jadwal bentrok | Kelas atau guru sudah memiliki jadwal pada waktu tersebut. | Pilih hari, jam, guru, atau kelas lain. |
| Akses ditolak / 403 | Akun tidak memiliki izin. | Kembali ke Dashboard atau hubungi Admin. |
| Halaman tidak ditemukan / 404 | Alamat salah atau halaman sudah tidak tersedia. | Gunakan menu aplikasi, jangan mengetik alamat secara manual. |
| Halaman kedaluwarsa / 419 | Sesi terlalu lama tidak digunakan. | Muat ulang halaman dan masuk kembali. |
| Nilai harus 0–100 | Angka berada di luar batas. | Ganti dengan angka 0 sampai 100 atau kosongkan jika belum dinilai. |
| Tombol tidak dapat ditekan | Pilihan wajib belum ditentukan atau proses sedang berjalan. | Lengkapi pilihan dan tunggu proses selesai. |

Jangan menekan tombol simpan berulang kali ketika tulisan **Menyimpan...** sedang tampil.

---

# 11. Kebiasaan penggunaan yang aman

- Jangan membagikan password kepada orang lain.
- Gunakan password yang tidak mudah ditebak.
- Keluar setelah selesai menggunakan komputer bersama.
- Jangan mengirim foto halaman nilai ke pihak yang tidak berkepentingan.
- Periksa kembali kelas, mata pelajaran, bulan, dan minggu sebelum menyimpan nilai.
- Admin sebaiknya menonaktifkan akun pengguna yang tidak lagi bertugas.
- Lakukan pencadangan database secara berkala sesuai panduan deployment.

---

# 12. Tanya jawab singkat

## Mengapa saya tidak melihat menu Admin?

Akun Anda bukan akun Admin atau belum memiliki izin yang sesuai.

## Mengapa siswa belum muncul saat guru mengisi nilai?

Pastikan pengajaran sudah dipilih dan siswa sudah ditempatkan pada kelas tersebut di tahun ajaran yang sesuai.

## Apakah nilai nol boleh diisi?

Ya. Nilai `0` adalah nilai yang sah. Kosongkan kolom jika siswa belum dinilai.

## Bagaimana memperbaiki nilai yang salah?

Guru membuka pengajaran dan bulan yang sama, memperbaiki angka, lalu menekan **Simpan Nilai**.

## Mengapa jadwal tidak dapat disimpan?

Kemungkinan terdapat bentrok kelas, bentrok guru, atau jadwal yang sama sudah tersedia.

## Bagaimana siswa naik kelas?

Admin membuat atau memilih kelas pada tahun ajaran baru, lalu menempatkan siswa melalui halaman **Penempatan Siswa**. Jangan menghapus riwayat kelas lama.

## Bagaimana mengganti password yang lupa?

Hubungi Admin. Admin dapat menetapkan password baru melalui halaman Pengguna tanpa melihat password lama.

## Siapa yang dapat melihat nilai seluruh siswa?

Admin dan Kepala Sekolah dapat melakukan monitoring. Guru hanya melihat kelas yang diajar, sedangkan siswa hanya melihat nilai sendiri.

---

## Bantuan

Jika masalah belum selesai, catat:

1. nama halaman;
2. waktu kejadian;
3. tindakan yang dilakukan;
4. pesan kesalahan yang tampil;
5. screenshot jika memungkinkan.

Sampaikan catatan tersebut kepada Admin atau pengelola teknis aplikasi.

