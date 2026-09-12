# Online-kan Situs Ini Gratis Lewat GitHub (ProFreeHost + bplkabbekasi.unaux.com)

## Arsitektur yang dipakai

GitHub **tidak bisa** menjalankan PHP maupun database — GitHub Pages hanya untuk
file statis. Jadi susunannya begini:

- **GitHub** = tempat menyimpan kode + memicu deploy otomatis (lewat GitHub Actions)
- **ProFreeHost** = tempat PHP + MySQL-nya *benar-benar berjalan*, dengan domain
  **bplkabbekasi.unaux.com**
- Setiap kali Anda `git push` ke branch `main`, GitHub Actions otomatis mengirim
  file ke ProFreeHost lewat FTP

Panel kontrol ProFreeHost namanya **VistaPanel** (sama seperti InfinityFree/ByetHost
— satu jaringan hosting gratis yang sama) — bukan cPanel, tapi fungsinya serupa
(bikin database, File Manager, FTP Accounts, dsb).

## Batasan jujur hosting gratis ini (baca dulu sebelum lanjut)

Hosting gratis jenis ini biasanya membatasi: sekitar 30–50 ribu kunjungan/hari,
database MySQL maksimal 50MB per database, tidak ada SSH, dan situs yang lama
tidak dikunjungi berisiko ditangguhkan. Untuk situs organisasi dengan trafik
wajar biasanya cukup — tapi bukan pengganti hosting berbayar untuk kebutuhan
serius/bisnis. Rutin **backup database & folder uploads** (caranya ada di
`README.md`).

---

## Langkah 1 — Buat database MySQL

1. Masuk ke **Control Panel** (VistaPanel) dari akun ProFreeHost Anda.
2. Cari menu **MySQL Databases**, buat database baru — catat **nama database**,
   **username**, dan **password** yang muncul (formatnya biasanya diawali kode
   akun Anda, contoh `epiz_xxxxxxxx_bplhmi`).
3. Buka **phpMyAdmin** dari Control Panel yang sama, pilih database tadi, klik
   tab **Import**, unggah `database/schema.sql` dari proyek ini, klik **Go**.

## Langkah 2 — Ambil kredensial FTP

Di Control Panel ProFreeHost, buka menu **FTP Accounts**, catat:
- **FTP Server** — cek di halaman "FTP Details" akun Anda (formatnya bisa
  berbeda-beda tiap akun).
- **Username** dan **Password** FTP.
- **Folder tujuan**, formatnya `/bplkabbekasi.unaux.com/htdocs/` — lihat persis
  namanya di File Manager ProfreeHost bagian domain Anda.

## Langkah 3 — Simpan kredensial itu sebagai GitHub Secrets

Di repo GitHub Anda: **Settings → Secrets and variables → Actions**. Kalau
4 secret ini sudah pernah dibuat untuk hosting sebelumnya, **edit isinya saja**
(nama secret tidak perlu diubah):

| Nama secret | Isi |
|---|---|
| `FTP_SERVER` | dari Langkah 2 |
| `FTP_USERNAME` | dari Langkah 2 |
| `FTP_PASSWORD` | dari Langkah 2 |
| `FTP_SERVER_DIR` | contoh: `/bplkabbekasi.unaux.com/htdocs/` |

Workflow otomatis (`.github/workflows/deploy.yml`, sudah disertakan di proyek ini)
akan memakai keempat secret ini setiap kali Anda push ke branch `main`.

## Langkah 4 — Push dan tunggu deploy otomatis

```
git add .
git commit -m "Pindah hosting ke ProFreeHost"
git push
```

Buka tab **Actions** di repo GitHub Anda — akan muncul proses "Deploy ke ProFreeHost"
berjalan (sekitar 1-2 menit). Kalau centang hijau ✅, file sudah masuk ke server.

## Langkah 5 — Isi config.php langsung di server (sekali saja, manual)

File `api/config.php` **sengaja tidak ikut ter-deploy otomatis** (demi keamanan —
lihat `.gitignore`). Isi sekali secara manual:

1. Buka **File Manager** ProFreeHost, masuk ke folder `htdocs/api/`.
2. Upload `api/config.sample.php` dari proyek ini, lalu **rename** jadi `config.php` di sana.
3. Edit file itu langsung di File Manager, isi 4 baris kredensial database dari Langkah 1.
4. Simpan.
5. Pastikan folder `htdocs/uploads/` ada dan permission-nya **755** (atau 775
   kalau 755 masih gagal saat upload foto nanti).

## Langkah 6 — Buka situsnya

Akses **https://bplkabbekasi.unaux.com** di browser. Login pengurus default masih
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
GitHub Actions otomatis mengirim perubahan itu ke ProFreeHost dalam 1-2 menit.
`uploads/` dan `api/config.php` di server **tidak akan pernah tertimpa** oleh proses ini.
