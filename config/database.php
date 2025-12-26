<?php
// === config/database.php ===

// Cek apakah kode dijalankan di Railway (Environment Production)
if (getenv('RAILWAY_ENVIRONMENT')) {
    // --- KONFIGURASI RAILWAY (Otomatis) ---
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_PORT', getenv('MYSQLPORT'));
    
    // URL Railway (Otomatis ambil domain dari Railway)
    // Nanti di Railway Dashboard kamu harus set variable: PUBLIC_URL
    define('BASE_URL', getenv('PUBLIC_URL')); 
    
} else {
    // --- KONFIGURASI LOCALHOST (XAMPP) ---
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'fp_pwi');
    
    // URL Localhost (Sesuai folder kamu sekarang)
    define('BASE_URL', 'http://localhost/fp_pwi');
}

// Create connection
try {
    // Khusus Railway kadang butuh Port, jadi kita tambahkan
    $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Tampilkan error tapi jangan terlalu detail di production
    die("Database Error. Silakan cek koneksi.");
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions (Tidak Berubah)
function db_query($query, $params = [], $types = "") {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) die("Prepare failed: " . $conn->error);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
?>