<?php
require_once '../config/database.php';
require_login();

$page_title = 'Pesanan Saya - Thrift & Swap';

$user_id = $_SESSION['user_id'];
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
          FROM orders o 
          WHERE o.buyer_id = ? 
          ORDER BY o.created_at DESC";
$stmt = db_query($query, [$user_id], "i");
$orders = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Pesanan Saya</h2>

    <div class="row">
        <div class="col-md-12">
            <?php if ($orders->num_rows > 0): ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-success rounded-pill px-3">Lunas</span>
                                <span class="text-muted ms-2 small">Order #<?= $order['id'] ?></span>
                            </div>
                            <small class="text-muted"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <p class="mb-1 text-muted">Total Pembayaran</p>
                                <h5 class="fw-bold mb-0">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></h5>
                                <small class="text-muted"><?= $order['total_items'] ?> Barang</small>
                            </div>
                            <a href="<?= BASE_URL ?>/pages/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-dark rounded-pill btn-sm">
                                Detail Pesanan <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-light rounded-4">
                    <i class="bi bi-bag-x fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">Belum ada pesanan</p>
                    <a href="<?= BASE_URL ?>/pages/products.php" class="btn btn-primary-modern">Mulai Belanja</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>