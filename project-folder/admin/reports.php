<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// بررسی دسترسی ادمین
if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

// تنظیم بازه زمانی پیش‌فرض (۷ روز گذشته)
$date_range = $_GET['range'] ?? '7days';
$end_date = date('Y-m-d');

switch($date_range) {
    case 'today': $start_date = $end_date; break;
    case '7days': $start_date = date('Y-m-d', strtotime('-7 days')); break;
    case '30days': $start_date = date('Y-m-d', strtotime('-30 days')); break;
    default: $start_date = date('Y-m-d', strtotime('-7 days'));
}

try {
    // آمار کلی
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM products) as total_products,
            (SELECT COUNT(*) FROM orders) as total_orders,
            (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed') as total_revenue
    ")->fetch();
    
    // آمار بازه زمانی
    $period_sql = "
        SELECT 
            COUNT(*) as orders_count,
            COALESCE(SUM(total_amount), 0) as revenue,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ";
    
    $stmt = $pdo->prepare($period_sql);
    $stmt->execute([$start_date, $end_date]);
    $period_stats = $stmt->fetch();
    
    // محصولات پرفروش
    $popular_products = $pdo->query("
        SELECT p.name, COUNT(oi.id) as sales
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY p.id
        ORDER BY sales DESC
        LIMIT 5
    ")->fetchAll();
    
    // آمار روزانه
    $daily_sql = "
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ";
    
    $stmt = $pdo->prepare($daily_sql);
    $stmt->execute([$start_date, $end_date]);
    $daily_stats = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("خطا در دریافت گزارشات: " . $e->getMessage());
}

$pageTitle = "گزارشات";
require_once '../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <!-- دکمه برگشت درست -->
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
        
        <div class="admin-header">
            <h1 class="page-title">گزارشات سیستم</h1>
            <form method="GET" class="date-filter">
                <select name="range" onchange="this.form.submit()">
                    <option value="today" <?= $date_range == 'today' ? 'selected' : '' ?>>امروز</option>
                    <option value="7days" <?= $date_range == '7days' ? 'selected' : '' ?>>۷ روز گذشته</option>
                    <option value="30days" <?= $date_range == '30days' ? 'selected' : '' ?>>۳۰ روز گذشته</option>
                </select>
            </form>
        </div>

        <!-- کارت‌های آماری -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-info">
                    <h3><?= number_format($stats['total_users']) ?></h3>
                    <p>کاربران</p>
                </div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <div class="stat-info">
                    <h3><?= number_format($stats['total_orders']) ?></h3>
                    <p>سفارشات</p>
                </div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-wallet"></i>
                <div class="stat-info">
                    <h3><?= number_format($stats['total_revenue']) ?> تومان</h3>
                    <p>درآمد کل</p>
                </div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="stat-info">
                    <h3><?= number_format($period_stats['orders_count']) ?></h3>
                    <p>سفارشات <?= $date_range == 'today' ? 'امروز' : 'این دوره' ?></p>
                </div>
            </div>
        </div>

        <!-- نمودار ساده -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> سفارشات روزانه</h3>
            <div class="chart-container">
                <?php if(!empty($daily_stats)): ?>
                    <div class="chart-bars">
                        <?php 
                        $max = max(array_column($daily_stats, 'count'));
                        if ($max == 0) $max = 1; // جلوگیری از تقسیم بر صفر
                        ?>
                        <?php foreach($daily_stats as $day): ?>
                            <div class="chart-col">
                                <div class="chart-bar" style="height: <?= ($day['count'] / $max) * 100 ?>%">
                                    <span class="bar-value"><?= $day['count'] ?></span>
                                </div>
                                <span class="bar-label"><?= date('d/m', strtotime($day['date'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">داده‌ای برای نمایش وجود ندارد</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- جدول محصولات پرفروش -->
        <div class="table-card">
            <h3><i class="fas fa-crown"></i> پرفروش‌ترین محصولات</h3>
            <?php if(!empty($popular_products)): ?>
            <table>
                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>تعداد فروش</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($popular_products as $index => $product): ?>
                        <tr>
                            <td>
                                <span class="rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                                <?= htmlspecialchars($product['name']) ?>
                            </td>
                            <td>
                                <span class="sales-count"><?= number_format($product['sales']) ?></span>
                                فروش
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="no-data">محصولی فروخته نشده است</p>
            <?php endif; ?>
        </div>

        <!-- آمار وضعیت سفارشات -->
        <div class="status-cards">
            <div class="status-card status-completed">
                <i class="fas fa-check-circle"></i>
                <div class="status-info">
                    <h4><?= number_format($period_stats['completed_orders']) ?></h4>
                    <p>تکمیل شده</p>
                </div>
            </div>
            
            <div class="status-card status-pending">
                <i class="fas fa-clock"></i>
                <div class="status-info">
                    <h4><?= number_format($period_stats['pending_orders']) ?></h4>
                    <p>در انتظار</p>
                </div>
            </div>
            
            <div class="status-card status-processing">
                <i class="fas fa-cog"></i>
                <div class="status-info">
                    <h4><?= number_format($period_stats['orders_count'] - $period_stats['completed_orders'] - $period_stats['pending_orders']) ?></h4>
                    <p>در حال پردازش</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-page { padding: 20px; background: #f8fafc; min-height: 100vh; }
.container { max-width: 1200px; margin: 0 auto; }

/* دکمه برگشت - اینجا باید خارج از media query باشد */
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    color: #2c3e50;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    border: 1px solid #eee;
    transition: all 0.3s ease;
    margin-bottom: 20px;
    font-family: inherit;
    font-size: 0.9rem;
}

.back-btn:hover {
    background: #e9ecef;
    border-color: #ddd;
    transform: translateX(-5px);
    color: #3498db;
}

.back-btn i {
    transition: transform 0.3s ease;
}

.back-btn:hover i {
    transform: translateX(3px);
}

.admin-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.page-title { margin: 0; color: #2c3e50; }

.date-filter select {
    padding: 8px 15px;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 0.9rem;
    background: white;
}

/* کارت‌های آمار */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card i {
    font-size: 2rem;
    color: #3498db;
}

.stat-info h3 {
    margin: 0;
    font-size: 1.8rem;
    color: #2c3e50;
}

.stat-info p {
    margin: 5px 0 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

/* نمودار */
.chart-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.chart-card h3 {
    margin-top: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #2c3e50;
}

.chart-container {
    padding: 20px 0;
    min-height: 200px;
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    gap: 15px;
    height: 150px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.chart-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 40px;
}

.chart-bar {
    width: 30px;
    background: linear-gradient(to top, #3498db, #2980b9);
    border-radius: 4px 4px 0 0;
    position: relative;
    min-height: 5px;
    transition: height 0.3s ease;
}

.bar-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    font-weight: bold;
    background: rgba(44, 62, 80, 0.9);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
}

.bar-label {
    margin-top: 8px;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #95a5a6;
    font-style: italic;
}

/* جدول */
.table-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.table-card h3 {
    margin-top: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #2c3e50;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

thead tr {
    background: #f8f9fa;
}

th {
    padding: 12px 15px;
    text-align: right;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #dee2e6;
}

td {
    padding: 12px 15px;
    text-align: right;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background-color: #f8fafc;
}

.rank {
    display: inline-block;
    width: 24px;
    height: 24px;
    background: #95a5a6;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 24px;
    margin-left: 10px;
    font-size: 0.8rem;
}

.rank-1 { background: #f39c12; }
.rank-2 { background: #7f8c8d; }
.rank-3 { background: #e74c3c; }
.rank-4 { background: #3498db; }
.rank-5 { background: #9b59b6; }

.sales-count {
    font-weight: bold;
    color: #27ae60;
    margin-left: 5px;
}

/* وضعیت‌ها */
.status-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.status-card {
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    color: white;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.status-card i {
    font-size: 1.8rem;
}

.status-info h4 {
    margin: 0;
    font-size: 1.5rem;
}

.status-info p {
    margin: 5px 0 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.status-completed { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.status-pending { background: linear-gradient(135deg, #f39c12, #f1c40f); }
.status-processing { background: linear-gradient(135deg, #3498db, #2980b9); }

/* ریسپانسیو */
@media (max-width: 768px) {
    .admin-header { 
        flex-direction: column; 
        align-items: flex-start; 
        gap: 15px; 
    }
    
    .stats-grid { 
        grid-template-columns: repeat(2, 1fr); 
    }
    
    .status-cards { 
        grid-template-columns: 1fr; 
    }
    
    .chart-bars {
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 40px;
    }
    
    .chart-col {
        min-width: 50px;
    }
    
    .back-btn {
        padding: 8px 15px;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .stats-grid { 
        grid-template-columns: 1fr; 
    }
    
    .back-btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 15px;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>