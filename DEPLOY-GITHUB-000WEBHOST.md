# Online-kan Situs Ini Gratis Lewat GitHub (Tanpa cPanel)

## Arsitektur yang dipakai

GitHub **tidak bisa** menjalankan PHP maupun database — GitHub Pages hanya untuk
file statis. Jadi susunannya begini:

- **GitHub** = tempat menyimpan kode + memicu deploy otomatis (lewat GitHub Actions)
- **000webhost** = tempat PHP + MySQL-nya *benar-benar berjalan*, gratis
- Setiap kali Anda `git push` ke branch `main`, GitHub Actions otomatis mengirim
  file ke 000webhost lewat FTP

Panel kontrol 000webhost adalah dashboard mereka sendiri (bukan cPanel, tapi
fungsinya serupa: ada File Manager, Database, FTP). Nama menu bisa sedikit
berbeda tergantung versi panel terbaru mereka — kalau nama persisnya beda dari
yang ditulis di sini, cari menu dengan fungsi yang sama.

## Batasan jujur hosting gratis ini (baca dulu sebelum lanjut)

000webhost versi gratis membatasi: sekitar 300MB penyimpanan, 3GB bandwidth/bulan,
1 database MySQL, tidak ada SSH, dan situs bisa ditangguhkan sementara kalau lama
tidak dikunjungi/traffic dianggap tidak wajar. Untuk situs organisasi dengan
trafik wajar biasanya cukup — tapi bukan pengganti hosting berbayar untuk
kebutuhan serius/bisnis. Rutin **backup database & folder uploads** (caranya
ada di `README.md`).

---

## Langkah 1 — Buat akun 000webhost & website

1. Daftar di **000webhost.com** (gratis, tanpa kartu kredit).
2. Klik **Create New Website**, pilih:
   - Subdomain gratis dari mereka (misal `bplhmibekasi.000webhostapp.com`), **atau**
   - Domain sendiri kalau sudah punya (arahkan nameserver sesuai instruksi mereka).
3. Tunggu beberapa menit sampai website berstatus **aktif**.

## Langkah 2 — Buat database MySQL

1. Masuk ke **dashboard** website Anda di 000webhost.
2. Cari menu **Database** (kadang disebut "MySQL Databases" atau "phpMyAdmin"),
   buat database baru — catat **nama database**, **username**, dan **password**
   yang muncul.
3. Buka **phpMyAdmin** dari menu yang sama, pilih database tadi, klik tab
   **Import**, unggah `database/schema.sql` dari proyek ini, klik **Go**.

## Langkah 3 — Siapkan repositori GitHub

Kalau repo GitHub-nya sudah ada dan sudah berisi proyek ini (seperti sekarang),
langkah ini sudah selesai — langsung ke Langkah 4. Kalau mulai dari nol:

1. Ekstrak seluruh isi paket proyek ini (jangan sertakan folder pembungkus
   `bpl-backend/` — file `index.html`, folder `api/`, dll. harus langsung ada
   di **akar repo**).
2. Buat repository baru di GitHub. **Boleh publik atau privat** — aman, karena
   `config.php` asli (berisi kredensial) sudah otomatis dikecualikan lewat
   `.gitignore` dan tidak akan pernah ikut ter-commit.
3. Upload semua file ke repo tsb.

## Langkah 4 — Ambil kredensial FTP 000webhost

Di dashboard 000webhost, buka menu **FTP** (kadang disebut "FTP Accounts" atau
"Connection Info"), catat:
- **FTP Server / Hostname** (biasanya berformat `files.000webhost.com` —
  tapi cek juga info koneksi di akun Anda untuk memastikan)
- **Username** dan **Password** FTP
- **Folder tujuan**, biasanya `/public_html/` — cek juga persis namanya di
  File Manager 000webhost

## Langkah 5 — Simpan kredensial itu sebagai GitHub Secrets

Di repo GitHub Anda: **Settings → Secrets and variables → Actions → New repository secret**.
Buat 4 secret ini satu per satu:

| Nama secret | Isi |
|---|---|
| `FTP_SERVER` | contoh: `files.000webhost.com` |
| `FTP_USERNAME` | username FTP dari Langkah 4 |
| `FTP_PASSWORD` | password FTP dari Langkah 4 |
| `FTP_SERVER_DIR` | contoh: `/public_html/` |

Workflow otomatis (`.github/workflows/deploy.yml`, sudah disertakan di proyek ini)
akan memakai keempat secret ini setiap kali Anda push ke branch `main`.

**Kalau sebelumnya secret ini sudah pernah dibuat untuk InfinityFree**, edit
(bukan buat baru) keempat secret di atas dan ganti isinya dengan kredensial
000webhost — nama secret-nya tetap sama, workflow tidak perlu diubah.

## Langkah 6 — Push dan tunggu deploy otomatis

```
git add .
git commit -m "Pindah hosting ke 000webhost"
git push
```

Buka tab **Actions** di repo GitHub Anda — akan muncul proses "Deploy ke 000webhost"
berjalan (sekitar 1-2 menit). Kalau centang hijau ✅, file sudah masuk ke 000webhost.

## Langkah 7 — Isi config.php langsung di server (sekali saja, manual)

File `api/config.php` **sengaja tidak ikut ter-deploy otomatis** (demi keamanan —
lihat penjelasan `.gitignore` di atas). Isi sekali secara manual:

1. Buka **File Manager** 000webhost, masuk ke folder `public_html/api/`.
2. Upload `api/config.sample.php` dari proyek ini, lalu **rename** jadi `config.php` di sana.
3. Edit file itu langsung di File Manager, isi 4 baris kredensial database dari Langkah 2.
4. Simpan.

## Langkah 8 — Buka situsnya

Akses domain/subdomain 000webhost Anda di browser. Login pengurus default masih
sama: **admin / Admin123** — segera ganti (caranya ada di `README.md`, bagian
"Mengganti password admin").

---

## Alur kerja setelah ini

Setiap kali ingin mengubah kode (bukan konten — konten diedit lewat situsnya
sendiri di mode Login Pengurus), cukup edit file lokal, lalu:
```
git add .
git commit -m "Perubahan apa"
git push
```
GitHub Actions otomatis mengirim perubahan itu ke 000webhost dalam 1-2 menit.
`uploads/` dan `api/config.php` di server **tidak akan pernah tertimpa** oleh proses ini.
