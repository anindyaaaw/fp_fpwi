<?php
// === config/database.php ===

// 1. DETEKSI ENVIRONMENT (Railway vs Localhost)
// Kita cek apakah ada variabel MYSQLHOST yang otomatis disediakan Railway
if (getenv('MYSQLHOST')) {
    // --- KONFIGURASI RAILWAY (PRODUCTION) ---
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_PORT', getenv('MYSQLPORT'));
    
    // Base URL dari Environment Variable APP_URL di Railway
    // Jika belum diset, fallback ke domain Railway default
    $railway_url = getenv('RAILWAY_STATIC_URL') ? 'https://' . getenv('RAILWAY_STATIC_URL') : '';
    define('BASE_URL', getenv('APP_URL') ? getenv('APP_URL') : $railway_url);

} else {
    // --- KONFIGURASI LOCALHOST (DEVELOPMENT) ---
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'fp_pwi'); // <--- Pastikan ini sesuai nama DB di phpMyAdmin kamu
    define('DB_PORT', 3306);
    
    // URL Localhost
    define('BASE_URL', 'http://localhost/fp_pwi');
}

// 2. KONEKSI KE DATABASE
try {
    $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Koneksi Gagal: " . $conn->connect_error);
    }
    
    // Set charset agar emoji dan simbol aman
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Tampilkan error (Matikan die() ini jika sudah live production agar user tidak lihat error teknis)
    die("Database Error: " . $e->getMessage());
}

// 3. SESSION MANAGEMENT
// Wajib ada agar Login dan Keranjang berfungsi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. HELPER FUNCTIONS (Wajib ada untuk fitur Website kamu)

// Fungsi untuk query database yang aman (Prepared Statement)
function db_query($query, $params = [], $types = "") {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) die("Prepare failed: " . $conn->error);
    
    if (!empty($params)) {
        // Jika types kosong, otomatis generate (s = string, i = integer)
        if (empty($types)) {
            $types = str_repeat('s', count($params)); 
        }
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt;
}

// Cek status login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Ambil role user (admin/user)
function get_user_role() {
    return $_SESSION['role'] ?? null; // Pastikan di tabel users kolomnya 'role'
}

// Paksa harus login
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// Paksa harus role tertentu (misal admin)
function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
?>