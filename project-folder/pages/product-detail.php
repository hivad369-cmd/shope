<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// اعتبارسنجی ID محصول
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);
$product = null;
$features = [];

try {
    $sql = "SELECT * FROM products WHERE id = :id AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        header('Location: products.php');
        exit();
    }
    
    $features = json_decode($product['features'], true) ?: [];
} catch(PDOException $e) {
    die("خطا در دریافت اطلاعات محصول");
}

$pageTitle = $product['name'];
require_once '../includes/header.php';
?>

<div class="product-detail-page">
    <div class="container">
        <!-- مسیر ناوبری -->
        <nav class="breadcrumb">
            <a href="../index.php">خانه</a>
            <i class="fas fa-chevron-left"></i>
            <a href="products.php">همه دوره‌ها</a>
            <i class="fas fa-chevron-left"></i>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <!-- بخش اصلی محصول -->
        <div class="product-detail-card">
            <div class="product-gallery">
                <div class="main-image">
                <img src="../images/products/<?php echo !empty($product['image_url']) ? $product['image_url'] : 'default.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         loading="lazy">
                    <?php if($product['discount_price']): ?>
                        <div class="discount-badge">
                            <?php 
                            $discount = (($product['price'] - $product['discount_price']) / $product['price']) * 100;
                            echo round($discount) . '%';
                            ?>
                            <small>تخفیف</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-details">
                <div class="product-header">
                    <span class="product-category"><?php echo htmlspecialchars($product['level']); ?></span>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="rating-text">(۴.۵ از ۵)</span>
                    </div>
                </div>

                <!-- قیمت -->
                <div class="price-section">
                    <?php if($product['discount_price']): ?>
                        <div class="price-row">
                            <span class="old-price"><?php echo number_format($product['price']); ?> تومان</span>
                            <span class="current-price"><?php echo number_format($product['discount_price']); ?> تومان</span>
                        </div>
                        <div class="saving">
                            <i class="fas fa-tag"></i>
                            <span>تاکنون <?php echo number_format($product['price'] - $product['discount_price']); ?> تومان صرفه‌جویی کرده‌اید</span>
                        </div>
                    <?php else: ?>
                        <span class="current-price single"><?php echo number_format($product['price']); ?> تومان</span>
                    <?php endif; ?>
                </div>

                <!-- اطلاعات جانبی -->
                <div class="product-meta-grid">
                    <div class="meta-item">
                        <i class="fas fa-signal"></i>
                        <div class="meta-content">
                            <span class="meta-label">سطح دوره</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['level']); ?></span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <div class="meta-content">
                            <span class="meta-label">مدت زمان</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['duration']); ?></span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-play-circle"></i>
                        <div class="meta-content">
                            <span class="meta-label">نوع محتوا</span>
                            <span class="meta-value">ویدیوی آموزشی</span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-certificate"></i>
                        <div class="meta-content">
                            <span class="meta-label">گواهینامه</span>
                            <span class="meta-value">دارد</span>
                        </div>
                    </div>
                </div>

                <!-- دکمه‌های اقدام -->
                <div class="action-buttons">
                    <?php if(isLoggedIn()): ?>
                        <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            افزودن به سبد خرید
                        </button>
                        <button class="btn btn-outline buy-now" onclick="window.location.href='checkout.php?product_id=<?php echo $product['id']; ?>'">
                            <i class="fas fa-bolt"></i>
                            خرید سریع
                        </button>
                    <?php else: ?>
                        <a href="login.php?redirect=product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-lock"></i>
                            برای خرید وارد شوید
                        </a>
                    <?php endif; ?>
                </div>

                <!-- ویژگی‌ها -->
                <div class="features-section">
                    <h3><i class="fas fa-gem"></i> ویژگی‌های منحصر به فرد</h3>
                    <div class="features-grid">
                        <?php foreach($features as $index => $feature): ?>
                            <?php if($index < 6): ?>
                                <div class="feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo htmlspecialchars($feature); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- تب‌های اطلاعات -->
        <div class="product-tabs">
            <div class="tab-headers">
                <button class="tab-btn active" data-tab="description">توضیحات کامل</button>
                <button class="tab-btn" data-tab="curriculum">سرفصل‌ها</button>
                <button class="tab-btn" data-tab="reviews">نظرات</button>
            </div>
            
            <div class="tab-content">
                <div class="tab-pane active" id="description">
                    <div class="description-content">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>
                
                <div class="tab-pane" id="curriculum">
                    <div class="curriculum-list">
                        <h4>سرفصل‌های دوره</h4>
                        <p>سرفصل‌های این دوره به زودی اضافه خواهد شد.</p>
                    </div>
                </div>
                
                <div class="tab-pane" id="reviews">
                    <div class="reviews-section">
                        <h4>نظرات خریداران</h4>
                        <p>اولین نفری باشید که نظر می‌دهد.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- محصولات مرتبط -->
        <div class="related-products-section">
            <div class="section-header">
                <h2>دوره‌های مشابه</h2>
                <a href="products.php?level=<?php echo urlencode($product['level']); ?>" class="view-all">
                    مشاهده همه <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            
            <div class="related-products-grid">
                <?php
                try {
                    $sql = "SELECT * FROM products 
                            WHERE level = :level 
                            AND id != :id 
                            AND is_active = 1 
                            ORDER BY RAND() 
                            LIMIT 3";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':level' => $product['level'],
                        ':id' => $product_id
                    ]);
                    $related_products = $stmt->fetchAll();
                    
                    if(count($related_products) > 0):
                        foreach($related_products as $related):
                ?>
                    <div class="related-card">
                        <div class="card-image">
                        <img src="../images/products/<?php echo !empty($related['image_url']) ? $related['image_url'] : 'default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 loading="lazy">
                        </div>
                        <div class="card-content">
                            <h4><?php echo htmlspecialchars($related['name']); ?></h4>
                            <div class="card-price">
                                <?php if($related['discount_price']): ?>
                                    <span class="current"><?php echo number_format($related['discount_price']); ?> تومان</span>
                                <?php else: ?>
                                    <span class="current"><?php echo number_format($related['price']); ?> تومان</span>
                                <?php endif; ?>
                            </div>
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-small">
                                <i class="fas fa-eye"></i>
                                مشاهده دوره
                            </a>
                        </div>
                    </div>
                <?php 
                        endforeach;
                    else:
                ?>
                    <div class="no-related">
                        <i class="fas fa-box-open"></i>
                        <p>در حال حاضر دوره مشابهی وجود ندارد</p>
                    </div>
                <?php endif; ?>
                <?php } catch(PDOException $e) { } ?>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل‌های جدید */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 20px 0;
    color: #666;
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--secondary-color);
    text-decoration: none;
}

