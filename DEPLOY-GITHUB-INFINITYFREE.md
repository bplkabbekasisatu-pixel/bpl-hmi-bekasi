# Pindah & Online-kan Situs Ini di InfinityFree — Panduan Lengkap dari Nol

## Sebelum mulai: bagian mana yang bisa dibantu otomatis, mana yang harus Anda kerjakan sendiri

- **Sudah disiapkan di repo ini**: workflow GitHub Actions (`.github/workflows/deploy.yml`)
  sudah diatur untuk deploy ke InfinityFree (termasuk pakai FTPS, yang diwajibkan
  InfinityFree). Anda tinggal isi 4 secret di GitHub setelah dapat kredensialnya.
- **Harus Anda kerjakan sendiri** (tidak bisa diwakilkan, karena butuh akun & kredensial
  pribadi Anda): daftar/masuk ke dashboard InfinityFree, membuat website & database di
  sana, mengambil kredensial FTP & database, lalu memasukkannya ke GitHub Secrets dan
  ke `api/config.php` di server.

Dokumen ini mengganti `DEPLOY-GITHUB-PROFREEHOST.md` (sekarang situs pindah dari
ProFreeHost ke InfinityFree — dua-duanya sama-sama jaringan hosting gratis iFastNet,
tapi domain, kredensial, dan sedikit detail teknisnya beda, terutama: InfinityFree
mewajibkan **FTPS** (bukan FTP polos) dan hostname database **bukan** `localhost`).

## Kenapa langkah kali ini dibuat lebih hati-hati

Migrasi sebelumnya sempat membingungkan karena perubahan kode yang sudah di-deploy
tidak langsung terlihat di situs live, dan penyebabnya sulit dipastikan dari luar
(cache browser? cache hosting? atau folder tujuan FTP-nya memang salah?). Supaya
tidak terulang, panduan ini menambahkan **langkah verifikasi manual** di setiap
tahap penting — upload 1 file kecil dulu dan cek lewat browser — SEBELUM
menyambungkan ke otomatisasi GitHub Actions. Jangan lewati langkah verifikasi ini.

---

## Bagian 0 — Yang perlu disiapkan

- Email aktif untuk daftar/masuk ke InfinityFree.
- Sekitar 30–45 menit.
- (Opsional tapi sangat disarankan) Aplikasi **FileZilla** (gratis) di komputer,
  untuk tes koneksi FTP manual sebelum lewat GitHub Actions:
  https://filezilla-project.org/

---

## Bagian 1 — Buat akun & website baru di InfinityFree

1. Buka **https://dash.infinityfree.com/accounts** (kalau belum punya akun, akan
   diarahkan ke halaman daftar dulu — ikuti saja alurnya, verifikasi email jika diminta).
2. Setelah masuk ke dashboard, klik **Create Account** (atau "New Account" /
   "Add Website", istilah persisnya bisa sedikit berbeda tergantung versi antarmuka).
3. Saat diminta nama domain, pilih salah satu:
   - **Opsi A (disarankan untuk sekarang): subdomain gratis dari InfinityFree**,
     contoh `bplhmibekasi.infinityfreeapp.com`. Langsung aktif, **tidak perlu** ubah
     DNS/nameserver sama sekali — paling kecil risiko salah konfigurasi.
   - **Opsi B: domain sendiri** (custom domain yang sudah Anda punya/beli). Ini perlu
     langkah tambahan ubah **nameserver** domain ke nameserver InfinityFree di tempat
     Anda beli domain, lalu tunggu propagasi DNS (bisa beberapa jam). Kalau pilih ini,
     kerjakan Bagian 1–7 dulu pakai subdomain gratis sampai situs benar-benar online
     dan terverifikasi, baru pindahkan ke custom domain supaya troubleshooting lebih
     mudah (tidak bercampur masalah DNS dengan masalah deploy).
4. Tunggu proses pembuatan akun selesai (biasanya 1–5 menit, akan ada notifikasi
   "Account created" atau email konfirmasi).
5. Catat **nama akun** Anda — formatnya biasanya seperti `if0_XXXXXXXX` (8 digit angka
   setelah `if0_`). Ini dipakai sebagai prefix di banyak tempat (nama database, username
   FTP, dll).

