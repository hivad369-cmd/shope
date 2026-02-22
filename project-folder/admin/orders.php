<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

$message = $message_type = '';

// تغییر وضعیت سفارش
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    try {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$new_status, $order_id]);
        $message = 'وضعیت به‌روز شد';
        $message_type = 'success';
    } catch(PDOException $e) {
        $message = 'خطا در بروزرسانی';
        $message_type = 'error';
    }
}

// پارامترهای جستجو
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// ساخت کوئری
$sql = "SELECT o.*, u.full_name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (o.order_code LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($status_filter) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    die("خطا در دریافت سفارشات");
}

// محاسبه آمارها
$total_orders = count($orders);
$pending_count = count(array_filter($orders, fn($o) => $o['status'] == 'pending'));
$processing_count = count(array_filter($orders, fn($o) => $o['status'] == 'processing'));
$completed_count = count(array_filter($orders, fn($o) => $o['status'] == 'completed'));
$cancelled_count = count(array_filter($orders, fn($o) => $o['status'] == 'cancelled'));
$total_amount = array_sum(array_column($orders, 'total_amount'));

$pageTitle = "مدیریت سفارشات";
require_once '../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <!-- هدر -->
        <div class="page-header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
            <div class="header-title">
                <h1><i class="fas fa-shopping-cart"></i> مدیریت سفارشات</h1>
                <p>پیگیری سفارشات مشتریان</p>
            </div>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> چاپ
            </button>
        </div>

        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check' : 'exclamation'; ?>"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- فیلترها -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="شماره سفارش، نام یا ایمیل..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <select name="status" class="form-select">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>در حال پردازش</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>تکمیل شده</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>لغو شده</option>
                    </select>
                    <div class="filter-group">
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" placeholder="از تاریخ">
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" placeholder="تا تاریخ">
                    </div>
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-filter"></i> فیلتر
                    </button>
                    <a href="orders.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> پاک کردن
                    </a>
                </div>
            </form>
        </div>

        <!-- آمار -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3><?php echo $pending_count; ?></h3>
                    <p>در انتظار</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h3><?php echo $processing_count; ?></h3>
                    <p>در حال پردازش</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3><?php echo $completed_count; ?></h3>
                    <p>تکمیل شده</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <h3><?php echo $cancelled_count; ?></h3>
                    <p>لغو شده</p>
                </div>
            </div>
            <div class="stat-card stat-total">
                <div class="stat-icon bg-dark">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h3><?php echo number_format($total_amount); ?></h3>
                    <p>جمع کل تومان</p>
                </div>
            </div>
        </div>

        <!-- جدول سفارشات -->
        <div class="card">
            <div class="card-header">
                <h3>لیست سفارشات (<?php echo $total_orders; ?>)</h3>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchTable" placeholder="جستجوی سریع...">
                </div>
            </div>
            
            <div class="card-body">
                <?php if($total_orders > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>سفارش</th>
                                <th>مشتری</th>
                                <th>مبلغ</th>
                                <th>تاریخ</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTable">
                            <?php foreach($orders as $order): ?>
                            <tr data-status="<?php echo $order['status']; ?>">
                                <td>
                                    <div class="order-info">
                                        <h4 class="order-code">#<?php echo $order['order_code']; ?></h4>
                                        <span class="badge-payment <?php echo $order['payment_status']; ?>">
                                            <?php echo $order['payment_status'] == 'paid' ? 'پرداخت شده' : ($order['payment_status'] == 'pending' ? 'در انتظار' : 'ناموفق'); ?>
                                        </span>
                                        <small class="text-muted"><?php echo $order['payment_method']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="customer-cell">
                                        <div class="avatar">
                                            <?php echo strtoupper(substr($order['full_name'], 0, 1)); ?>
                                        </div>
                                        <div class="customer-info">
                                            <h4><?php echo htmlspecialchars($order['full_name']); ?></h4>
                                            <p class="text-muted"><?php echo htmlspecialchars($order['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="price"><?php echo number_format($order['total_amount']); ?> تومان</div>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <?php echo date('Y/m/d', strtotime($order['created_at'])); ?>
                                        <br>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <div class="status-wrapper">
                                            <select name="status" class="status-select <?php echo $order['status']; ?>">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>در حال پردازش</option>
                                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>تکمیل شده</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>لغو شده</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-icon save">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                           class="btn-icon view" title="جزئیات">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../payment/invoice.php?id=<?php echo $order['id']; ?>" 
                                           class="btn-icon invoice" title="فاکتور" target="_blank">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h4>سفارشی یافت نشد</h4>
                    <p>فیلترهای جستجو را تغییر دهید</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// جستجوی سریع در جدول
document.getElementById('searchTable').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#ordersTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// تغییر رنگ select بر اساس وضعیت
document.querySelectorAll('.status-select').forEach(select => {
    updateSelectColor(select);
    
    select.addEventListener('change', function() {
        updateSelectColor(this);
    });
});

function updateSelectColor(select) {
    select.className = 'status-select ' + select.value;
}
</script>

<style>
.admin-page { padding: 20px; background: #f5f7fa; min-height: 100vh; }
.container { max-width: 1400px; margin: 0 auto; }

/* هدر */
.page-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.back-btn {
    padding: 10px 20px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: #495057;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}
.header-title { flex: 1; }
.header-title h1 { margin: 0 0 5px 0; color: #2c3e50; }
.header-title p { margin: 0; color: #6c757d; }

/* پیام‌ها */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid;
}
.alert-success { background: #d4edda; color: #155724; border-color: #28a745; }
.alert-error { background: #f8d7da; color: #721c24; border-color: #dc3545; }

/* فیلترها */
.filter-section {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.filter-form .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}
.filter-group {
    flex: 1;
    min-width: 200px;
    position: relative;
}
.filter-group i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}
.filter-group input {
    width: 100%;
    padding: 12px 40px 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
}
.filter-group input[type="date"] {
    min-width: 150px;
}
.form-select {
    padding: 12px 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    min-width: 150px;
}
.btn-filter {
    background: #3498db;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-outline {
    padding: 12px 24px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 8px;
    text-decoration: none;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* آمار */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}
.bg-warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
.bg-primary { background: linear-gradient(135deg, #3498db, #2980b9); }
.bg-success { background: linear-gradient(135deg, #27ae60, #229954); }
.bg-danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.bg-dark { background: linear-gradient(135deg, #2c3e50, #34495e); }
.stat-card h3 { margin: 0; font-size: 1.8rem; color: #2c3e50; }
.stat-card p { margin: 5px 0 0 0; color: #6c757d; font-size: 0.9rem; }
.stat-total h3 { font-size: 1.5rem; }

/* کارت و جدول */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-header h3 { margin: 0; color: #2c3e50; }
.search-box {
    position: relative;
    width: 300px;
}
.search-box input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
}
.search-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* سلول‌های جدول */
.table { width: 100%; border-collapse: separate; border-spacing: 0; }
.table th { 
    padding: clamp(12px, 1.5vw, 18px) clamp(15px, 2vw, 25px);
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    text-align: right;
    border-bottom: 2px solid #dee2e6;
}
.table td { 
    padding: clamp(15px, 2vw, 20px) clamp(15px, 2vw, 25px);
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}
.table tr:hover { background: #f8fafc; }

.order-info { min-width: 150px; }
.order-code { 
    margin: 0 0 8px 0; 
    font-family: 'Courier New', monospace;
    color: #2c3e50;
}
.badge-payment {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-payment.paid { background: #e8f8f0; color: #27ae60; }
.badge-payment.pending { background: #fff8e1; color: #f39c12; }
.badge-payment.failed { background: #fdedec; color: #e74c3c; }

.customer-cell { display: flex; align-items: center; gap: 15px; }
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}
.customer-info h4 { margin: 0 0 5px 0; font-size: 14px; }
.customer-info p { margin: 0; font-size: 12px; }

.price { 
    font-weight: 700; 
    color: #27ae60;
    font-size: 1.1rem;
}

.date-cell { 
    color: #6c757d;
    font-size: 0.9rem;
}

/* وضعیت سفارش */
.status-form { display: flex; gap: 10px; align-items: center; }
.status-wrapper { display: flex; gap: 8px; }
.status-select {
    padding: 8px 15px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    min-width: 140px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s;
}
.status-select.pending { border-color: #f39c12; color: #f39c12; }
.status-select.processing { border-color: #3498db; color: #3498db; }
.status-select.completed { border-color: #27ae60; color: #27ae60; }
.status-select.cancelled { border-color: #e74c3c; color: #e74c3c; }

.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}
.save { background: #e3f2fd; color: #1976d2; }
.save:hover { background: #1976d2; color: white; }
.view { background: #e8f5e9; color: #388e3c; }
.view:hover { background: #388e3c; color: white; }
.invoice { background: #fff3e0; color: #f57c00; }
.invoice:hover { background: #f57c00; color: white; }

.actions { display: flex; gap: 8px; }

/* وضعیت خالی */
.empty-state {
    padding: 60px 20px;
    text-align: center;
}
.empty-state i { font-size: 48px; color: #bdc3c7; margin-bottom: 20px; }
.empty-state h4 { margin: 0 0 10px 0; color: #7f8c8d; }
.empty-state p { margin: 0; color: #95a5a6; }

/* رسپانسیو */
@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 992px) {
    .filter-row { flex-direction: column; align-items: stretch; }
    .filter-group, .form-select { min-width: 100%; }
    .search-box { width: 100%; margin-top: 15px; }
    .card-header { flex-direction: column; align-items: stretch; }
}

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .table-responsive { overflow-x: auto; margin: 0 -15px; padding: 0 15px; }
    .customer-cell { flex-direction: column; align-items: flex-start; }
    .avatar { align-self: center; }
    .status-wrapper { flex-direction: column; }
    .status-select { width: 100%; }
    .actions { flex-wrap: wrap; justify-content: center; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: stretch; }
    .back-btn { justify-content: center; }
    .order-info { text-align: center; }
}
</style>

<?php require_once '../includes/footer.php'; ?>