.breadcrumb span {
    color: var(--primary-color);
    font-weight: 600;
}

.product-detail-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}

.product-gallery {
    position: relative;
}

.main-image {
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.main-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: transform 0.5s;
}

.main-image:hover img {
    transform: scale(1.02);
}

.discount-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    line-height: 1.2;
}

.discount-badge small {
    display: block;
    font-size: 0.8rem;
    opacity: 0.9;
}

.product-header {
    margin-bottom: 25px;
}

.product-category {
    display: inline-block;
    background: #e8f4fc;
    color: var(--secondary-color);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.product-title {
    font-size: 2rem;
    color: var(--primary-color);
    margin: 10px 0;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.stars {
    color: #f39c12;
}

.price-section {
    padding: 20px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
    margin: 20px 0;
}

.price-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
}

.old-price {
    color: #999;
    text-decoration: line-through;
    font-size: 1.2rem;
}

.current-price {
    color: var(--accent-color);
    font-size: 1.8rem;
    font-weight: bold;
}

.current-price.single {
    font-size: 2rem;
}

.saving {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--success-color);
    font-size: 0.9rem;
}

.product-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin: 25px 0;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: transform 0.3s;
}

.meta-item:hover {
    transform: translateY(-3px);
    background: #e8f4fc;
}

.meta-item i {
    color: var(--secondary-color);
    font-size: 1.2rem;
}