---

## Bagian 2 — Cek struktur folder DULU, sebelum apa pun lainnya (kunci anti salah folder)

Ini bagian yang paling penting untuk mencegah masalah "sudah di-deploy tapi tidak
muncul" seperti kemarin.

1. Dari dashboard, klik akun hosting yang baru dibuat → masuk ke **Control Panel**-nya.
2. Buka menu **File Manager**.
3. Perhatikan folder-folder di root. Di InfinityFree, foldernya biasanya:
   ```
   /
   ├── htdocs/     <- INI folder webroot. index.html HARUS ada langsung di sini.
   ├── php.ini (kadang ada)
   └── ...
   ```
4. **Uji coba manual** (jangan lewati langkah ini):
   a. Masuk ke folder `htdocs`.
   b. Buat file teks baru, nama bebas misal `cek.txt`, isi bebas misal `oke`.
   c. Buka `http://SUBDOMAIN-ANDA.infinityfreeapp.com/cek.txt` di browser (ganti
      sesuai domain Anda dari Bagian 1).
   d. **Kalau muncul teks "oke"** → folder `htdocs` sudah benar, inilah folder yang
      nanti dipakai sebagai `FTP_SERVER_DIR`.
   e. **Kalau muncul 404 Not Found** → berarti struktur foldernya beda (jarang terjadi
      untuk akun baru, tapi bisa terjadi kalau Anda pakai fitur "Addon Domain" di akun
      lama yang sudah ada situs lain — foldernya jadi `htdocs/namadomain.com/`). Coba
      cek folder lain di File Manager sampai ketemu yang pas, ulangi tes ini sampai
      berhasil.
   f. **Hapus `cek.txt`** setelah tes berhasil (bukan file yang perlu ada di situs).
5. Catat path folder yang sudah terbukti benar ini (biasanya cukup `/htdocs/` —
   dengan garis miring di depan dan belakang).

---

## Bagian 3 — Buat database MySQL & impor struktur

1. Di Control Panel, buka menu **MySQL Databases**.
2. Klik **Create Database**, isi nama misal `bplhmi` → otomatis jadi nama lengkap
   seperti `if0_XXXXXXXX_bplhmi`.
3. Catat/atur password database-nya (kalau tidak diminta saat create, cari opsi
   "Change Password" di halaman yang sama) — **simpan baik-baik**, biasanya cuma
   ditampilkan sekali.
4. **Catat MySQL hostname-nya** — ini beda dari kebanyakan hosting cPanel biasa!
   Di InfinityFree, hostname database **bukan** `localhost`, melainkan sesuatu seperti
   `sqlXXX.infinityfree.com` (nomor beda-beda per akun — lihat persis di halaman MySQL
   Databases, biasanya tertulis di bagian atas atau di detail database). Salah isi
   bagian ini adalah penyebab umum error "tidak bisa konek ke database" nantinya.
5. Buka **phpMyAdmin** dari Control Panel (biasanya ada tombol langsung di halaman
   MySQL Databases atau menu tersendiri).
6. Pilih database yang baru dibuat di panel kiri → tab **Import** → pilih file
   `database/schema.sql` dari folder proyek ini di komputer Anda → klik **Go**.
7. Pastikan muncul pesan sukses dan ada **11 tabel baru** (admins, site_content,
   pengurus, kader_db, kader_monitoring, administrasi, dst).

---

## Bagian 4 — Ambil kredensial FTP & tes koneksi manual

1. Di Control Panel, buka menu **FTP Accounts**.
2. Catat:
   - **FTP Server / Hostname** — untuk InfinityFree umumnya `ftpupload.net`, tapi
     **selalu cek persis** di halaman ini karena bisa beda per akun.
   - **Username FTP** — biasanya diawali `if0_XXXXXXXX` (sama seperti nama akun Anda).
   - **Password FTP** — kalau lupa/belum tahu, cari opsi reset password di halaman ini.
   - **Port** — biasanya 21.
3. **PENTING**: InfinityFree mewajibkan **FTPS** (FTP over TLS), bukan FTP polos tanpa
   enkripsi. Workflow di repo ini (`.github/workflows/deploy.yml`) sudah diatur
   `protocol: ftps` untuk ini — tidak perlu diubah.
