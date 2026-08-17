# SISD 232

Sistem Informasi Jadwal Pelajaran dan Nilai Siswa berbasis web untuk SD Negeri 232 Maluku Tengah. Aplikasi menyediakan pengelolaan master akademik, jadwal bebas bentrok, nilai tugas Minggu 1–4, monitoring, laporan print/PDF, serta akses terpisah bagi Admin, Guru, Siswa, dan Kepala Sekolah.

## Stack

- PHP 8.3+ (dikembangkan pada PHP 8.4)
- Laravel 13, Livewire 4 single-file components, Blade
- Tailwind CSS 4, Vite 8
- MySQL 8+/MariaDB yang kompatibel
- Spatie Laravel Permission 8
- DomPDF 3
- PHPUnit 12

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database MySQL, lalu isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada `.env`.

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Untuk development frontend gunakan `npm run dev`. Public registration tidak tersedia; akun dibuat oleh Admin atau seeder.

## Akun Demo

Semua akun memakai password: `Sekolah232!`

| Role | Username | Email |
|---|---|---|
| Admin | `admin` | `admin@sisd232.test` |
| Guru | `guru1` | `guru1@sisd232.test` |
| Siswa | `siswa` | `siswa@sisd232.test` |
| Kepala Sekolah | `kepala` | `kepala@sisd232.test` |

Seeder juga membuat akun `guru2` hingga `guru8`.

## Pengujian dan Build

```bash
php artisan migrate:fresh --seed --env=testing
php artisan test
./vendor/bin/pint --test
npm run build
php artisan route:list
```

Test memakai SQLite in-memory agar cepat; development dan production tetap menggunakan MySQL.

## Production

Setidaknya atur:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-sekolah.example
APP_TIMEZONE=Asia/Jayapura
APP_LOCALE=id
SESSION_SECURE_COOKIE=true
```

Kemudian jalankan `php artisan optimize`, arahkan document root web server ke `public/`, aktifkan HTTPS, dan pastikan `storage/` serta `bootstrap/cache/` writable. Aplikasi saat ini tidak memerlukan queue worker atau scheduler khusus. Panduan lengkap tersedia di `docs/deployment.md`.

## Dokumentasi

- `docs/skripsi-lengkap-awati-fujihani-lessy.docx` — draf skripsi lengkap yang dapat diedit di Microsoft Word
- `docs/skripsi-preview-awati-fujihani-lessy.pdf` — pratinjau tata letak skripsi untuk pemeriksaan dan pencetakan
- `docs/presentasi-aplikasi-sisd232.pptx` — slide PowerPoint presentasi aplikasi
- `docs/presentasi-aplikasi-sisd232.pdf` — versi PDF presentasi
- `docs/naskah-presentasi.md` — naskah pembicara dan urutan demonstrasi
- `docs/black-box-testing.pdf` — dokumen skenario black box testing siap cetak
- `docs/buku-panduan-penggunaan.pdf` — buku panduan siap dibagikan/cetak
- `docs/buku-panduan-penggunaan.html` — buku panduan untuk dibuka di browser
- `docs/buku-panduan-penggunaan.md` — sumber buku panduan
- `docs/screenshots/` — screenshot seluruh halaman utama dan tampilan mobile
- `docs/requirement.md`
- `docs/erd.dbml`
- `docs/database.md`
- `docs/roles-permissions.md`
- `docs/system-flow.md`
- `docs/black-box-testing.md`
- `docs/deployment.md`
