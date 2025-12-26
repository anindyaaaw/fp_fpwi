<?php
require_once 'config/database.php';
$page_title = 'Home - Thrift & Swap';

// --- 1. SETUP PETA IKON KATEGORI (BAGIAN BARU DISINI) ---
// Array ini memasangkan Nama Kategori dari Database -> Nama Ikon Bootstrap
// Silakan tambah/ubah jika ada kategori lain.
$category_icons = [
    'Fashion'    => 'bi-handbag',      // Ikon tas untuk fashion
    'Elektronik' => 'bi-laptop',       // Ikon laptop untuk elektronik
    'Kendaraan'  => 'bi-car-front',    // Ikon mobil (SESUAI PERMINTAAN)
    'Buku'       => 'bi-book',         // (Jaga-jaga jika masih ada)
    'Hobi'       => 'bi-controller',   // (Jaga-jaga jika masih ada)
    'Lainnya'    => 'bi-grid-fill',    // Ikon kotak-kotak untuk lainnya
    // Ikon cadangan jika nama kategori tidak dikenali
    'default'    => 'bi-tag-fill'
];

// --- QUERY DATABASE ---
// 1. QUERY SLIDER: Ambil 5 produk acak
$slider_query = "SELECT * FROM products WHERE status = 'available' ORDER BY RAND() LIMIT 5";
$slider_result = $conn->query($slider_query);

// 2. QUERY KATEGORI
$cat_query = "SELECT DISTINCT category FROM products WHERE status = 'available' LIMIT 6";
$categories = $conn->query($cat_query);

// 3. QUERY PRODUK TERBARU
$query = "SELECT p.*, u.name as seller_name 
          FROM products p 
          JOIN users u ON p.seller_id = u.id 
          WHERE p.status = 'available' 
          ORDER BY p.created_at DESC 
          LIMIT 6";
$result = $conn->query($query);

include 'includes/header.php';
?>

<div class="hero-modern">
    <div class="container">
        <h1 class="fade-in-up">Jual Beli Barang Preloved<br><span class="text-gradient">Berkualitas</span></h1>
        <p class="fade-in-up">Hemat budget, ramah lingkungan, dan temukan barang unik favorit kamu!</p>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/pages/products.php" class="btn btn-light btn-lg rounded-pill px-5">
                Mulai Belanja <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>

<div class="container my-5">
    
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Kategori Populer</h2>
        </div>
        
        <div class="row g-3">
            <?php while($cat = $categories->fetch_assoc()): 
                $cat_name = $cat['category'];
                // Cek apakah nama kategori ini ada di daftar ikon kita?
                // Jika ada, pakai ikonnya. Jika tidak, pakai ikon default.
                $icon_class = array_key_exists($cat_name, $category_icons) ? $category_icons[$cat_name] : $category_icons['default'];
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= BASE_URL ?>/pages/products.php?category=<?= urlencode($cat_name) ?>" 
                   class="text-decoration-none">
                    <div class="card border-0 shadow-sm hover-lift text-center p-4 h-100 d-flex flex-column justify-content-center align-items-center" 
                         style="transition: all 0.3s; cursor: pointer;">
                        <i class="bi <?= $icon_class ?> fs-1 text-primary mb-2"></i>
                        <h6 class="mb-0"><?= htmlspecialchars($cat_name) ?></h6>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <section class="mb-5">
        <div id="productCarousel" class="carousel slide carousel-fade shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php for($i = 0; $i < $slider_result->num_rows; $i++): ?>
                    <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?= $i ?>" 
                            class="<?= $i === 0 ? 'active' : '' ?>" aria-current="true"></button>
                <?php endfor; ?>
            </div>

            <div class="carousel-inner">
                <?php 
                $active = true;
                if ($slider_result->num_rows > 0):
                    while($slide = $slider_result->fetch_assoc()): 
                ?>
                    <div class="carousel-item <?= $active ? 'active' : '' ?>" data-bs-interval="4000">
                        <div class="slider-img-wrapper">
                            <img src="<?= htmlspecialchars($slide['image']) ?>" class="d-block w-100 slider-img" alt="<?= htmlspecialchars($slide['name']) ?>">
                            <div class="slider-overlay"></div>
                        </div>
                        <div class="carousel-caption d-none d-md-block text-start p-5">
                            <span class="badge bg-warning text-dark mb-2">Rekomendasi</span>
                            <h2 class="fw-bold display-5"><?= htmlspecialchars($slide['name']) ?></h2>
                            <p class="fs-4">Rp <?= number_format($slide['price'], 0, ',', '.') ?></p>
                            <a href="<?= BASE_URL ?>/pages/product-detail.php?id=<?= $slide['id'] ?>" class="btn btn-light rounded-pill px-4 mt-2">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php 
                    $active = false; 
                    endwhile; 
                else:
                ?>
                    <div class="carousel-item active">
                        <div class="slider-img-wrapper bg-secondary d-flex align-items-center justify-content-center">
                            <h3 class="text-white">Belum ada produk untuk ditampilkan</h3>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>
    
    <section>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Produk Terbaru</h2>
            <a href="<?= BASE_URL ?>/pages/products.php" class="btn btn-outline-dark rounded-pill">
                Lihat Semua <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="bento-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($product = $result->fetch_assoc()): ?>
                <div class="product-card fade-in-up" onclick="window.location.href='<?= BASE_URL ?>/pages/product-detail.php?id=<?= $product['id'] ?>'">
                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-card-img">
                    
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
                    <div class="py-5 bg-light rounded-4">
                        <i class="bi bi-box-seam fs-1 text-muted"></i>
                        <p class="mt-3 text-muted">Belum ada produk tersedia</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="my-5 py-5 bg-light rounded-4">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Mengapa Pilih Thrift & Swap?</h2>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-shield-check fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold">Terpercaya</h5>
                    <p class="text-muted">Sistem rating dan review untuk kepercayaan pembeli</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-recycle fs-1 text-success"></i>
                    </div>
                    <h5 class="fw-bold">Ramah Lingkungan</h5>
                    <p class="text-muted">Kurangi limbah dengan membeli barang preloved</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-cash-coin fs-1 text-warning"></i>
                    </div>
                    <h5 class="fw-bold">Hemat Budget</h5>
                    <p class="text-muted">Dapatkan barang berkualitas dengan harga terjangkau</p>
                </div>
            </div>
        </div>
    </section>
    
</div>

<style>
.slider-img-wrapper {
    position: relative;
    height: 400px;
    width: 100%;
}
.slider-img {
    height: 100%;
    object-fit: cover;
    object-position: center;
}
.slider-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 60%, rgba(0,0,0,0) 100%);
}
.carousel-caption {
    bottom: 0;
    left: 0;
    right: 0;
    padding-bottom: 3rem;
    padding-left: 5rem;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
@media (max-width: 768px) {
    .slider-img-wrapper { height: 250px; }
    .carousel-caption { padding-left: 2rem; padding-bottom: 1rem; }
    .carousel-caption h2 { font-size: 1.5rem; }
}
</style>

<?php include 'includes/footer.php'; ?>