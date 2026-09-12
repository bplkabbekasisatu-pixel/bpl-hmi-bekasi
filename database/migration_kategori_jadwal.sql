-- ============================================================
-- Migrasi: pisahkan Program Kerja BPL & Program Kerja Bidang PA
-- ============================================================
-- Jalankan SEKALI lewat phpMyAdmin (tab SQL) pada database situs yang
-- SUDAH LIVE sebelum pemisahan ini dibuat. Jangan dijalankan pada
-- database yang baru diimpor dari database/schema.sql versi terbaru —
-- kolom kategori di situ sudah otomatis ada.
--
-- Sebelum migrasi ini, tabel `jadwal` dipakai bersama oleh tiga tempat
-- tampilan (Jadwal Kegiatan di Beranda, Program Kerja Bidang PA, dan
-- Program Kerja BPL) — mengedit salah satu otomatis mengubah semuanya.
-- Setelah migrasi ini, setiap baris jadwal punya kategori sendiri
-- ('bpl' atau 'pa') sehingga Program Kerja BPL dan Program Kerja
-- Bidang PA bisa dikelola (tambah/ubah/hapus) secara terpisah.

ALTER TABLE jadwal
  ADD COLUMN kategori ENUM('bpl','pa') NOT NULL DEFAULT 'bpl'
    COMMENT 'bpl = Program Kerja BPL, pa = Program Kerja Bidang PA'
    AFTER program;

-- Semua data lama otomatis masuk kategori 'bpl' (nilai default di atas).
-- Setelah menjalankan migrasi ini, buka mode Login Pengurus di situs,
-- lalu tinjau daftar di bagian "Program Kerja BPL" — pindahkan baris
-- yang sebenarnya milik Bidang PA dengan tombol ubah (✎) dan ganti
-- pilihan "Bidang" ke "Program Kerja Bidang PA".
