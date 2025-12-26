<?php
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = (int)$data['cart_id'];
$quantity = (int)$data['quantity'];

if ($quantity < 1) {
    echo json_encode(['success' => false]);
    exit;
}

db_query("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", 
    [$quantity, $cart_id, $_SESSION['user_id']], "iii");

echo json_encode(['success' => true]);
?>