4. **Tes koneksi manual pakai FileZilla dulu sebelum setting GitHub Actions**
   (sangat disarankan, supaya kalau ada masalah kredensial/folder, ketahuan di sini
   dulu — bukan setelah berkali-kali gagal di GitHub Actions):
   a. Buka FileZilla → **File → Site Manager → New Site**.
   b. Host: isi FTP Server dari langkah 2. Port: 21.
   c. **Encryption**: pilih **"Require explicit FTP over TLS"**.
   d. Logon Type: Normal. Isi Username & Password dari langkah 2.
   e. Klik **Connect** (kalau muncul dialog sertifikat TLS, klik OK/Accept).
   f. Setelah konek, lihat folder yang tersedia di panel kanan (folder di server) —
      pastikan ada folder `htdocs` seperti yang sudah dicek di Bagian 2.
   g. Masuk ke `htdocs`, upload manual 1 file kecil (drag-drop dari panel kiri ke
      kanan), lalu cek lewat browser seperti di Bagian 2 langkah c–d.
   h. Kalau berhasil muncul di browser, **path FTP yang benar sudah terkonfirmasi** —
      lanjut ke Bagian 5. Kalau belum, ulangi pengecekan folder sebelum lanjut;
      jangan lanjut ke GitHub Actions sebelum tes manual ini berhasil.

---

## Bagian 5 — Simpan kredensial ke GitHub Secrets

Langkah ini **harus Anda kerjakan sendiri langsung di GitHub** — kredensial FTP dan
database sebaiknya tidak pernah diketikkan ke chat/percakapan dengan siapa pun,
termasuk ke saya, demi keamanan.

1. Buka repo ini di github.com → **Settings → Secrets and variables → Actions**.
2. Kalau 4 secret ini sudah pernah dibuat untuk hosting sebelumnya, **edit isinya saja**
   (nama secret tidak perlu diubah, cukup klik "Update" pada tiap secret):

   | Nama secret | Isi | Sumbernya dari |
   |---|---|---|
   | `FTP_SERVER` | FTP Server InfinityFree | Bagian 4, langkah 2 |
   | `FTP_USERNAME` | Username FTP InfinityFree | Bagian 4, langkah 2 |
   | `FTP_PASSWORD` | Password FTP InfinityFree | Bagian 4, langkah 2 |
   | `FTP_SERVER_DIR` | Folder webroot yang **sudah terbukti benar** | Bagian 2 & 4 (biasanya `/htdocs/`) |

3. ⚠️ **`FTP_SERVER_DIR` wajib persis sama** dengan folder yang sudah diverifikasi
   manual di Bagian 2 & 4 — bukan sekadar tebakan. Ini yang mencegah masalah
   "ter-upload tapi tidak muncul di domain" terulang lagi.

---

## Bagian 6 — Push kode & tunggu deploy otomatis

Workflow-nya sudah disiapkan di repo ini (`.github/workflows/deploy.yml`), tidak
perlu diubah lagi. Setelah 4 secret di Bagian 5 terisi:

1. Pastikan branch `main` sudah berisi kode terbaru (kalau Anda baca ini dari PR yang
   masih terbuka, tunggu sampai di-merge dulu, atau push langsung ke `main`).
2. Buka tab **Actions** di repo GitHub → lihat run **"Deploy ke InfinityFree"** yang
   otomatis berjalan setiap ada push ke `main`.
3. Tunggu sampai selesai (biasanya 1–2 menit) dan pastikan tanda centang **hijau ✅**.
   Kalau merah ❌, buka detail run-nya — error yang paling umum di FTP-Deploy-Action
   adalah kredensial salah atau `FTP_SERVER_DIR` salah (ulangi verifikasi di Bagian 2 & 4).

---

## Bagian 7 — Isi `api/config.php` langsung di server (manual, sekali saja)

File `api/config.php` sengaja **tidak** ikut ter-deploy otomatis (lihat `.gitignore`),
demi keamanan kredensial database. Isi sekali manual:

1. Buka **File Manager** InfinityFree, masuk ke folder `htdocs/api/`.
2. Upload file `api/config.sample.php` dari proyek ini (lewat File Manager, tombol
   Upload), lalu **rename** jadi `config.php` di sana.
