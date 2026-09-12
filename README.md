# BPL x Bidang PA HMI Kabupaten Bekasi — Versi Dinamis (Backend Sungguhan)

Situs ini sekarang punya **server backend PHP + database MySQL sungguhan**,
bukan lagi data yang ditempel di dalam file HTML. Artinya:

- Login pengurus diverifikasi di server (password di-hash, bukan tertulis polos di kode)
- Semua data (pengurus, jadwal, kader, dll.) tersimpan di database, bukan di file
- Foto yang diunggah benar-benar tersimpan sebagai file di server
- Pengajuan surat Komisariat (Administrasi) benar-benar masuk ke database dan bisa
  ditinjau pengurus, bukan cuma terbuka lewat WhatsApp/Email
- Berjalan di hosting **cPanel dengan PHP + MySQL** — jenis hosting paling umum

## Isi paket ini

```
index.html          <- halaman situs (upload ke public_html)
api/
  config.php        <- WAJIB diisi kredensial database Anda sebelum upload
  helpers.php        
  index.php         <- semua endpoint API
uploads/            <- folder kosong, tempat foto tersimpan (harus bisa ditulis server)
database/
  schema.sql        <- struktur + data awal, diimpor lewat phpMyAdmin
```

## Langkah pemasangan di cPanel

### 1. Buat database MySQL
1. Masuk cPanel → **MySQL® Databases**.
2. Buat database baru, contoh nama: `bplhmi` (cPanel otomatis menambah prefix, jadi
   nama lengkapnya jadi semacam `namauser_bplhmi`).
3. Buat user database baru dengan password yang kuat.
4. Di bagian **Add User to Database**, tambahkan user tsb ke database tadi dan
   centang **ALL PRIVILEGES**.
5. Catat tiga hal ini — akan dipakai di langkah 3: **nama database**, **username**, **password**.

### 2. Impor struktur database
1. Masuk cPanel → **phpMyAdmin**.
2. Klik database yang baru dibuat di sebelah kiri.
3. Klik tab **Import**, pilih file `database/schema.sql` dari paket ini, klik **Go**.
4. Pastikan muncul pesan sukses dan ada 11 tabel baru (admins, site_content, pengurus, dst).

### 3. Isi kredensial di config.php
Buka `api/config.php` dengan text editor, ganti 3 baris ini:
```php
define('DB_NAME', 'GANTI_NAMA_DATABASE');
define('DB_USER', 'GANTI_USERNAME_DATABASE');
define('DB_PASS', 'GANTI_PASSWORD_DATABASE');
```
dengan nama database, username, dan password dari langkah 1. Simpan filenya.

### 4. Upload semua file
Lewat **File Manager** cPanel atau FTP (FileZilla dkk):
1. Masuk ke folder `public_html` (atau subfolder jika situs dipasang di subfolder).
2. Upload `index.html`, folder `api/`, dan folder `uploads/` — **strukturnya harus
   sejajar** persis seperti di paket ini (jangan taruh `index.html` di dalam folder `api`, dst).
3. Folder `database/` **tidak perlu diupload** ke server — isinya sudah dipakai di langkah 2.

### 5. Beri izin tulis ke folder uploads
Di File Manager, klik kanan folder `uploads` → **Change Permissions** → set ke **755**
(atau 775 jika 755 masih gagal saat upload foto nanti).

### 6. Aktifkan HTTPS
Di cPanel → **SSL/TLS Status** → aktifkan **AutoSSL** untuk domain Anda (biasanya gratis).
Ini penting supaya sesi login pengurus lebih aman.

### 7. Selesai — coba akses
Buka domain Anda di browser. Situs akan tampil sebagai tamu (read-only).

**Login pengurus default:**
- Username: `admin`
- Password: `Admin123`

⚠️ **Segera ganti password ini** setelah login pertama kali (lihat bagian
"Mengganti password admin" di bawah), karena password default ini tertulis
di file `database/schema.sql` yang mungkin pernah Anda simpan/bagikan.

## Mengganti password admin

Saat ini penggantian password belum ada tombolnya di UI. Cara tercepat lewat phpMyAdmin:
1. Buat hash password baru. Cara termudah: buat file sementara `buat_hash.php` isinya:
   ```php
   <?php echo password_hash('PasswordBaruAnda', PASSWORD_BCRYPT);
   ```
   Upload, akses lewat browser (`namadomain.com/buat_hash.php`), salin hasilnya
   (diawali `$2y$10$...`), lalu **hapus file ini dari server** (jangan ditinggal).
2. Di phpMyAdmin, buka tabel `admins`, edit baris `admin`, tempelkan hash tadi ke
   kolom `password_hash`, simpan.

## Menambah akun pengurus lain

Lewat phpMyAdmin, tabel `admins` → Insert baris baru, isi `username`, `display_name`,
dan `password_hash` (dibuat dengan cara yang sama seperti di atas).

## Cara kerja Administrasi (surat-menyurat)

Siapa saja bisa mengisi form di halaman "Administrasi" tanpa login. Begitu dikirim,
datanya langsung tersimpan di tabel `administrasi`. Pengurus yang login akan melihat
daftarnya di bagian bawah halaman Administrasi ("Surat Masuk"), bisa mengubah status
(Diajukan/Diproses/Selesai) dan menghapusnya.

## Backup

