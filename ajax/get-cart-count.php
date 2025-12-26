<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['count' => 0]);
    exit;
}

$stmt = db_query("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?", [$_SESSION['user_id']], "i");
$result = $stmt->get_result()->fetch_assoc();

echo json_encode(['count' => (int)$result['count']]);
?>