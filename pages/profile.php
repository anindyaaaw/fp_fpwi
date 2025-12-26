<?php
require_once '../config/database.php';
require_once '../config/functions.php'; // Wajib ada untuk upload foto
require_login();

$page_title = 'Profil Saya';
$success = '';
$error = '';

// Ambil data user terbaru
$user_id = $_SESSION['user_id'];
$user = db_query("SELECT * FROM users WHERE id=?", [$user_id], "i")->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $password = $_POST['password'];
    
    // Default gambar QRIS lama
    $qris_url = $user['qris_image'];

    // Cek apakah ada upload QRIS baru
    if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] === 0) {
        $uploaded_url = uploadToCloudinary($_FILES['qris_image']['tmp_name']);
        if ($uploaded_url) {
            $qris_url = $uploaded_url;
        } else {
            $error = "Gagal upload gambar QRIS.";
        }
    }

    if (!$error) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db_query("UPDATE users SET name=?, phone=?, password=?, qris_image=? WHERE id=?", 
                [$name, $phone, $hash, $qris_url, $user_id], "ssssi");
        } else {
            db_query("UPDATE users SET name=?, phone=?, qris_image=? WHERE id=?", 
                [$name, $phone, $qris_url, $user_id], "sssi");
        }
        
        $_SESSION['user_name'] = $name;
        $success = 'Profil berhasil diperbarui!';
        // Refresh data user agar tampilan langsung berubah
        $user = db_query("SELECT * FROM users WHERE id=?", [$user_id], "i")->get_result()->fetch_assoc();
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow rounded-4 p-4">
                <h3 class="fw-bold mb-4">Edit Profil</h3>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. Telepon</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">QRIS Toko (Untuk Menerima Pembayaran)</label>
                        <input type="file" name="qris_image" class="form-control" accept="image/*">
                        <small class="text-muted">Upload gambar QR Code dari e-wallet/bank Anda.</small>
                        
                        <?php if(!empty($user['qris_image'])): ?>
                            <div class="mt-2 p-2 border rounded bg-light text-center">
                                <small class="d-block mb-1">QRIS Saat Ini:</small>
                                <img src="<?= $user['qris_image'] ?>" alt="QRIS Saya" style="max-width: 150px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password Baru (Kosongkan jika tidak ubah)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••">
                    </div>
                    
                    <button type="submit" class="btn btn-primary-modern w-100">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>