<?php
require_once '../config/database.php';
$page_title = 'Produk - Thrift & Swap';

// Filter Logic
$where_clause = "WHERE status = 'available'";
$params = [];
$types = "";

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clause .= " AND category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_clause .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Get Products
$query = "SELECT * FROM products $where_clause ORDER BY created_at DESC";
$stmt = db_query($query, $params, $types);
$result = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0">
                <?= isset($_GET['category']) ? 'Kategori: ' . htmlspecialchars($_GET['category']) : 'Semua Produk' ?>
            </h2>
            <p class="text-muted mb-0"><?= $result->num_rows ?> produk ditemukan</p>
        </div>
        
        <div class="d-flex gap-2 overflow-auto w-100 w-md-auto pb-2">
            <a href="products.php" class="btn btn-outline-dark rounded-pill whitespace-nowrap <?= !isset($_GET['category']) ? 'active' : '' ?>">Semua</a>
            <a href="products.php?category=Fashion" class="btn btn-outline-dark rounded-pill whitespace-nowrap <?= (isset($_GET['category']) && $_GET['category'] == 'Fashion') ? 'active' : '' ?>">Fashion</a>
            <a href="products.php?category=Elektronik" class="btn btn-outline-dark rounded-pill whitespace-nowrap <?= (isset($_GET['category']) && $_GET['category'] == 'Elektronik') ? 'active' : '' ?>">Elektronik</a>
            <a href="products.php?category=Kendaraan" class="btn btn-outline-dark rounded-pill whitespace-nowrap <?= (isset($_GET['category']) && $_GET['category'] == 'Kendaraan') ? 'active' : '' ?>">Kendaraan</a>
            <!-- <a href="products.php?category=Hobi" class="btn btn-outline-dark rounded-pill whitespace-nowrap <?= (isset($_GET['category']) && $_GET['category'] == 'Hobi') ? 'active' : '' ?>">Hobi</a> -->
        </div>
    </div>

    <div class="bento-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($product = $result->fetch_assoc()): ?>
            <div class="product-card fade-in-up" onclick="window.location.href='product-detail.php?id=<?= $product['id'] ?>'">
                <img data-src="<?= htmlspecialchars($product['image']) ?>" 
                     src="<?= '../assets/images/placeholder.jpg' // Placeholder saat loading ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="product-card-img skeleton">
                
                <div class="product-card-body">
                    <div class="product-category"><?= htmlspecialchars($product['category']) ?></div>
                    <h5 class="product-title"><?= htmlspecialchars($product['name']) ?></h5>
                    <div class="product-price">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="product-location text-muted small">
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($product['location']) ?>
                        </span>
                        <span class="badge-condition condition-<?= str_replace(' ', '-', strtolower($product['condition_item'])) ?>">
                            <?= htmlspecialchars($product['condition_item']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 w-100">
                <div class="py-5">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">Produk tidak ditemukan</h5>
                    <a href="products.php" class="btn btn-primary-modern mt-3">Reset Filter</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>