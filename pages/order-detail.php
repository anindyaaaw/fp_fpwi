<?php
require_once '../config/database.php';
require_login();

if (!isset($_GET['id'])) {
    header('Location: my-orders.php');
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Ambil Data Order
$query_order = "SELECT * FROM orders WHERE id = ? AND buyer_id = ?";
$stmt = db_query($query_order, [$order_id, $user_id], "ii");
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: my-orders.php');
    exit;
}

// 2. Ambil Item + INFO PENJUAL (Phone)
// Kita tambahkan JOIN ke users (u) untuk ambil nomor HP penjual
$query_items = "SELECT oi.*, p.name, p.image, p.category, u.phone as seller_phone, u.name as seller_name
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                JOIN users u ON p.seller_id = u.id
                WHERE oi.order_id = ?";
$items_result = db_query($query_items, [$order_id], "i")->get_result();

// Simpan items ke array supaya bisa dipakai berulang (untuk loop dan untuk ambil nomor HP)
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

// 3. LOGIC WA PENJUAL
// Ambil nomor HP dari item pertama (Asumsi 1 order = 1 penjual)
$seller_phone = '';
if (!empty($items)) {
    $raw_phone = $items[0]['seller_phone'];
    // Format ke 628xxx
    if (substr($raw_phone, 0, 1) == '0') {
        $seller_phone = '62' . substr($raw_phone, 1);
    } else {
        $seller_phone = $raw_phone;
    }
}

// Pesan WA: Menyertakan ID Order agar penjual paham
$wa_message = "Halo kak, saya mau tanya tentang Pesanan Order #" . $order_id;
$wa_link = "https://wa.me/" . $seller_phone . "?text=" . urlencode($wa_message);

$page_title = 'Detail Pesanan #' . $order_id;
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="my-orders.php">Pesanan Saya</a></li>
                    <li class="breadcrumb-item active">Order #<?= $order_id ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold">Detail Pesanan</h2>
        </div>
        
        <div class="text-end">
            <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">
                <i class="bi bi-check-circle-fill me-1"></i> Pembayaran Berhasil
            </span>
            <p class="text-muted small mt-2 mb-0">
                <?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-4 border-bottom">
                    <h5 class="fw-bold mb-0">Daftar Barang</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach($items as $item): ?>
                    <div class="p-4 border-bottom last-border-0">
                        <div class="d-flex gap-3">
                            <img src="<?= htmlspecialchars($item['image']) ?>" 
                                 class="rounded-3 object-fit-cover" 
                                 style="width: 80px; height: 80px;" 
                                 alt="<?= htmlspecialchars($item['name']) ?>">
                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                        <p class="text-muted small mb-1"><?= htmlspecialchars($item['category']) ?></p>
                                        <small class="text-primary"><i class="bi bi-shop me-1"></i><?= htmlspecialchars($item['seller_name']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <p class="fw-bold mb-0">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                                        <small class="text-muted">x<?= $item['quantity'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer bg-light p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted">Total Belanja</span>
                        <span class="h4 fw-bold text-primary mb-0">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Info Pengiriman</h5>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="bg-light p-2 rounded-circle text-primary">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Alamat Penerima</small>
                            <p class="mb-0 fw-medium"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-light p-2 rounded-circle text-primary">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Status Pengiriman</small>
                            <p class="mb-0 fw-medium">Sedang Diproses Penjual</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <?php if(!empty($seller_phone)): ?>
                <a href="<?= $wa_link ?>" target="_blank" class="btn btn-outline-success rounded-pill fw-semibold py-2">
                    <i class="bi bi-whatsapp me-2"></i>Hubungi Penjual
                </a>
                <?php else: ?>
                <button class="btn btn-outline-secondary rounded-pill" disabled>
                    <i class="bi bi-telephone-x me-2"></i>Kontak Tidak Tersedia
                </button>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/pages/products.php" class="btn btn-link text-decoration-none text-muted mt-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali Belanja
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.last-border-0:last-child { border-bottom: none !important; }
</style>

<?php include '../includes/footer.php'; ?>