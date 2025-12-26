<?php
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$shipping_address = $conn->real_escape_string($data['address']);

// Get cart items
$cart_query = "SELECT c.*, p.price, p.seller_id FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$stmt = db_query($cart_query, [$user_id], "i");
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit;
}

// Calculate total
$total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart_items));

// Create order
$conn->begin_transaction();

try {
    $stmt = db_query("INSERT INTO orders (buyer_id, total_amount, shipping_address) VALUES (?, ?, ?)",
        [$user_id, $total, $shipping_address], "ids");
    $order_id = $conn->insert_id;
    
    // Create order items
    foreach ($cart_items as $item) {
        db_query("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)",
            [$order_id, $item['product_id'], $item['seller_id'], $item['quantity'], $item['price']], "iiiid");
        
        // Update product status
        db_query("UPDATE products SET status = 'pending' WHERE id = ?", [$item['product_id']], "i");
    }
    
    // Clear cart
    db_query("DELETE FROM cart WHERE user_id = ?", [$user_id], "i");
    
    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Checkout gagal']);
}
?>