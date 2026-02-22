<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$pageTitle = "دوره‌های آموزشی";
require_once '../includes/header.php';

// دریافت پارامترهای فیلتر
$level_filter = isset($_GET['level']) ? $_GET['level'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';

// ساخت کوئری پایه
$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];

// اعمال جستجو
if (!empty($search_query)) {
    $sql .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search_query%";
}

// اعمال فیلتر سطح
if (!empty($level_filter)) {
    $sql .= " AND level = :level";
    $params[':level'] = $level_filter;
}

// اعمال محدوده قیمت
if (!empty($price_range)) {
    if ($price_range == '0-200000') {
        $sql .= " AND (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) <= 200000";
    } elseif ($price_range == '200000-500000') {
        $sql .= " AND (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) BETWEEN 200000 AND 500000";
    } elseif ($price_range == '500000-1000000') {
        $sql .= " AND (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) BETWEEN 500000 AND 1000000";
    } elseif ($price_range == '1000000+') {
        $sql .= " AND (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) > 1000000";
    }
}

// اعمال مرتب‌سازی
switch ($sort) {
    case 'cheapest':
        $sql .= " ORDER BY (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) ASC";
        break;
    case 'expensive':
        $sql .= " ORDER BY (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch(PDOException $e) {
    die("خطا در دریافت محصولات");
}
?>

<div class="courses-page">
    <div class="container">
        <!-- هدر صفحه -->
        <div class="page-hero">
            <h1><i class="fas fa-graduation-cap"></i> دوره‌های آموزش زبان</h1>
            <p>با انتخاب دوره مناسب، یادگیری زبان انگلیسی را شروع کنید</p>
        </div>

        <!-- فیلترها و جستجو -->
        <div class="filter-bar">
            <div class="filter-card">
                <div class="filter-header">
                    <i class="fas fa-filter"></i>
                    <h3>فیلتر دوره‌ها</h3>
                </div>
                
                <div class="filter-body">
                                <!-- نمایش عبارت جستجو اگر وجود داشته باشد -->
                    <?php if(!empty($search_query)): ?>
                    <div class="search-result-info">
                        <i class="fas fa-search"></i>
                        <span>نتایج جستجو برای: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></span>
                        <a href="products.php" class="clear-search">× پاک کردن</a>
                    </div>
                    <?php endif; ?>

                    <div class="filter-group">
                        <label><i class="fas fa-signal"></i> سطح دوره</label>
                        <div class="level-filters">
                            <button class="level-btn <?php echo $level_filter == '' ? 'active' : ''; ?>" data-level="">همه</button>
                            <button class="level-btn <?php echo $level_filter == 'مقدماتی' ? 'active' : ''; ?>" data-level="مقدماتی">مقدماتی</button>
                            <button class="level-btn <?php echo $level_filter == 'متوسط' ? 'active' : ''; ?>" data-level="متوسط">متوسط</button>
                            <button class="level-btn <?php echo $level_filter == 'پیشرفته' ? 'active' : ''; ?>" data-level="پیشرفته">پیشرفته</button>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-sort"></i> مرتب‌سازی</label>
                        <div class="sort-filters">
                            <button class="sort-btn <?php echo $sort == 'newest' ? 'active' : ''; ?>" data-sort="newest">
                                <i class="fas fa-clock"></i> جدیدترین
                            </button>
                            <button class="sort-btn <?php echo $sort == 'cheapest' ? 'active' : ''; ?>" data-sort="cheapest">
                                <i class="fas fa-money-bill"></i> ارزان‌ترین
                            </button>
                            <button class="sort-btn <?php echo $sort == 'expensive' ? 'active' : ''; ?>" data-sort="expensive">
                                <i class="fas fa-crown"></i> گران‌ترین
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card">
                <div class="stat-item">
                    <i class="fas fa-book"></i>
                    <div>
                        <h4><?php echo count($products); ?></h4>
                        <p>دوره فعال</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <h4>۲۵۰۰+</h4>
                        <p>دانشجو</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- لیست دوره‌ها -->
        <div class="courses-container">
            <?php if(count($products) > 0): ?>
                <div class="courses-grid">
                    <?php foreach($products as $product): ?>
                        <?php 
                        $discount_percent = 0;
                        if($product['discount_price']) {
                            $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                        }
                        ?>
                        
                        <div class="course-card">
                            <div class="course-image">
                            <img src="../images/products/<?php echo !empty($product['image_url']) ? $product['image_url'] : 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                                
                                <div class="image-overlay">
                                    <?php if($discount_percent > 0): ?>
                                        <span class="discount-tag"><?php echo $discount_percent; ?>%</span>
                                    <?php endif; ?>
                                    <span class="level-tag <?php echo strtolower($product['level']); ?>">
                                        <?php echo htmlspecialchars($product['level']); ?>
                                    </span>
                                </div>
                                
                                <div class="quick-view">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="quick-btn">
                                        <i class="fas fa-eye"></i> مشاهده سریع
                                    </a>
                                </div>
                            </div>

                            <div class="course-content">
                                <div class="course-header">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p><?php echo substr(htmlspecialchars($product['description']), 0, 120) . '...'; ?></p>
                                </div>

                                <div class="course-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($product['duration']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-play-circle"></i>
                                        <span>ویدیو</span>
                                    </div>
                                </div>

                                <div class="course-footer">
                                    <div class="course-price">
                                        <?php if($discount_percent > 0): ?>
                                            <div class="price-row">
                                                <span class="old-price"><?php echo number_format($product['price']); ?></span>
                                                <span class="new-price"><?php echo number_format($product['discount_price']); ?> تومان</span>
                                            </div>
                                            <span class="price-save"><?php echo number_format($product['price'] - $product['discount_price']); ?> تومان صرفه‌جویی</span>
                                        <?php else: ?>
                                            <span class="normal-price"><?php echo number_format($product['price']); ?> تومان</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="course-actions">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                                            <i class="fas fa-info-circle"></i> جزئیات
                                        </a>
                                        
                                        <?php if(isLoggedIn()): ?>
                                            <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-primary">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>دوره‌ای یافت نشد</h3>
                    <p>با تغییر فیلترها دوباره جستجو کنید</p>
                    <button class="btn btn-reset" onclick="window.location.href='products.php'">
                        <i class="fas fa-redo"></i> حذف همه فیلترها
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* استایل‌های صفحه دوره‌ها */
.courses-page {
    background: #f8fafc;
    min-height: calc(100vh - 200px);
    padding: 40px 0;
}