Sesekali unduh backup database lewat phpMyAdmin (**Export** → **Quick** → **Go**) dan
simpan salinan folder `uploads/` — dua hal inilah yang menyimpan seluruh isi situs Anda.

## Menyimpan & meng-online-kan lewat GitHub (opsional, direkomendasikan)

**Penting untuk dipahami dulu:** GitHub Pages (fitur hosting gratis bawaan GitHub)
**tidak bisa menjalankan PHP atau MySQL** — itu cuma untuk file statis (HTML/CSS/JS biasa).
Karena situs ini sekarang punya backend PHP + database sungguhan, GitHub Pages saja
**tidak cukup**.

Solusinya: GitHub tetap dipakai untuk menyimpan & mengelola kode (versioning, riwayat
perubahan, kolaborasi), sementara **hosting cPanel Anda yang tetap menjalankan situsnya**
(karena di situlah PHP dan MySQL bisa berjalan). Supaya tetap terasa "online lewat GitHub",
paket ini sudah dilengkapi **GitHub Actions** yang otomatis mengirim file ke hosting cPanel
Anda setiap kali Anda `git push` — jadi alurnya: edit kode → push ke GitHub → situs di
cPanel otomatis ter-update, tanpa perlu upload manual lagi lewat File Manager/FTP.

File yang sudah disiapkan untuk ini:
```
.gitignore                     <- supaya config.php asli & folder uploads tidak ikut ke GitHub
.github/workflows/deploy.yml   <- robot yang mengirim file ke cPanel lewat FTP tiap push
api/config.example.php         <- contoh config.php tanpa kredensial asli (aman disimpan di GitHub)
```

### Langkah-langkahnya

1. **Buat akun & repository GitHub**
   Daftar di [github.com](https://github.com) kalau belum punya akun. Klik **New repository**,
   beri nama misal `bpl-hmi-bekasi`, pilih **Private** (lebih aman), jangan centang apa pun
   yang lain, klik **Create repository**.

2. **Upload folder ini ke repository**
   Cara termudah tanpa perlu install apa pun: di halaman repo yang baru dibuat, klik
   **uploading an existing file**, lalu **drag-and-drop** seluruh isi folder `bpl-backend`
   (index.html, folder api, folder database, dst — termasuk file `.gitignore` yang
   tersembunyi). Klik **Commit changes**.
   *(Kalau Anda familiar dengan Git lewat command line, cara biasa `git init` → `git add .`
   → `git commit` → `git push` juga bisa dipakai.)*

3. **Ambil kredensial FTP dari cPanel**
   Di cPanel, buka **FTP Accounts**. Kalau belum ada akun FTP, buat satu baru (atau pakai
   akun FTP utama hosting Anda). Catat: **FTP server/host**, **username**, **password**.

4. **Simpan kredensial itu sebagai "Secrets" di GitHub** (supaya tidak terlihat publik)
   Di repo GitHub Anda: **Settings** → **Secrets and variables** → **Actions** →
   **New repository secret**. Buat 3 secret ini satu per satu:
   - `FTP_SERVER` → isi dengan alamat FTP (misal `ftp.namadomainanda.com`)
   - `FTP_USERNAME` → username FTP
   - `FTP_PASSWORD` → password FTP

5. **Sesuaikan folder tujuan (jika perlu)**
   Buka file `.github/workflows/deploy.yml` di repo, cek baris `server-dir: /public_html/`.
   Kalau situs Anda dipasang di subfolder (misal `public_html/bpl/`), ubah jadi
   `server-dir: /public_html/bpl/`.

6. **Setup awal server tetap manual sekali saja**
   Karena `config.php` dan folder `uploads/` sengaja **tidak** ikut dikirim otomatis
   (demi keamanan kredensial & supaya foto yang sudah ada tidak tertimpa), langkah 1–6
   di bagian **"Langkah pemasangan di cPanel"** di atas tetap perlu dilakukan **satu kali**
   di awal (buat database, impor schema.sql, isi config.php asli langsung di server,
   siapkan folder uploads). Setelah itu, GitHub Actions hanya akan mengirim pembaruan
   kode (index.html, api/index.php, dst), tidak pernah menyentuh config.php atau uploads/
   yang sudah ada di server.

7. **Selesai — coba jalankan**
   Tab **Actions** di GitHub akan menampilkan proses "Deploy ke cPanel" berjalan
   otomatis setiap ada push ke branch `main`. Klik untuk melihat progres/errornya kalau ada.

Setelah semua ini siap, alur kerja Anda ke depannya jadi: edit file → upload ulang
(drag-and-drop) atau `git push` → tunggu beberapa puluh detik → situs di cPanel otomatis
ter-update.

## Batasan yang perlu diketahui

- Ukuran unggah foto dibatasi 4MB per file di sisi server (`api/index.php`), dan
  otomatis dipadatkan ke sekitar beberapa ratus KB oleh browser sebelum diunggah.
- Jika hosting Anda membatasi ukuran unggah PHP lebih kecil dari itu (`upload_max_filesize`
  di `php.ini`), minta host menaikkannya atau turunkan batas di kode jika perlu.
- Backend ini memakai PDO MySQL — pastikan ekstensi `pdo_mysql` aktif di PHP hosting
  Anda (di cPanel modern hampir selalu sudah aktif secara default).
