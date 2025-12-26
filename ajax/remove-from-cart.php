<?php
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = (int)$data['cart_id'];

db_query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cart_id, $_SESSION['user_id']], "ii");

echo json_encode(['success' => true]);
?>