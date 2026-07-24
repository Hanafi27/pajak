# Sistem PBB BAPENDA Kabupaten Bandung

Aplikasi web pengelolaan **Pajak Bumi dan Bangunan (PBB)** berbasis Laravel untuk kebutuhan operasional Bapenda.

## Deskripsi Aplikasi

Sistem ini membantu petugas dalam:

- Mengelola data **Wajib Pajak**
- Mengelola data **Objek Pajak**
- Mengolah data **PBB** (NJOP, tarif, total pajak)
- Melihat dan mengekspor **Laporan PBB** (PDF preview dan Excel `.xlsx`)
- Memantau dashboard statistik dan grafik berbasis data aktual
- Menyusun insight dashboard, narasi laporan, dan draft surat tagihan dengan integrasi AI Gemini

Role yang tersedia:

- `petugas`
- `pimpinan`

## Fitur Utama

- Login session-based tanpa Laravel Auth bawaan
- Fitur **Lupa Kata Sandi** untuk akun demo
- CRUD Wajib Pajak, Objek Pajak, dan PBB
- Validasi input (contoh: KTP hanya angka maks 16 digit, NOP 18 digit)
- Pencarian live (AJAX) dan pagination
- Dashboard statistik, grafik, dan AI Insight
- AI Narasi Laporan yang mengikuti filter data laporan
- AI Draft Surat Tagihan PBB dengan preview dokumen bergaya PDF dan teks yang bisa disalin
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
- Google Gemini API

## Cara Clone & Menjalankan Proyek

```bash
git clone https://github.com/Hanafi27/pajak.git bapenda
cd bapenda
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Konfigurasi Environment

Edit `.env` untuk koneksi database MySQL dan Gemini API:

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

GEMINI_API_KEY=isi_api_key_gemini_anda
GEMINI_MODEL=gemini-3.5-flash-lite
```

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

## Setup Integrasi AI Gemini

Fitur AI memakai Google Gemini API untuk membuat insight dashboard, narasi laporan, dan draft surat tagihan PBB.

1. Buka Google AI Studio dan buat API key Gemini.
2. Masukkan API key ke file `.env` pada variabel `GEMINI_API_KEY`.
3. Gunakan model aktif, misalnya `gemini-3.5-flash-lite`.
4. Jangan commit file `.env` karena berisi API key dan konfigurasi lokal.

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
npm run dev
```

Buka aplikasi di:

- `http://127.0.0.1:8000`

Untuk build production asset:

```bash
npm run build
```

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
- AI memakai variabel `GEMINI_API_KEY` dan `GEMINI_MODEL` dari `.env`.
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

4. **AI gagal membuat insight atau draft surat**
   Pastikan `GEMINI_API_KEY` sudah benar, `GEMINI_MODEL` memakai model aktif, lalu jalankan `php artisan config:clear`.

## Lisensi

Proyek ini dikembangkan untuk kebutuhan akademik/implementasi sistem Bapenda dan dapat disesuaikan kembali sesuai kebutuhan instansi.