3. Edit file itu langsung di File Manager (klik kanan → Edit), isi:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com'); // dari Bagian 3, langkah 4 — BUKAN 'localhost'
   define('DB_NAME', 'if0_XXXXXXXX_bplhmi');       // dari Bagian 3
   define('DB_USER', 'if0_XXXXXXXX_bplhmi');       // dari Bagian 3 (biasanya sama dgn nama DB)
   define('DB_PASS', 'passwordDatabaseAnda');       // dari Bagian 3
   ```
   (Kalau `config.sample.php` di proyek ini belum punya baris `DB_HOST`, tambahkan —
   InfinityFree **mewajibkan** hostname database eksplisit, tidak bisa `localhost`.)
4. Simpan.
5. Pastikan folder `htdocs/uploads/` ada, dan permission-nya **755** (klik kanan folder
   → Change Permissions/CHMOD; kalau 755 gagal saat upload foto nanti, coba 775).

---

## Bagian 8 — Buka situsnya & verifikasi menyeluruh

1. Akses domain Anda (subdomain InfinityFree dari Bagian 1, atau custom domain kalau
   sudah propagasi DNS-nya).
2. Situs harus tampil sebagai tamu (read-only) dulu.
3. Login pengurus default: **admin / Admin123** — **segera ganti** (lihat bagian
   "Mengganti password admin" di `README.md`), karena password ini tertulis polos di
   `database/schema.sql`.
4. Setelah login, coba buka halaman **Database Kader** — pastikan tabelnya termuat dan
   ada navigasi "Sebelumnya"/"Selanjutnya" di bawahnya (fitur paginasi terbaru).
5. Coba tambah 1 data uji coba di mana pun (misal data kader), refresh halaman,
   pastikan datanya tetap ada — ini konfirmasi database benar-benar tersambung.

---

## Bagian 9 — Aktifkan HTTPS

- Untuk **subdomain gratis InfinityFree** (`*.infinityfreeapp.com`), HTTPS biasanya
  sudah otomatis aktif tanpa perlu setting tambahan.
- Untuk **custom domain**, buka menu **SSL/TLS** di Control Panel, aktifkan
  **Let's Encrypt Free SSL** untuk domain Anda. Prosesnya kadang butuh beberapa jam
  setelah DNS domain benar-benar mengarah ke InfinityFree.

---

## Batasan jujur InfinityFree (baca sebelum menganggap ini pengganti hosting berbayar)

Sama seperti ProFreeHost sebelumnya — ini jaringan hosting gratis (iFastNet), jadi ada
batasan: tidak ada SSH, resource CPU/inode dibatasi, situs yang lama tidak dikunjungi
berisiko ditangguhkan, dan tidak cocok untuk kebutuhan traffic tinggi/bisnis serius.
Untuk situs organisasi dengan traffic wajar biasanya cukup. Rutin **backup database &
folder uploads** (caranya ada di `README.md`).

---

## Kalau situs sudah online tapi perubahan kode baru tidak muncul

1. Cek tab **Actions** — pastikan run terbaru untuk commit yang dimaksud statusnya
   **success**, bukan sekadar "sudah push".
2. Hard refresh browser: `Ctrl+Shift+R` (Windows/Linux) / `Cmd+Shift+R` (Mac), atau
   buka di jendela penyamaran/incognito — ini penyebab paling umum.
3. Kalau masih belum muncul setelah itu, ulangi tes manual di Bagian 2/4 (upload 1
   file kecil ke `htdocs/` lewat File Manager, cek lewat browser) untuk memastikan
   `FTP_SERVER_DIR` yang tersimpan di GitHub Secrets masih menunjuk ke folder yang
   sama dengan yang situs benar-benar baca.

## Alur kerja sehari-hari setelah semua ini selesai

Sama seperti sebelumnya — lihat `ALUR-KERJA.md` untuk langkah lengkap setiap kali ada
perubahan kode. Ringkasnya: edit lokal → commit → push/merge ke `main` → GitHub
Actions otomatis deploy ke InfinityFree dalam 1–2 menit. `uploads/` dan
`api/config.php` di server tidak pernah tertimpa oleh proses ini.
