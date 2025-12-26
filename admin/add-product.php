<?php
require_once '../config/database.php';
require_once '../config/functions.php'; // Panggil fungsi upload
require_role('seller');

$page_title = 'Tambah Produk - Thrift & Swap';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $price = (int)$_POST['price'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];
    $location = htmlspecialchars($_POST['location']);
    $description = htmlspecialchars($_POST['description']);
    $seller_id = $_SESSION['user_id'];
    
    // Validasi Gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_tmp = $_FILES['image']['tmp_name'];
        
        // UPLOAD KE CLOUDINARY
        $image_url = uploadToCloudinary($file_tmp);
        
        if ($image_url) {
            // Simpan ke Database (Link HTTPS dari Cloudinary)
            $stmt = db_query(
                "INSERT INTO products (name, description, price, image, category, condition_item, location, seller_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')",
                [$name, $description, $price, $image_url, $category, $condition, $location, $seller_id],
                "ssissssi"
            );
            
            if ($stmt) {
                header('Location: ' . BASE_URL . '/admin/manage-products.php');
                exit;
            } else {
                $error = "Gagal menyimpan data ke database.";
            }
        } else {
            $error = "Gagal upload gambar ke Cloudinary. Cek koneksi internet.";
        }
    } else {
        $error = "Wajib upload foto produk.";
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg rounded-4 p-4">
                <h3 class="fw-bold mb-4">Jual Barang Baru</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Fashion">Fashion</option>
                                <option value="Elektronik">Elektronik</option>
                                <option value="Kendaraan">Kendaraan</option>
                                <!-- <option value="Lainnya">Lainnya</option> -->
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" placeholder="Contoh: 150000" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kondisi Barang</label>
                            <select name="condition" class="form-select" required>
                                <option value="New">Baru (New)</option>
                                <option value="Like New">Seperti Baru (Like New)</option>
                                <option value="Good">Baik (Good)</option>
                                <option value="Fair">Layak Pakai (Fair)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Lokasi Barang (Kota)</label>
                            <input type="text" name="location" class="form-control" placeholder="Contoh: Jakarta Selatan" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Foto Produk</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Foto akan disimpan di Cloud (Aman untuk Hosting).</small>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-modern btn-lg">
                            <i class="bi bi-upload me-2"></i>Tayangkan Produk
                        </button>
                        <a href="<?= BASE_URL ?>/admin/manage-products.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>