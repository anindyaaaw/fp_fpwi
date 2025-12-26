<?php
require_once '../config/database.php';
$page_title = 'Daftar Akun - Thrift & Swap';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    
    // PERUBAHAN DISINI: Role otomatis diset menjadi 'seller'
    $role = 'seller'; 
    
    // Validation
    if (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } else {
        // Check if email exists
        $check_stmt = db_query("SELECT id FROM users WHERE email = ?", [$email], "s");
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Query tetap sama
            $stmt = db_query(
                "INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)",
                [$name, $email, $hashed_password, $role, $phone],
                "sssss"
            );
            
            if ($stmt->affected_rows > 0) {
                $success = 'Registrasi berhasil! Silakan login.';
                header('refresh:2;url=login.php');
            } else {
                $error = 'Registrasi gagal! Silakan coba lagi.';
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
                        <h2 class="fw-bold mt-3">Buat Akun Baru</h2>
                        <p class="text-muted">Mulai berjualan & belanja sekarang</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="tel" name="phone" class="form-control" placeholder="08123456789" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Ketik ulang password" required>
                        </div>
                        
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary-modern btn-lg">
                                <i class="bi bi-person-check me-2"></i>Daftar Sekarang
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-muted">Sudah punya akun? 
                                <a href="login.php" class="text-primary fw-semibold">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>