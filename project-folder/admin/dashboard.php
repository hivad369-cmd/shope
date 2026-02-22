<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'");
    $total_revenue = $stmt->fetchColumn() ?: 0;
    
    $stmt = $pdo->prepare("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_orders = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("خطا در دریافت اطلاعات: " . $e->getMessage());
}

$pageTitle = "داشبورد مدیریت";
require_once '../includes/header.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <!-- هدر بهبود یافته -->
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> داشبورد مدیریت</h1>
                <p class="welcome-msg">خوش آمدید، <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></p>
            </div>
            <div class="header-date">
                <i class="fas fa-calendar"></i> <?php echo date('Y/m/d'); ?>
            </div>
        </div>
        
        <!-- کارت‌های آمار بهبود یافته -->
        <div class="dashboard-stats">
            <div class="stat-card stat-users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">کاربران</div>
                </div>
            </div>
            
            <div class="stat-card stat-products">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_products); ?></div>
                    <div class="stat-label">محصولات</div>
                </div>
            </div>
            
            <div class="stat-card stat-orders">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                    <div class="stat-label">سفارشات</div>
                </div>
            </div>
            
            <div class="stat-card stat-revenue">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_revenue); ?></div>
                    <div class="stat-label">درآمد کل</div>
                    <div class="stat-unit">تومان</div>
                </div>
            </div>
        </div>
        
        <!-- محتوای اصلی -->
        <div class="dashboard-content">
            <!-- جدول سفارشات -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-shopping-bag"></i> آخرین سفارشات</h3>
                    <a href="orders.php" class="view-all">مشاهده همه <i class="fas fa-arrow-left"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>شماره سفارش</th>
                                    <th>مشتری</th>
                                    <th>مبلغ</th>
                                    <th>تاریخ</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['order_code']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td class="text-price"><?php echo number_format($order['total_amount']); ?> تومان</td>
                                    <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'در انتظار',
                                                'processing' => 'در حال پردازش',
                                                'completed' => 'تکمیل شده',
                                                'cancelled' => 'لغو شده'
                                            ];
                                            echo $status_labels[$order['status']];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="action-btn" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- لینک‌های سریع -->
            <div class="sidebar">
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-link"></i> دسترسی سریع</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-links">
                            <a href="manage-products.php" class="quick-link">
                                <div class="link-icon" data-hover-color="white">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <span>مدیریت محصولات</span>
                            </a>
                            <a href="orders.php" class="quick-link">
                                <div class="link-icon" data-hover-color="white">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <span>مدیریت سفارشات</span>
                            </a>
                            <a href="manage-users.php" class="quick-link">
                                <div class="link-icon" data-hover-color="white">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span>مدیریت کاربران</span>
                            </a>
                            <a href="reports.php" class="quick-link">
                                <div class="link-icon" data-hover-color="white">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <span>گزارشات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل‌های جدید برای داشبورد */
.admin-dashboard {
    padding: 20px 0;
    min-height: calc(100vh - 200px);
    background: #f8fafc;
}

.dashboard-header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-right: 4px solid #3498db;
}

.dashboard-header h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dashboard-header h1 i {
    color: #3498db;
}

.welcome-msg {
    margin: 8px 0 0;
    color: #7f8c8d;
    font-size: 0.95rem;
}

.welcome-msg span {
    color: #2c3e50;
    font-weight: 600;
}

.header-date {
    background: #e8f4fc;
    padding: 8px 15px;
    border-radius: 8px;
    color: #3498db;
    font-size: 0.9rem;
}

.header-date i {
    margin-left: 5px;
}

/* کارت‌های آمار */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    border-top: 4px solid;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.stat-users { border-color: #3498db; }
.stat-products { border-color: #2ecc71; }
.stat-orders { border-color: #e74c3c; }
.stat-revenue { border-color: #9b59b6; }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-users .stat-icon { background: #3498db; }
.stat-products .stat-icon { background: #2ecc71; }
.stat-orders .stat-icon { background: #e74c3c; }
.stat-revenue .stat-icon { background: #9b59b6; }

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    color: #7f8c8d;
    font-size: 0.95rem;
    margin-bottom: 3px;
}

.stat-unit {
    font-size: 0.85rem;
    color: #95a5a6;
}

/* محتوای اصلی */
.dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

@media (max-width: 992px) {
    .dashboard-content {
        grid-template-columns: 1fr;
    }
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    overflow: hidden;
}

.card-header {
    padding: 18px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.view-all {
    color: #3498db;
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-all:hover {
    color: #2980b9;
    text-decoration: underline;
}

.card-body {
    padding: 20px;
}

/* جدول */
.table-container {
    overflow-x: auto;
}

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
}

.dashboard-table th {
    padding: 14px 12px;
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    text-align: right;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.dashboard-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.dashboard-table tr:hover {
    background-color: #f8fafc;
}

.text-price {
    color: #27ae60;
    font-weight: 600;
}

/* وضعیت‌ها */
.order-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-align: center;
    min-width: 100px;
}

.status-pending {
    background: #fff8e1;
    color: #f39c12;
    border: 1px solid #ffeaa7;
}

.status-processing {
    background: #e8f4fc;
    color: #3498db;
    border: 1px solid #d6eaf8;
}

.status-completed {
    background: #e8f8f0;
    color: #27ae60;
    border: 1px solid #abebc6;
}

.status-cancelled {
    background: #fdedec;
    color: #e74c3c;
    border: 1px solid #fadbd8;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #e8f4fc;
    color: #3498db;
    text-decoration: none;
    transition: all 0.3s;
}

.action-btn:hover {
    background: #3498db;
    color: white;
    transform: scale(1.05);
}

/* لینک‌های سریع */
.quick-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s;
    border: 1px solid transparent;
}

.quick-link:hover {
    background: white;
    border-color: #3498db;
    transform: translateX(-5px);
    color: #3498db;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
}

.link-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #e8f4fc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    font-size: 1.2rem;
}

.quick-link:hover .link-icon {
    background: #3498db !important;
    color: #ffffff !important;
}

.quick-link:hover .link-icon[data-hover-color="white"] i {
    color:rgb(255, 255, 255);
}

.quick-link span {
    flex: 1;
    font-weight: 500;
}

/* ریسپانسیو */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stat-value {
        font-size: 1.6rem;
    }
    
    .dashboard-table th,
    .dashboard-table td {
        padding: 10px 8px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>