.page-hero {
    text-align: center;
    margin-bottom: 50px;
}

.page-hero h1 {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.page-hero p {
    color: #666;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

/* فیلتر بار */
.filter-bar {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 25px;
    margin-bottom: 40px;
}

@media (max-width: 992px) {
    .filter-bar {
        grid-template-columns: 1fr;
    }
}

.filter-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.filter-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.filter-header i {
    color: var(--secondary-color);
    font-size: 1.3rem;
}

.filter-header h3 {
    margin: 0;
    color: var(--primary-color);
    font-size: 1.3rem;
}

.filter-body {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.filter-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    color: var(--primary-color);
    font-weight: 600;
}

.level-filters, .sort-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.level-btn, .sort-btn {
    padding: 10px 20px;
    border: 2px solid #e0e6ef;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.95rem;
    color: #4a5568;
}

.level-btn:hover, .sort-btn:hover {
    border-color: var(--secondary-color);
    color: var(--secondary-color);
}

.level-btn.active, .sort-btn.active {
    background: var(--secondary-color);
    color: white;
    border-color: var(--secondary-color);
}

.sort-btn {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* کارت آمار */
.stats-card {
    background: linear-gradient(135deg, var(--primary-color), #34495e);
    border-radius: 15px;
    padding: 25px;
    color: white;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-item i {
    font-size: 2rem;
    opacity: 0.9;
}

.stat-item h4 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: bold;
}

.stat-item p {
    margin: 5px 0 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

/* گرید دوره‌ها */
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
}

@media (max-width: 768px) {
    .courses-grid {
        grid-template-columns: 1fr;
    }
}

.course-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
}

.course-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.course-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.course-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.course-card:hover .course-image img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    display: flex;
    justify-content: space-between;
}

.discount-tag {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
}

.level-tag {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: white;
}

.level-tag.مقدماتی { background: #27ae60; }
.level-tag.متوسط { background: #f39c12; }
.level-tag.پیشرفته { background: #9b59b6; }

.quick-view {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.course-card:hover .quick-view {
    opacity: 1;
}

.quick-btn {
    background: white;
    color: var(--primary-color);
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    transition: transform 0.3s;
}

.quick-btn:hover {
    transform: scale(1.05);
    color: var(--secondary-color);
}

/* محتوای کارت */
.course-content {
    padding: 25px;
}

.course-header {
    margin-bottom: 20px;
}

.course-header h3 {
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 10px;
    line-height: 1.4;
}

.course-header p {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

.course-meta {
    display: flex;
    gap: 20px;
    padding: 15px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
    margin: 15px 0;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.meta-item i {
    color: var(--secondary-color);
}

/* فوتر کارت */
.course-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.course-price {
    flex: 1;
}

.price-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}

.old-price {
    color: #999;
    text-decoration: line-through;
    font-size: 1.1rem;
}

.new-price {
    color: var(--accent-color);
    font-size: 1.5rem;
    font-weight: bold;
}

.normal-price {
    color: var(--primary-color);
    font-size: 1.5rem;
    font-weight: bold;
}

.price-save {
    display: block;
    color: var(--success-color);
    font-size: 0.9rem;
    font-weight: 500;
}

.course-actions {
    display: flex;
    gap: 10px;
}

.btn-outline {
    padding: 10px 20px;
    border: 2px solid var(--secondary-color);
    color: var(--secondary-color);
    background: transparent;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-outline:hover {
    background: var(--secondary-color);
    color: white;
}

.btn-primary {
    padding: 12px 15px;
    background: var(--secondary-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #2980b9;
}

/* حالت خالی */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.empty-icon {
    font-size: 4rem;
    color: #bdc3c7;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    margin-bottom: 25px;
}

.btn-reset {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    transition: transform 0.3s;
}

.btn-reset:hover {
    transform: translateY(-3px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت فیلترهای سطح
    document.querySelectorAll('.level-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateFilters('level', this.dataset.level);
        });
    });
    
    // مدیریت فیلترهای مرتب‌سازی
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateFilters('sort', this.dataset.sort);
        });
    });
    
    // تابع به‌روزرسانی فیلترها
    function updateFilters(type, value) {
        const url = new URL(window.location);
        
        if (value === '') {
            url.searchParams.delete(type);
        } else {
            url.searchParams.set(type, value);
        }
        
        window.location.href = url.toString();
    }
    
    // افزودن به سبد خرید
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const originalHTML = this.innerHTML;
            
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            fetch('../ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('✓ به سبد خرید اضافه شد');
                    this.innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    showNotification('✗ ' + (data.message || 'خطا در افزودن'), 'error');
                    this.innerHTML = originalHTML;
                }
            })
            .catch(() => {
                showNotification('✗ خطا در ارتباط', 'error');
                this.innerHTML = originalHTML;
            })
            .finally(() => {
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                }, 1500);
            });
        });
    });
    
    // تابع نمایش نوتیفیکیشن
    function showNotification(message, type = 'success') {
        // حذف نوتیفیکیشن قبلی
        const oldNote = document.querySelector('.quick-notification');
        if(oldNote) oldNote.remove();
        
        const notification = document.createElement('div');
        notification.className = `quick-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // استایل نوتیفیکیشن
    const style = document.createElement('style');
    style.textContent = `
    .quick-notification {
        position: fixed;
        bottom: 30px;
        left: 30px;
        background: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-right: 4px solid;
    }
    
    .quick-notification.show {
        transform: translateX(0);
    }
    
    .quick-notification.success {
        border-color: #27ae60;
    }
    
    .quick-notification.error {
        border-color: #e74c3c;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    
    .quick-notification i {
        font-size: 1.2rem;
    }
    
    .success .notification-content i {
        color: #27ae60;
    }
    
    .error .notification-content i {
        color: #e74c3c;
    }
    `;
    document.head.appendChild(style);
});
</script>

<?php require_once '../includes/footer.php'; ?>