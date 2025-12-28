<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// Ambil Data dari JSON (Fetch API)
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak valid']);
    exit;
}

// 1. CEK APAKAH SUDAH DISUKAI?
$check = db_query("SELECT id FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id], "ii");

if ($check->get_result()->num_rows > 0) {
    // JIKA SUDAH ADA: Jangan lakukan apa-apa (atau bisa return pesan)
    echo json_encode(['success' => false, 'message' => 'Produk sudah ada di daftar favorit']);
    exit;
}

// 2. JIKA BELUM ADA: Masukkan ke database (Quantity otomatis 1)
$insert = db_query("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)", [$user_id, $product_id], "ii");

if ($insert) {
    echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke Favorit']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan']);
}
?>