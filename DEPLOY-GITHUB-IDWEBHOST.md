# Online-kan Situs Ini Gratis Lewat GitHub (IDwebhost + bplkabbekasi.my.id)

## Arsitektur yang dipakai

GitHub **tidak bisa** menjalankan PHP maupun database — GitHub Pages hanya untuk
file statis. Jadi susunannya begini:

- **GitHub** = tempat menyimpan kode + memicu deploy otomatis (lewat GitHub Actions)
- **IDwebhost** = tempat PHP + MySQL-nya *benar-benar berjalan*, dengan domain
  **bplkabbekasi.my.id**
- Setiap kali Anda `git push` ke branch `main`, GitHub Actions otomatis mengirim
  file ke IDwebhost lewat FTP

IDwebhost memakai panel **cPanel** standar, jadi langkah-langkah di `README.md`
(bagian "Langkah pemasangan di cPanel") berlaku persis untuk hosting ini kalau
Anda mau upload manual. Dokumen ini fokus ke jalur otomatis lewat GitHub Actions.

## Langkah 1 — Buat database MySQL di cPanel IDwebhost

1. Login ke **cPanel** IDwebhost Anda (link & kredensial biasanya dikirim lewat
   email saat aktivasi hosting).
2. Masuk menu **MySQL® Databases**, buat database baru (misal `bplhmi`) —
   cPanel otomatis menambah prefix jadi semacam `namauser_bplhmi`.
3. Buat user database baru dengan password kuat, lalu di **Add User to Database**
   tambahkan user itu ke database tadi dengan **ALL PRIVILEGES**.
4. Catat: **nama database**, **username**, **password** — dipakai di Langkah 5.
5. Masuk **phpMyAdmin**, pilih database tadi, tab **Import**, unggah
   `database/schema.sql` dari proyek ini, klik **Go**.

## Langkah 2 — Ambil kredensial FTP

Di cPanel → menu **FTP Accounts**:
1. Buat akun FTP baru (atau pakai akun FTP utama yang sudah ada saat aktivasi).
2. Catat:
   - **FTP Server / Hostname** — biasanya `ftp.bplkabbekasi.my.id` atau nama
     server yang tertera di halaman FTP Accounts.
   - **Username** dan **Password** FTP.
   - **Folder tujuan** — biasanya `/public_html/` (folder root domain di cPanel).

## Langkah 3 — Simpan kredensial itu sebagai GitHub Secrets

Di repo GitHub Anda: **Settings → Secrets and variables → Actions → New repository secret**
(kalau sudah pernah dibuat untuk hosting lama, **edit** isinya saja, nama secret
tidak perlu diubah):

| Nama secret | Isi |
|---|---|
| `FTP_SERVER` | contoh: `ftp.bplkabbekasi.my.id` |
| `FTP_USERNAME` | username FTP dari Langkah 2 |
| `FTP_PASSWORD` | password FTP dari Langkah 2 |
| `FTP_SERVER_DIR` | contoh: `/public_html/` |

Workflow otomatis (`.github/workflows/deploy.yml`, sudah disertakan di proyek ini)
akan memakai keempat secret ini setiap kali Anda push ke branch `main`.

## Langkah 4 — Push dan tunggu deploy otomatis

```
git add .
git commit -m "Pindah hosting ke IDwebhost"
git push
```

Buka tab **Actions** di repo GitHub Anda — akan muncul proses "Deploy ke IDwebhost"
berjalan (sekitar 1-2 menit). Kalau centang hijau ✅, file sudah masuk ke server.

## Langkah 5 — Isi config.php langsung di server (sekali saja, manual)

File `api/config.php` **sengaja tidak ikut ter-deploy otomatis** (demi keamanan —
lihat `.gitignore`). Isi sekali secara manual:

1. Buka **File Manager** cPanel, masuk ke folder `public_html/api/`.
2. Upload `api/config.sample.php` dari proyek ini, lalu **rename** jadi `config.php` di sana.
3. Edit file itu langsung di File Manager, isi 4 baris kredensial database dari Langkah 1.
4. Simpan.
5. Pastikan folder `public_html/uploads/` ada dan permission-nya **755**
   (atau 775 kalau 755 masih gagal saat upload foto nanti).

## Langkah 6 — Aktifkan HTTPS

Di cPanel → **SSL/TLS Status** → aktifkan **AutoSSL** untuk `bplkabbekasi.my.id`
(biasanya gratis, sudah termasuk paket IDwebhost).

## Langkah 7 — Buka situsnya

Akses **https://bplkabbekasi.my.id** di browser. Login pengurus default masih
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
GitHub Actions otomatis mengirim perubahan itu ke IDwebhost dalam 1-2 menit.
`uploads/` dan `api/config.php` di server **tidak akan pernah tertimpa** oleh proses ini.
