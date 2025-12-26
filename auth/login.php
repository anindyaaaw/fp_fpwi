<?php
require_once '../config/database.php';
$page_title = 'Login - Thrift & Swap';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $stmt = db_query("SELECT id, name, email, password, role FROM users WHERE email = ?", [$email], "s");
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin' || $user['role'] === 'seller') {
                header('Location: ../index.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Email atau password salah!';
    }
    
    $stmt->close();
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-bag-heart-fill fs-1 text-primary"></i>
                        <h2 class="fw-bold mt-3">Selamat Datang Kembali</h2>
                        <p class="text-muted">Login untuk melanjutkan</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" 
                                   placeholder="nama@email.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg" 
                                   placeholder="••••••••" required>
                        </div>
                        
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary-modern btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-muted">Belum punya akun? 
                                <a href="register.php" class="text-primary fw-semibold">Daftar Sekarang</a>
                            </p>
                        </div>
                    </form>
                    
                    <!-- Demo Accounts Info -->
                    <div class="mt-4 p-3 bg-light rounded-3">
                        <small class="text-muted d-block mb-2"><strong>Demo Accounts:</strong></small>
                        <small class="d-block text-muted">👤 Admin: admin@thriftswap.com / admin123</small>
                        <small class="d-block text-muted">🛍️ Seller: seller1@example.com / admin123</small>
                        <small class="d-block text-muted">🛒 Buyer: buyer1@example.com / admin123</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>