<?php
require_once '../config/database.php';
require_role('seller');

$page_title = 'Kelola Produk - Thrift & Swap';
$user_id = $_SESSION['user_id'];

// Ambil semua produk milik seller ini
$stmt = db_query("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC", [$user_id], "i");
$products = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Kelola Produk</h2>
        <a href="add-product.php" class="btn btn-primary-modern">
            <i class="bi bi-plus-lg me-2"></i>Tambah Produk
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="100">Gambar</th>
                        <th>Info Produk</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while($p = $products->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <img src="<?= htmlspecialchars($p['image']) ?>" 
                                     class="rounded-3 object-fit-cover" 
                                     style="width: 60px; height: 60px;" alt="img">
                            </td>
                            <td>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($p['name']) ?></h6>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category']) ?></span>
                            </td>
                            <td class="fw-semibold">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td>
                                <?php if($p['status'] == 'available'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Tersedia</span>
                                <?php elseif($p['status'] == 'sold'): ?>
                                    <span class="badge bg-secondary rounded-pill px-3">Terjual</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="edit-product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="#" onclick="confirmDelete(<?= $p['id'] ?>)" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-box-seam fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Belum ada produk yang dijual.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Produk?',
        text: "Produk yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete-product.php?id=${id}`;
        }
    })
}
</script>

<?php include '../includes/footer.php'; ?>