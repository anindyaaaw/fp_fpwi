<?php
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

$query = "SELECT c.id, c.quantity, p.name, p.price, p.image 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ? AND p.status = 'available'";
$stmt = db_query($query, [$user_id], "i");
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total
]);
?>