<?php
require_once '../config/database.php';
require_role('seller');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $seller_id = $_SESSION['user_id'];
    
    // Hapus hanya jika milik seller yang login
    db_query("DELETE FROM products WHERE id = ? AND seller_id = ?", [$id, $seller_id], "ii");
}

header('Location: manage-products.php');
exit;
?>