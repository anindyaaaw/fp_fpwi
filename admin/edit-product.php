<?php
require_once '../config/database.php';
require_role('seller');

$id = (int)$_GET['id'];
$seller_id = $_SESSION['user_id'];

// Ambil data lama & pastikan milik seller yg login
$stmt = db_query("SELECT * FROM products WHERE id = ? AND seller_id = ?", [$id, $seller_id], "ii");
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: manage-products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $price = (int)$_POST['price'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];
    $location = htmlspecialchars($_POST['location']);
    $description = htmlspecialchars($_POST['description']);
    $status = $_POST['status'];
    
    // Default pakai gambar lama
    $db_image_path = $product['image'];
    
    // Jika ada upload baru
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../assets/images/products/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $db_image_path = "/assets/images/products/" . $new_filename;
        }
    }
    
    $update_query = "UPDATE products SET name=?, price=?, category=?, condition_item=?, location=?, description=?, status=?, image=? WHERE id=? AND seller_id=?";
    
    db_query($update_query, 
        [$name, $price, $category, $condition, $location, $description, $status, $db_image_path, $id, $seller_id], 
        "sissssssii");

    header('Location: ' . BASE_URL . '/admin/manage-products.php');
    exit;
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow rounded-4 p-4">
                <h3 class="fw-bold mb-4">Edit Produk</h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="available" <?= $product['status'] == 'available' ? 'selected' : '' ?>>Tersedia</option>
                                <option value="sold" <?= $product['status'] == 'sold' ? 'selected' : '' ?>>Terjual</option>
                                <option value="pending" <?= $product['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="Fashion" <?= $product['category'] == 'Fashion' ? 'selected' : '' ?>>Fashion</option>
                                <option value="Elektronik" <?= $product['category'] == 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                                <option value="Kendaraan" <?= $product['category'] == 'Kendaraan' ? 'selected' : '' ?>>Kendaraan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kondisi</label>
                            <select name="condition" class="form-select">
                                <option value="New" <?= $product['condition_item'] == 'New' ? 'selected' : '' ?>>New</option>
                                <option value="Like New" <?= $product['condition_item'] == 'Like New' ? 'selected' : '' ?>>Like New</option>
                                <option value="Good" <?= $product['condition_item'] == 'Good' ? 'selected' : '' ?>>Good</option>
                                <option value="Fair" <?= $product['condition_item'] == 'Fair' ? 'selected' : '' ?>>Fair</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($product['location']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Ganti Foto (Opsional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="mt-2">
                            <small class="text-muted d-block mb-1">Foto saat ini:</small>
                            <img src="<?= htmlspecialchars($product['image']) ?>" width="80" class="rounded border">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-modern w-100">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>