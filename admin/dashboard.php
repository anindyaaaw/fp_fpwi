<?php
require_once '../config/database.php';
require_role('seller');

$page_title = 'Dashboard Seller - Thrift & Swap';
$user_id = $_SESSION['user_id'];

// --- 1. STATISTIK RINGKAS (KARTU ATAS) ---
// Total Produk
$stmt = db_query("SELECT COUNT(*) as total FROM products WHERE seller_id = ?", [$user_id], "i");
$total_products = $stmt->get_result()->fetch_assoc()['total'];

// Total Pendapatan
$stmt = db_query("SELECT SUM(price * quantity) as earnings FROM order_items WHERE seller_id = ?", [$user_id], "i");
$earnings = $stmt->get_result()->fetch_assoc()['earnings'] ?? 0;

// Produk Terjual
$stmt = db_query("SELECT SUM(quantity) as sold FROM order_items WHERE seller_id = ?", [$user_id], "i");
$items_sold = $stmt->get_result()->fetch_assoc()['sold'] ?? 0;


// --- 2. DATA UNTUK GRAFIK (7 HARI TERAKHIR) ---
// Query ini mengelompokkan penjualan berdasarkan tanggal
$chart_query = "SELECT DATE(o.created_at) as sale_date, SUM(oi.price * oi.quantity) as daily_total 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.seller_id = ? 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(o.created_at)
                ORDER BY sale_date ASC";

$chart_stmt = db_query($chart_query, [$user_id], "i");
$chart_result = $chart_stmt->get_result();

$dates = [];
$totals = [];

while($row = $chart_result->fetch_assoc()) {
    // Format tanggal jadi "12 Dec"
    $dates[] = date('d M', strtotime($row['sale_date'])); 
    $totals[] = (int)$row['daily_total'];
}

// Konversi ke format JSON agar bisa dibaca Javascript
$json_dates = json_encode($dates);
$json_totals = json_encode($totals);


// --- 3. TRANSAKSI TERBARU (TABEL BAWAH) ---
$query_recent = "SELECT oi.*, p.name as product_name, o.created_at, o.buyer_id, u.name as buyer_name 
                 FROM order_items oi 
                 JOIN products p ON oi.product_id = p.id 
                 JOIN orders o ON oi.order_id = o.id 
                 JOIN users u ON o.buyer_id = u.id 
                 WHERE oi.seller_id = ? 
                 ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = db_query($query_recent, [$user_id], "i")->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Dashboard Penjualan</h2>
            <p class="text-muted">Halo, <?= htmlspecialchars($_SESSION['user_name']) ?>! Berikut performa toko kamu.</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/add-product.php" class="btn btn-primary-modern shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Tambah Produk
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Pendapatan</p>
                            <h3 class="fw-bold mb-0">Rp <?= number_format($earnings, 0, ',', '.') ?></h3>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">Produk Aktif</p>
                            <h3 class="fw-bold mb-0"><?= $total_products ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-primary">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">Barang Terjual</p>
                            <h3 class="fw-bold mb-0"><?= $items_sold ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-success">
                            <i class="bi bi-bag-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Grafik Pendapatan (7 Hari Terakhir)</h5>
                    <div style="height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white p-4 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Penjualan Terakhir</h5>
                <a href="<?= BASE_URL ?>/admin/manage-products.php" class="btn btn-sm btn-outline-dark rounded-pill">Kelola Produk</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Produk</th>
                        <th>Pembeli</th>
                        <th>Tanggal</th>
                        <th>Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold"><?= htmlspecialchars($order['product_name']) ?></span>
                                <small class="text-muted d-block">Qty: <?= $order['quantity'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            <td>Rp <?= number_format($order['price'] * $order['quantity'], 0, ',', '.') ?></td>
                            <td><span class="badge bg-success rounded-pill">Lunas</span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada penjualan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Ambil data dari PHP
const dates = <?= $json_dates ?>;
const totals = <?= $json_totals ?>;

// Setup Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line', // Jenis grafik: Line, Bar, Pie, dll.
    data: {
        labels: dates.length > 0 ? dates : ['No Data'],
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: totals.length > 0 ? totals : [0],
            backgroundColor: 'rgba(59, 130, 246, 0.2)', // Warna area (Transparan)
            borderColor: '#3B82F6', // Warna garis (Biru Accent)
            borderWidth: 2,
            tension: 0.4, // Membuat garis melengkung halus
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#3B82F6',
            pointBorderWidth: 2,
            pointRadius: 4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false // Sembunyikan legenda agar bersih
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            // Format Rupiah di Tooltip
                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [5, 5], // Garis putus-putus
                    color: '#e5e7eb'
                },
                ticks: {
                    // Format Rupiah di Sumbu Y
                    callback: function(value) {
                        return 'Rp ' + (value / 1000) + 'k'; 
                    }
                }
            },
            x: {
                grid: {
                    display: false // Hilangkan garis vertikal
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>