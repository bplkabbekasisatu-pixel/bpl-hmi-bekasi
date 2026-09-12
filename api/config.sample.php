<?php
/**
 * ============================================================
 * KONFIGURASI DATABASE
 * ============================================================
 * Ganti KEEMPAT nilai di bawah ini sesuai database MySQL yang
 * Anda buat di dashboard 000webhost (menu "Database").
 *
 * Nama database & username di 000webhost biasanya berawalan id
 * akun hosting Anda, contoh: "id12345678_bplhmi".
 *
 * File ini hanya CONTOH (aman untuk di-commit ke git). Salin jadi
 * "config.php" lalu isi nilai sungguhannya di sana — "config.php"
 * sudah dikecualikan lewat .gitignore supaya kredensial asli tidak
 * pernah ikut ter-commit.
 * ============================================================
 */
define('DB_HOST', 'GANTI_HOST_DATABASE');
define('DB_NAME', 'GANTI_NAMA_DATABASE');
define('DB_USER', 'GANTI_USERNAME_DATABASE');
define('DB_PASS', 'GANTI_PASSWORD_DATABASE');

/**
 * Folder tempat menyimpan foto yang diunggah pengurus (logo,
 * foto pengurus/instruktur, foto sampul kegiatan/berita).
 * Folder "uploads" harus ada satu tingkat di atas folder "api"
 * dan bisa ditulis oleh server (permission 755 biasanya cukup).
 */
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

/**
 * URL publik menuju folder uploads tsb, dilihat dari root domain.
 * Jika aplikasi dipasang di folder utama (public_html langsung),
 * biarkan '/uploads/'. Jika dipasang di subfolder, misal
 * public_html/bpl/, ubah menjadi '/bpl/uploads/'.
 */
define('UPLOAD_URL', '/uploads/');

/**
 * Zona waktu untuk pencatatan tanggal.
 */
date_default_timezone_set('Asia/Jakarta');

/**
 * Membuka koneksi PDO ke MySQL. Dipanggil oleh setiap endpoint
 * lewat get_pdo() supaya koneksinya dibuat sekali saja per request.
 */
function get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Tidak bisa terhubung ke database. Cek kredensial di api/config.php.']);
            exit;
        }
    }
    return $pdo;
}
