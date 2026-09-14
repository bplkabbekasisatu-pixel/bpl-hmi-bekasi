# Alur kerja saat ada perubahan kode

Dokumen ini menjelaskan langkah yang harus diikuti setiap kali ada perubahan
kode di proyek ini (`index.html`, folder `api/`, dll). Deploy ke situs live
(hosting InfinityFree — lihat `DEPLOY-GITHUB-INFINITYFREE.md` untuk domain
yang dipakai) sudah **otomatis** lewat GitHub Actions — jadi alur di bawah
ini fokus supaya perubahan aman sebelum dan sesudah otomatis ter-deploy.

## Ringkasan alur

```
Edit kode lokal
      │
      ▼
Cek dulu sebelum commit (lihat "Sebelum commit" di bawah)
      │
      ▼
git add + commit (pesan jelas)
      │
      ▼
Push ke branch kerja (bukan langsung ke main untuk perubahan besar)
      │
      ▼
Buat Pull Request ke main
      │
      ▼
Review (diri sendiri / pengurus lain)
      │
      ▼
Merge PR ke branch main
      │
      ▼
GitHub Actions "Deploy ke InfinityFree" otomatis jalan (± 1–2 menit)
      │
      ▼
Cek tab Actions: pastikan centang hijau ✅
      │
      ▼
Buka situs live, verifikasi perubahan tampil & tidak ada yang rusak
      │
      ▼
Kalau ada masalah → lihat "Kalau ada masalah setelah deploy" di bawah
```

## Langkah detail

### 1. Edit kode di lokal
Buka file yang perlu diubah (`index.html`, file di `api/`, dst) dan lakukan
perubahan.

### 2. Sebelum commit
- Buka `index.html` di browser (atau jalankan server lokal) untuk memastikan
  tidak ada error tampilan / JS yang jelas terlihat.
- Kalau mengubah file di `api/`, cek syntax PHP-nya tidak error (`php -l nama_file.php`
  kalau PHP tersedia di lokal).
- Jangan sentuh `api/config.php` di repo — file ini sengaja tidak ikut di-track
  git (lihat `.gitignore`) supaya kredensial database tidak bocor. Perubahan
  kredensial dilakukan manual langsung di server (lihat `DEPLOY-GITHUB-INFINITYFREE.md`).

### 3. Commit
Gunakan pesan commit yang jelas tentang **kenapa** perubahan dilakukan, bukan
sekadar mengulang isi diff.

### 4. Push & buat Pull Request
- Untuk perubahan kecil dan aman, push langsung ke `main` juga bisa.
- Untuk perubahan yang lebih besar atau perlu ditinjau dulu, push ke branch
  terpisah lalu buat Pull Request ke `main` supaya ada kesempatan review
  sebelum masuk ke situs live.

### 5. Merge ke main → deploy otomatis
Begitu perubahan masuk ke branch `main` (baik lewat push langsung maupun
merge PR), workflow `.github/workflows/deploy.yml` otomatis mengirim file ke
InfinityFree lewat FTPS. Prosesnya bisa dipantau di tab **Actions** repo GitHub.

### 6. Verifikasi
- Pastikan run di tab Actions selesai dengan centang hijau ✅.
- Buka domain situs (lihat `DEPLOY-GITHUB-INFINITYFREE.md`) dan cek perubahan
  sudah tampil — kalau belum, coba hard refresh (`Ctrl+Shift+R`) dulu sebelum
  curiga ada yang salah.
- Coba alur yang relevan dengan perubahan (mis. kalau ubah form, coba isi
  dan kirim form-nya).

### 7. Yang TIDAK ikut ter-deploy otomatis
`uploads/` dan `api/config.php` di server **tidak pernah ditimpa** oleh
proses deploy ini (lihat pengecualian di `deploy.yml`). Perubahan pada
keduanya harus dilakukan manual lewat File Manager InfinityFree.

## Kalau ada masalah setelah deploy

Deploy ini bersifat FTP overwrite, jadi tidak ada tombol "rollback" otomatis.
Cara memperbaiki:
1. Perbaiki masalahnya di kode lokal.
2. Commit perubahan perbaikan dengan pesan yang menjelaskan bug yang diperbaiki.
3. Push / merge ke `main` seperti biasa — deploy otomatis akan mengirim
   perbaikan itu dalam 1–2 menit.

Kalau perlu kembali cepat ke versi sebelumnya, `git revert` commit yang
bermasalah lalu push ke `main` — ini akan memicu deploy ulang dengan kode versi
sebelumnya.
