<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base path
$base_path = '.';
if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    $base_path = '..';
} elseif (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/auth/') !== false) {
    $base_path = '..';
}

// --- LOGIKA DETEKSI HALAMAN AKTIF ---
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Thrift & Swap - Platform Jual Beli Barang Preloved' ?></title>

    <link rel="icon" type="image/png" href="<?= $base_path ?>/assets/images/logo/favicon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_path ?>/assets/css/style.css">

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= $base_path ?>/index.php">
            <img src="<?= $base_path ?>/assets/images/logo/logo.png" alt="Logo" 
                style="height: 50px; width: auto; object-fit: contain; margin-right: 10px;">
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" 
                       href="<?= $base_path ?>/index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'products.php' || $current_page == 'product-detail.php') ? 'active' : '' ?>" 
                       href="<?= $base_path ?>/pages/products.php">Produk</a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'seller' || $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'dashboard.php' || $current_page == 'manage-products.php' || $current_page == 'add-product.php') ? 'active' : '' ?>" 
                               href="<?= $base_path ?>/admin/dashboard.php">Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'my-orders.php' || $current_page == 'order-detail.php') ? 'active' : '' ?>" 
                           href="<?= $base_path ?>/pages/my-orders.php">Pesanan Saya</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="search-modern me-3 d-none d-lg-block">
                <i class="bi bi-search"></i>
                <input type="text" id="search-input" class="form-control" placeholder="Cari produk...">
            </div>
            
            <ul class="navbar-nav">
                <li class="nav-item me-2">
                    <button id="theme-toggle" class="theme-toggle btn">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item me-2">
                        <button id="cart-btn" class="btn btn-outline-danger position-relative rounded-circle">
                            <i class="bi bi-heart"></i> <span id="cart-count" class="cart-badge bg-danger" style="display: none;">0</span>
                        </button>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= $base_path ?>/pages/profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base_path ?>/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= $base_path ?>/auth/login.php" class="btn btn-outline-dark me-2">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base_path ?>/auth/register.php" class="btn btn-primary-modern">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['user_id'])): ?>
<div id="cart-overlay" class="cart-overlay"></div>
<div id="cart-drawer" class="cart-drawer">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-danger"><i class="bi bi-heart-fill me-2"></i>Disukai</h5>
        <button id="close-cart" class="btn btn-sm btn-outline-secondary rounded-circle">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <div id="cart-items" class="flex-grow-1 overflow-auto">
        </div>
    
    <div class="p-4 border-top">
        <a href="<?= $base_path ?>/pages/cart.php" class="btn btn-danger w-100 rounded-pill">
            Lihat Semua Favorit
        </a>
    </div>
</div>
<?php endif; ?>