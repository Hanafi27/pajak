# Sistem PBB BAPENDA Kabupaten Bandung

Aplikasi web pengelolaan **Pajak Bumi dan Bangunan (PBB)** berbasis Laravel untuk kebutuhan operasional Bapenda.

## Deskripsi Aplikasi

Sistem ini membantu petugas dalam:

- Mengelola data **Wajib Pajak**
- Mengelola data **Objek Pajak**
- Mengolah data **PBB** (NJOP, tarif, total pajak)
- Melihat dan mengekspor **Laporan PBB** (PDF preview dan Excel `.xlsx`)
- Memantau dashboard statistik dan grafik berbasis data aktual

Role yang tersedia:

- `petugas`
- `pimpinan`

## Fitur Utama

- Login session-based tanpa Laravel Auth bawaan
- Fitur **Lupa Kata Sandi** untuk akun demo
- CRUD Wajib Pajak, Objek Pajak, dan PBB
- Validasi input (contoh: KTP hanya angka maks 16 digit, NOP 18 digit)
- Pencarian live (AJAX) dan pagination
- Export laporan:
  - PDF (preview di browser)
  - Excel (`.xlsx`)

## Teknologi

- PHP 8.2+
- Laravel 12
- MySQL
- Vite + Tailwind CSS
- Chart.js
- DomPDF
- PhpSpreadsheet

## Cara Clone & Menjalankan Proyek

```bash
git clone <url-repo-anda> bapenda
cd bapenda
composer install
cp .env.example .env
php artisan key:generate
```

## Konfigurasi Environment

Edit `.env` untuk koneksi database MySQL:

```env
APP_NAME="Sistem PBB BAPENDA"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bapenda
DB_USERNAME=root
DB_PASSWORD=
```

## Inisialisasi Database

```bash
php artisan migrate
```

Jika perlu refresh total:

```bash
php artisan migrate:fresh
```

## Menjalankan Aplikasi

Jalankan backend:

```bash
php artisan serve
```

Jalankan asset frontend:

```bash
npm install
npm run dev
```

Buka aplikasi di:

- `http://127.0.0.1:8000`

## Akun Demo

Default akun login:

- `petugas / password123`
- `pimpinan / password123`

Catatan:

- Password akun demo bisa diubah lewat fitur **Lupa Kata Sandi**
- Data akun demo tersimpan di: `storage/app/demo-users.json`

## Struktur Menu

- Dashboard
- Data Wajib Pajak
- Data Objek Pajak
- Pengolahan PBB
- Laporan

## Catatan Pengembangan

- Beberapa visual menggunakan modal dan toast custom.
- Export PDF membutuhkan ekstensi PHP:
  - `gd`
  - `mbstring`
- Export Excel membutuhkan ekstensi:
  - `zip`
  - `xml`

## Troubleshooting Singkat

1. **`SQLSTATE... no such table` / tabel tidak ditemukan**  
   Jalankan: `php artisan migrate`

2. **Asset CSS/JS tidak ter-load**  
   Pastikan: `npm run dev` aktif

3. **File PDF/Excel gagal generate**  
   Cek ekstensi PHP (`gd`, `zip`, `xml`) aktif di `php.ini`

## Lisensi

Proyek ini dikembangkan untuk kebutuhan akademik/implementasi sistem Bapenda dan dapat disesuaikan kembali sesuai kebutuhan instansi.
