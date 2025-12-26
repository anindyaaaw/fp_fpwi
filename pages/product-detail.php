<?php
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = (int)$_GET['id'];

// --- UPDATE SQL DISINI ---
// Kita tambahkan 'u.phone as seller_phone' untuk mengambil nomor WA penjual
$stmt = db_query("SELECT p.*, u.name as seller_name, u.phone as seller_phone 
                  FROM products p 
                  JOIN users u ON p.seller_id = u.id 
                  WHERE p.id = ?", [$id], "i");
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

// --- LOGIC NOMOR WA ---
$phone = $product['seller_phone'];
// Jika nomor diawali '0', ganti dengan '62'
if (substr($phone, 0, 1) == '0') {
    $phone = '62' . substr($phone, 1);
}
// Pesan otomatis saat klik tombol
$wa_message = "Halo kak, saya tertarik dengan produk *" . $product['name'] . "* di Thrift & Swap. Apakah masih tersedia?";
$wa_link = "https://wa.me/" . $phone . "?text=" . urlencode($wa_message);

$page_title = $product['name'] . ' - Thrift & Swap';
include '../includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-7">
            <div class="rounded-4 overflow-hidden shadow-sm border">
                <img src="<?= htmlspecialchars($product['image']) ?>" 
                     class="w-100 object-fit-cover" 
                     style="max-height: 600px;" 
                     alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
        </div>

        <div class="col-lg-5">
            <div class="sticky-top" style="top: 100px;">
                <div class="mb-2">
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($product['category']) ?></span>
                    <span class="badge-condition condition-<?= str_replace(' ', '-', strtolower($product['condition_item'])) ?> ms-2">
                        <?= htmlspecialchars($product['condition_item']) ?>
                    </span>
                </div>
                
                <h1 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                <h2 class="text-primary fw-bold mb-4">Rp <?= number_format($product['price'], 0, ',', '.') ?></h2>

                <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                    <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                        <i class="bi bi-shop fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Penjual</small>
                        <span class="fw-semibold"><?= htmlspecialchars($product['seller_name']) ?></span>
                        <span class="mx-2">•</span>
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($product['location']) ?></small>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold">Deskripsi</h5>
                    <p class="text-muted" style="white-space: pre-line;"><?= htmlspecialchars($product['description']) ?></p>
                </div>

                <div class="d-none d-md-flex gap-2">
                    <?php if ($product['status'] === 'available'): ?>
                        <a href="<?= $wa_link ?>" target="_blank" class="btn btn-success flex-grow-1 btn-lg">
                            <i class="bi bi-whatsapp me-2"></i>Hubungi Penjual
                        </a>

                        <button onclick="cart.addToCart(<?= $product['id'] ?>)" class="btn btn-outline-danger btn-lg px-4" title="Simpan ke Favorit">
                            <i class="bi bi-heart"></i> Suka
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary flex-grow-1 btn-lg" disabled>
                            Terjual
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sticky-cart d-md-none border-top p-3 bg-white fixed-bottom shadow-lg d-flex justify-content-between align-items-center">
    <div>
        <small class="text-muted d-block">Harga</small>
        <span class="fw-bold text-primary fs-5">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
    </div>
    <div class="d-flex gap-2">
        <?php if ($product['status'] === 'available'): ?>
            <button onclick="cart.addToCart(<?= $product['id'] ?>)" class="btn btn-outline-danger">
                <i class="bi bi-heart"></i>
            </button>
            <a href="<?= $wa_link ?>" target="_blank" class="btn btn-success">
                Beli (WA)
            </a>
        <?php else: ?>
            <button class="btn btn-secondary" disabled>Terjual</button>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>