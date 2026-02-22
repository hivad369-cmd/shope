<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    header('Location: profile.php');
    exit();
}

// دریافت اطلاعات سفارش
try {
    $sql = "SELECT o.*, u.full_name, u.email, u.phone 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ? AND o.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: profile.php');
        exit();
    }
    
    // دریافت آیتم‌های سفارش
    $sql = "SELECT oi.*, p.name, p.image_url, p.level, p.duration 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("خطا در دریافت اطلاعات سفارش");
}

// وضعیت سفارش
$status_badges = [
    'pending' => ['text' => 'در انتظار پرداخت', 'color' => '#f39c12'],
    'processing' => ['text' => 'در حال پردازش', 'color' => '#3498db'],
    'completed' => ['text' => 'تکمیل شده', 'color' => '#27ae60'],
    'cancelled' => ['text' => 'لغو شده', 'color' => '#e74c3c']
];

$pageTitle = "جزئیات سفارش #{$order['order_code']}";
require_once '../includes/header.php';
?>

<div class="order-details-page">
    <div class="container">
        <!-- هدر -->
        <div class="page-header">
            <a href="profile.php#orders" class="btn-back">
                <i class="fas fa-arrow-right"></i>
            </a>
            <div class="header-info">
                <h1>جزئیات سفارش</h1>
                <p class="order-code">کد سفارش: <strong>#<?php echo $order['order_code']; ?></strong></p>
            </div>
        </div>

        <!-- خلاصه سفارش -->
        <div class="order-summary">
            <div class="summary-card">
                <div class="summary-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <span class="label">تاریخ سفارش</span>
                        <span class="value"><?php echo date('Y/m/d - H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                </div>
                
                <div class="summary-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <span class="label">مشتری</span>
                        <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                </div>
                
                <div class="summary-item">
                    <i class="fas fa-wallet"></i>
                    <div>
                        <span class="label">مبلغ کل</span>
                        <span class="value price"><?php echo number_format($order['total_amount']); ?> تومان</span>
                    </div>
                </div>
                
                <div class="summary-item">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <span class="label">وضعیت</span>
                        <span class="status-badge" style="background: <?php echo $status_badges[$order['status']]['color']; ?>">
                            <?php echo $status_badges[$order['status']]['text']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-content">
            <!-- آیتم‌های سفارش -->
            <div class="order-items-section">
                <div class="section-header">
                    <h2><i class="fas fa-box"></i> محصولات سفارش</h2>
                    <span class="count"><?php echo count($order_items); ?> محصول</span>
                </div>
                
                <div class="items-list">
                    <?php foreach($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <img src="../images/products/<?php echo $item['image_url'] ?: 'default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <div class="item-meta">
                                <span class="meta">
                                    <i class="fas fa-signal"></i>
                                    <?php echo htmlspecialchars($item['level']); ?>
                                </span>
                                <span class="meta">
                                    <i class="fas fa-clock"></i>
                                    <?php echo htmlspecialchars($item['duration']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="item-info">
                            <div class="price-info">
                                <span class="price"><?php echo number_format($item['price']); ?> تومان</span>
                                <span class="quantity">× <?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="total">
                                <?php echo number_format($item['price'] * $item['quantity']); ?> تومان
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- جزئیات پرداخت -->
            <div class="order-sidebar">
                <!-- اطلاعات فاکتور -->
                <div class="invoice-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice"></i>
                        <h3>صورتحساب</h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="invoice-row">
                            <span>جمع محصولات</span>
                            <span><?php echo number_format($order['total_amount']); ?> تومان</span>
                        </div>
                        
                        <div class="invoice-row">
                            <span>تخفیف</span>
                            <span class="discount">۰ تومان</span>
                        </div>
                        
                        <div class="invoice-row">
                            <span>هزینه ارسال</span>
                            <span class="free">رایگان</span>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="invoice-row total">
                            <span>مبلغ قابل پرداخت</span>
                            <span class="total-price"><?php echo number_format($order['total_amount']); ?> تومان</span>
                        </div>
                    </div>
                </div>

                <!-- اطلاعات مشتری -->
                <div class="customer-card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                        <h3>اطلاعات مشتری</h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="info-row">
                            <span class="label">نام:</span>
                            <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">ایمیل:</span>
                            <span><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                        <?php if($order['phone']): ?>
                        <div class="info-row">
                            <span class="label">تلفن:</span>
                            <span><?php echo htmlspecialchars($order['phone']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- اقدامات -->
                <div class="actions-card">
                    <div class="card-header">
                        <i class="fas fa-cog"></i>
                        <h3>اقدامات</h3>
                    </div>
                    
                    <div class="card-body">
                        <?php if($order['status'] == 'pending'): ?>
                        <button class="btn btn-primary btn-block" onclick="window.location.href='checkout.php?order_id=<?php echo $order_id; ?>'">
                            <i class="fas fa-credit-card"></i> پرداخت سفارش
                        </button>
                        <?php endif; ?>
                        
                        <?php if($order['status'] == 'completed'): ?>
                        <button class="btn btn-success btn-block" onclick="window.location.href='download.php?order_id=<?php echo $order_id; ?>'">
                            <i class="fas fa-download"></i> دریافت فایل‌ها
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline btn-block" onclick="window.print()">
                            <i class="fas fa-print"></i> چاپ فاکتور
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.order-details-page {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

/* هدر */
.page-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-back {
    width: 45px;
    height: 45px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #3b82f6;
    color: white;
    transform: translateX(-3px);
}

.header-info h1 {
    margin: 0;
    font-size: 1.5rem;
    color: #1e293b;
}

.order-code {
    margin-top: 5px;
    color: #64748b;
    font-size: 0.95rem;
}

/* خلاصه سفارش */
.order-summary {
    margin-bottom: 25px;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.summary-item i {
    font-size: 1.5rem;
    color: #3b82f6;
    width: 40px;
    text-align: center;
}

.summary-item .label {
    display: block;
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.summary-item .value {
    display: block;
    color: #1e293b;
    font-weight: 500;
}

.summary-item .price {
    color: #e74c3c;
    font-size: 1.1rem;
    font-weight: bold;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    color: white;
    font-size: 0.85rem;
    font-weight: 500;
}

/* محتوای اصلی */
.order-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

@media (max-width: 992px) {
    .order-content {
        grid-template-columns: 1fr;
    }
}

/* آیتم‌های سفارش */
.order-items-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
    font-size: 1.2rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h2 i {
    color: #3b82f6;
}

.count {
    background: #f1f5f9;
    color: #475569;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

/* آیتم */
.order-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.order-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

@media (max-width: 768px) {
    .order-item {
        flex-direction: column;
        gap: 15px;
    }
}

.item-image {
    flex: 0 0 100px;
}

.item-image img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.item-details {
    flex: 1;
    min-width: 0;
}

.item-details h4 {
    margin: 0 0 10px 0;
    color: #1e293b;
    font-size: 1.1rem;
}

.item-meta {
    display: flex;
    gap: 15px;
}

.meta {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #64748b;
    font-size: 0.9rem;
}

.meta i {
    color: #3b82f6;
    font-size: 0.9rem;
}

.item-info {
    flex: 0 0 150px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

@media (max-width: 768px) {
    .item-info {
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
    }
}

.price-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.price {
    color: #e74c3c;
    font-weight: bold;
    font-size: 1.1rem;
}

.quantity {
    color: #64748b;
    font-size: 0.9rem;
}

.total {
    background: #f1f5f9;
    padding: 8px 15px;
    border-radius: 8px;
    color: #1e293b;
    font-weight: bold;
    font-size: 1rem;
}

/* سایدبار */
.order-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* کارت‌ها */
.invoice-card,
.customer-card,
.actions-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card-header {
    background: #f8fafc;
    padding: 18px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header i {
    color: #3b82f6;
    font-size: 1.2rem;
}

.card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #1e293b;
}

.card-body {
    padding: 20px;
}

/* فاکتور */
.invoice-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    color: #475569;
    font-size: 0.95rem;
}

.invoice-row .discount {
    color: #27ae60;
}

.invoice-row .free {
    color: #3b82f6;
}

.divider {
    height: 1px;
    background: #e2e8f0;
    margin: 15px 0;
}

.invoice-row.total {
    background: #f1f5f9;
    padding: 15px;
    margin: 10px -20px -20px;
    border-radius: 0 0 12px 12px;
    font-weight: bold;
    color: #1e293b;
    font-size: 1.1rem;
}

.total-price {
    color: #e74c3c;
    font-size: 1.2rem;
}

/* اطلاعات مشتری */
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    color: #64748b;
    font-size: 0.9rem;
}

.info-row span:last-child {
    color: #1e293b;
    font-weight: 500;
}

/* دکمه‌ها */
.btn {
    padding: 12px 20px;
    border-radius: 8px;
    border: none;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-block {
    width: 100%;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    margin-bottom: 10px;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-success {
    background: #27ae60;
    color: white;
    margin-bottom: 10px;
}

.btn-success:hover {
    background: #219653;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-outline {
    background: white;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.btn-outline:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

/* استایل پرینت */
@media print {
    .order-details-page {
        background: white;
        padding: 0;
    }
    
    .btn-back,
    .actions-card,
    .section-header .count {
        display: none !important;
    }
    
    .order-content {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        break-inside: avoid;
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

<script>
// مدیریت پرینت
document.addEventListener('DOMContentLoaded', function() {
    // اضافه کردن دکمه پرینت در حالت موبایل
    if (window.innerWidth < 768) {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-outline btn-block';
        printBtn.innerHTML = '<i class="fas fa-print"></i> چاپ فاکتور';
        printBtn.style.marginTop = '10px';
        printBtn.onclick = () => window.print();
        
        const actionsCard = document.querySelector('.actions-card');
        if (actionsCard) {
            actionsCard.querySelector('.card-body').appendChild(printBtn);
        }
    }
    
    // نمایش اطلاعیه برای سفارش‌های در انتظار
    const statusBadge = document.querySelector('.status-badge');
    if (statusBadge && statusBadge.textContent.includes('در انتظار پرداخت')) {
        setTimeout(() => {
            if (!sessionStorage.getItem('payment_notice_shown')) {
                alert('💳 توجه: سفارش شما در انتظار پرداخت است. برای تکمیل سفارش، روی دکمه "پرداخت سفارش" کلیک کنید.');
                sessionStorage.setItem('payment_notice_shown', 'true');
            }
        }, 1000);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>