.meta-content {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 0.85rem;
    color: #666;
}

.meta-value {
    font-weight: 600;
    color: var(--primary-color);
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin: 30px 0;
}

.btn-primary {
    flex: 2;
    background: linear-gradient(135deg, var(--secondary-color), #2980b9);
    padding: 15px;
    font-size: 1.1rem;
}

.btn-outline {
    flex: 1;
    border: 2px solid var(--secondary-color);
    color: var(--secondary-color);
    background: transparent;
}

.btn-outline:hover {
    background: var(--secondary-color);
    color: white;
}

.features-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin-top: 30px;
}

.features-section h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary-color);
    margin-bottom: 20px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
}

.feature-item i {
    color: var(--success-color);
    font-size: 1rem;
}

.product-tabs {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}

.tab-headers {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.tab-btn {
    padding: 20px 30px;
    background: none;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    color: #666;
    transition: all 0.3s;
    position: relative;
}

.tab-btn.active {
    color: var(--secondary-color);
    background: white;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--secondary-color);
}

.tab-content {
    padding: 30px;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.description-content {
    line-height: 1.8;
    font-size: 1.05rem;
}

.related-products-section {
    margin-top: 50px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.section-header h2 {
    color: var(--primary-color);
    font-size: 1.8rem;
}

.view-all {
    color: var(--secondary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}

.related-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.related-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.related-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.card-image {
    height: 200px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.related-card:hover .card-image img {
    transform: scale(1.1);
}

.card-content {
    padding: 20px;
}

.card-content h4 {
    margin-bottom: 15px;
    color: var(--primary-color);
    font-size: 1.1rem;
    height: 50px;
    overflow: hidden;
}

.card-price .current {
    color: var(--accent-color);
    font-size: 1.3rem;
    font-weight: bold;
    margin-bottom: 15px;
    display: block;
}

.btn-small {
    padding: 10px 20px;
    font-size: 0.9rem;
}

.no-related {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
    color: #95a5a6;
}

.no-related i {
    font-size: 3rem;
    margin-bottom: 15px;
    display: block;
}

/* رسپانسیو */
@media (max-width: 992px) {
    .product-detail-card {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .main-image img {
        height: 350px;
    }
    
    .product-title {
        font-size: 1.6rem;
    }
}

@media (max-width: 768px) {
    .product-detail-card {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .product-meta-grid {
        grid-template-columns: 1fr;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .tab-headers {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: right;
        border-bottom: 1px solid #eee;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت تب‌ها
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // حذف کلاس active از همه
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // اضافه کردن active به تب انتخاب شده
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // افزودن به سبد خرید
    const addToCartBtn = document.querySelector('.add-to-cart');
    if(addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const originalHTML = this.innerHTML;
            
            // نمایش لودینگ
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال افزودن...';
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
                    showNotification('✓ محصول به سبد خرید اضافه شد', 'success');
                    
                    // انیمیشن دکمه
                    this.innerHTML = '<i class="fas fa-check"></i> اضافه شد';
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }, 1500);
                } else {
                    showNotification('✗ ' + (data.message || 'خطا در افزودن'), 'error');
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                }
            })
            .catch(() => {
                showNotification('✗ خطا در ارتباط با سرور', 'error');
                this.innerHTML = originalHTML;
                this.disabled = false;
            });
        });
    }
    
    // تابع نمایش نوتیفیکیشن
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
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
    .notification {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-right: 4px solid;
    }
    
    .notification.show {
        transform: translateX(-50%) translateY(0);
    }
    
    .notification.success {
        border-color: #27ae60;
    }
    
    .notification.error {
        border-color: #e74c3c;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    `;
    document.head.appendChild(style);
});
</script>

<?php require_once '../includes/footer.php'; ?>