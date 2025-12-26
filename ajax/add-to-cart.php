<?php
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = (int)$data['product_id'];
$user_id = $_SESSION['user_id'];

// Check if product exists and available
$stmt = db_query("SELECT id, seller_id FROM products WHERE id = ? AND status = 'available'", [$product_id], "i");
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak tersedia']);
    exit;
}

// Can't buy own product
if ($product['seller_id'] == $user_id) {
    echo json_encode(['success' => false, 'message' => 'Tidak bisa membeli produk sendiri']);
    exit;
}

// Add to cart or update quantity
$check = db_query("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id], "ii");
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $new_qty = $existing['quantity'] + 1;
    db_query("UPDATE cart SET quantity = ? WHERE id = ?", [$new_qty, $existing['id']], "ii");
} else {
    db_query("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)", [$user_id, $product_id], "ii");
}

echo json_encode(['success' => true]);
?>