<?php
require_once '../config/database.php';
require_login();

$page_title = 'Checkout - Thrift & Swap';

// Get Cart Items + Info Penjual (Termasuk QRIS Image)
$user_id = $_SESSION['user_id'];
// Kita JOIN ke tabel Users (u) berdasarkan seller_id produk untuk ambil qris_image
$query = "SELECT c.*, p.name, p.price, p.image, u.name as seller_name, u.qris_image 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          JOIN users u ON p.seller_id = u.id 
          WHERE c.user_id = ?";
$cart_items = db_query($query, [$user_id], "i")->get_result();

if ($cart_items->num_rows === 0) {
    header('Location: products.php');
    exit;
}

// Simpan hasil query ke array agar bisa di-loop 2 kali (sekali untuk QRIS, sekali untuk List)
$items_data = [];
while ($row = $cart_items->fetch_assoc()) {
    $items_data[] = $row;
}

// Ambil QRIS dari item pertama (Asumsi: User checkout per toko, atau simplifikasi)
$seller_qris = $items_data[0]['qris_image'];
$seller_name = $items_data[0]['seller_name'];

$total = 0;
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-7">
            <h3 class="fw-bold mb-4">Informasi Pengiriman</h3>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <form id="checkout-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea class="form-control" name="address" rows="3" placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kota</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kode Pos</label>
                            <input type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan (Opsional)</label>
                        <input type="text" class="form-control" placeholder="Pesan untuk penjual">
                    </div>
                </form>
            </div>

            <h3 class="fw-bold mb-4">Pembayaran (QRIS Only)</h3>
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <?php if (!empty($seller_qris)): ?>
                    <div class="alert alert-info d-inline-block px-4 py-2 rounded-pill mb-3">
                        <i class="bi bi-shop me-2"></i>QRIS Toko: <strong><?= htmlspecialchars($seller_name) ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-center mb-3">
                        <div class="p-2 border rounded-4 bg-white shadow-sm" style="width: 250px; height: 250px;">
                            <img src="<?= $seller_qris ?>" class="w-100 h-100 object-fit-contain" alt="Scan QRIS">
                        </div>
                    </div>
                    
                    <p class="text-muted mb-0">Silakan scan QRIS di atas menggunakan aplikasi E-Wallet/Banking Anda.</p>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Penjual belum mengupload QRIS.</strong><br>
                        Silakan hubungi penjual atau coba metode lain nanti.
                    </div>
                <?php endif; ?>
                
                <input type="hidden" name="payment" value="qris">
            </div>
        </div>

        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="card border-0 bg-light rounded-4 p-4 sticky-top" style="top: 100px;">
                <h4 class="fw-bold mb-4">Ringkasan Pesanan</h4>
                
                <div class="cart-summary mb-4" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach($items_data as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <div class="d-flex gap-3 mb-3">
                        <img src="<?= $item['image'] ?>" class="rounded-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-truncate" style="max-width: 200px;"><?= htmlspecialchars($item['name']) ?></h6>
                            <small class="text-muted"><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></small>
                        </div>
                        <span class="fw-semibold">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-semibold">Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-muted">Biaya Layanan</span>
                    <span class="fw-semibold text-success">Gratis</span>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <span class="h5 fw-bold">Total Bayar</span>
                    <span class="h5 fw-bold text-primary">Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>

                <button onclick="processPayment()" class="btn btn-primary-modern w-100 btn-lg shadow-sm" 
                    <?= empty($seller_qris) ? 'disabled' : '' ?>>
                    <i class="bi bi-check-circle-fill me-2"></i>Sudah Bayar
                </button>
                <small class="text-muted text-center d-block mt-2">Klik tombol ini setelah melakukan pembayaran.</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
async function processPayment() {
    const form = document.getElementById('checkout-form');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const address = form.querySelector('[name="address"]').value;

    // 1. Show Loading (Simulasi Verifikasi)
    Swal.fire({
        title: 'Memverifikasi Pembayaran...',
        html: 'Mohon tunggu, sedang mengecek transaksi QRIS.',
        timer: 3000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(async (result) => {
        // 2. Kirim Data ke Backend
        try {
            // Gunakan BASE_URL di sini agar aman
            const response = await fetch('<?= BASE_URL ?>/ajax/process-checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ address: address })
            });
            
            const data = await response.json();

            if (data.success) {
                // 3. Show Success Popup
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Diterima!',
                    text: 'Terima kasih, pesanan Anda akan segera diproses penjual.',
                    confirmButtonText: 'Lihat Pesanan Saya',
                    confirmButtonColor: '#000000'
                }).then(() => {
                    window.location.href = '<?= BASE_URL ?>/pages/my-orders.php';
                });
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message || 'Terjadi kesalahan', 'error');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>