<?php
require_once '../config/database.php';
require_login();
// Ubah Title
$page_title = 'Barang Disukai - Thrift & Swap';

// Ambil data (Query tetap sama, mengambil dari tabel cart)
$user_id = $_SESSION['user_id'];
$query = "SELECT c.*, p.name, p.price, p.image, p.category, p.seller_id, p.status 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ? ORDER BY c.created_at DESC";
$cart_items = db_query($query, [$user_id], "i")->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Barang Disukai ❤️</h2>

    <?php if ($cart_items->num_rows > 0): ?>
    <div class="row g-4">
        <?php while($item = $cart_items->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="position-relative">
                    <img src="<?= htmlspecialchars($item['image']) ?>" 
                         class="card-img-top object-fit-cover" 
                         style="height: 250px;" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                    
                    <button onclick="removeCartPage(<?= $item['id'] ?>)" 
                            class="btn btn-light rounded-circle position-absolute top-0 end-0 m-3 shadow-sm text-danger"
                            title="Hapus dari Favorit">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="card-body p-4 d-flex flex-column">
                    <div class="mb-2">
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($item['category']) ?></span>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                    <h5 class="text-primary fw-bold mb-3">Rp <?= number_format($item['price'], 0, ',', '.') ?></h5>

                    <div class="mt-auto d-grid gap-2">
                        <?php if($item['status'] == 'available'): ?>
                            <a href="checkout.php?id=<?= $item['product_id'] ?>" class="btn btn-outline-primary rounded-pill">
                                Checkout
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary rounded-pill" disabled>Barang Terjual</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-heart-break text-muted" style="font-size: 5rem;"></i>
        </div>
        <h3>Belum ada barang yang disukai</h3>
        <p class="text-muted">Simpan barang impianmu disini agar tidak hilang.</p>
        <a href="products.php" class="btn btn-primary-modern mt-3 px-5">
            Cari Barang
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
// Fungsi untuk menghapus item (Logic JS tetap pakai 'cart' karena backendnya sama)
async function removeCartPage(id) {
    if(confirm('Hapus barang ini dari daftar disukai?')) {
        await cart.removeItem(id);
        window.location.reload();
    }
}
</script>

<?php include '../includes/footer.php'; ?>