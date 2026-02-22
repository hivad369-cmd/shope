<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
if(!isLoggedIn()) {
    header('Location: login.php?redirect=cart');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // دریافت آیتم‌های سبد خرید
    $sql = "SELECT c.id as cart_id, c.quantity, p.* 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $cart_items = $stmt->fetchAll();
    
    // محاسبه جمع کل
    $total = 0;
    foreach($cart_items as $item) {
        $price = $item['discount_price'] ?: $item['price'];
        $total += $price * $item['quantity'];
    }
    
} catch(PDOException $e) {
    die("خطا در دریافت سبد خرید: " . $e->getMessage());
}

$pageTitle = "سبد خرید";
require_once '../includes/header.php';
?>

<div class="cart-page">
    <div class="container">
        <div class="cart-header">
            <h1 class="page-title"><i class="fas fa-shopping-cart"></i> سبد خرید شما</h1>
            <span class="item-count"><?php echo count($cart_items); ?> آیتم</span>
        </div>
        
        <?php if(count($cart_items) > 0): ?>
        <div class="cart-content">
            <div class="cart-items-section">
                <div class="cart-items">
                    <?php foreach($cart_items as $item): 
                        $item_price = $item['discount_price'] ?: $item['price'];
                        $item_total = $item_price * $item['quantity'];
                    ?>
                    <div class="cart-item-card" data-id="<?php echo $item['cart_id']; ?>">
                        <div class="item-main">
                            <div class="product-image">
                                <img src="../images/products/<?php echo !empty($item['image_url']) ? $item['image_url'] : 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php if($item['discount_price']): ?>
                                    <span class="discount-badge">
                                        <i class="fas fa-percentage"></i> تخفیف
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-details">
                                <h4 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <div class="product-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-signal"></i>
                                        <?php echo htmlspecialchars($item['level']); ?>
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <?php echo htmlspecialchars($item['duration']); ?>
                                    </span>
                                </div>
                                
                                <div class="product-price">
                                    <?php if($item['discount_price']): ?>
                                        <span class="original-price">
                                            <?php echo number_format($item['price']); ?> تومان
                                        </span>
                                        <span class="current-price">
                                            <?php echo number_format($item_price); ?> تومان
                                        </span>
                                    <?php else: ?>
                                        <span class="current-price">
                                            <?php echo number_format($item_price); ?> تومان
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="item-actions">
                            <div class="quantity-section">
                                <div class="quantity-control">
                                    <button class="quantity-btn decrease" data-action="decrease" title="کاهش">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <div class="quantity-display">
                                        <span class="quantity-number"><?php echo $item['quantity']; ?></span>
                                        <span class="quantity-label">عدد</span>
                                    </div>
                                    <button class="quantity-btn increase" data-action="increase" title="افزایش">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="item-total">
                                    <span class="total-label">مجموع:</span>
                                    <span class="total-price"><?php echo number_format($item_total); ?> تومان</span>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="btn-remove" data-id="<?php echo $item['cart_id']; ?>" title="حذف">
                                    <i class="fas fa-trash"></i>
                                    <span class="remove-text">حذف</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-continue">
                    <a href="products.php" class="btn-continue">
                        <i class="fas fa-arrow-left"></i>
                        ادامه خرید
                    </a>
                </div>
            </div>
            
            <div class="cart-summary-section">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3><i class="fas fa-receipt"></i> خلاصه سفارش</h3>
                    </div>
                    
                    <div class="summary-body">
                        <div class="summary-row">
                            <span class="label">تعداد آیتم‌ها</span>
                            <span class="value"><?php echo count($cart_items); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="label">جمع آیتم‌ها</span>
                            <span class="value"><?php echo number_format($total); ?> تومان</span>
                        </div>
                        
                        <div class="summary-row discount">
                            <span class="label">تخفیف</span>
                            <span class="value">۰ تومان</span>
                        </div>
                        
                        <div class="summary-row shipping">
                            <span class="label">هزینه ارسال</span>
                            <span class="value">رایگان</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total-row">
                            <span class="label">مبلغ قابل پرداخت</span>
                            <span class="value total-amount"><?php echo number_format($total); ?> تومان</span>
                        </div>
                        
                        <div class="summary-note">
                            <i class="fas fa-info-circle"></i>
                            <span>پس از ثبت سفارش، لینک دسترسی به دوره برای شما ارسال می‌شود</span>
                        </div>
                    </div>
                    
                    <div class="summary-footer">
                        <a href="checkout.php" class="btn-checkout">
                            <i class="fas fa-lock"></i>
                            ادامه جهت تسویه حساب
                        </a>
                    </div>
                </div>
                
                <div class="security-info">
                    <div class="security-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h4>پرداخت امن</h4>
                            <p>با درگاه‌های معتبر ایرانی</p>
                        </div>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-headset"></i>
                        <div>
                            <h4>پشتیبانی ۲۴ ساعته</h4>
                            <p>پاسخگویی آنلاین</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-cart">
            <div class="empty-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>سبد خرید شما خالی است</h2>
            <p>هنوز محصولی به سبد خرید خود اضافه نکرده‌اید</p>
            <div class="empty-actions">
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-box"></i>
                    مشاهده دوره‌ها
                </a>
                <a href="../index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i>
                    بازگشت به خانه
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<style>
/* استایل‌های جدید سبد خرید */
.cart-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eaeaea;
}

.page-title {
    display: flex;
    align-items: center;
    gap: 15px;
    color: #2c3e50;
    font-size: 2rem;
}

.page-title i {
    color: #3498db;
}

.item-count {
    background: #3498db;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
}

.cart-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

@media (max-width: 992px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
}

/* کارت آیتم */
.cart-item-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    gap: 25px;
    border: 1px solid #eee;
    transition: transform 0.3s;
}

.cart-item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.item-main {
    display: flex;
    gap: 25px;
    align-items: flex-start;
}

@media (max-width: 768px) {
    .item-main {
        flex-direction: column;
    }
}

.product-image {
    position: relative;
    flex: 0 0 150px;
}

.product-image img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #eee;
}

.discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 5px;
}

.product-details {
    flex: 1;
}

.product-title {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1.3rem;
    line-height: 1.4;
}

.product-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 20px;
}

.meta-item i {
    color: #3498db;
    font-size: 0.9rem;
}

.product-price {
    margin-top: 20px;
}

.original-price {
    color: #999;
    text-decoration: line-through;
    font-size: 1.1rem;
    margin-left: 15px;
}

.current-price {
    color: #e74c3c;
    font-size: 1.5rem;
    font-weight: bold;
}

/* بخش اقدامات */
.item-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.quantity-section {
    display: flex;
    align-items: center;
    gap: 40px;
}

@media (max-width: 576px) {
    .item-actions {
        flex-direction: column;
        gap: 20px;
        align-items: flex-start;
    }
    
    .quantity-section {
        width: 100%;
        justify-content: space-between;
    }
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 10px;
}

.quantity-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: white;
    color: #3498db;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.quantity-btn:hover {
    background: #3498db;
    color: white;
    transform: scale(1.1);
}

.quantity-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 50px;
}

.quantity-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #2c3e50;
}

.quantity-label {
    font-size: 0.8rem;
    color: #666;
    margin-top: 3px;
}

.item-total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.total-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.total-price {
    color: #27ae60;
    font-size: 1.4rem;
    font-weight: bold;
}

/* دکمه حذف */
.btn-remove {
    background: #fee;
    color: #e74c3c;
    border: 1px solid #fcc;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.btn-remove:hover {
    background: #e74c3c;
    color: white;
    transform: scale(1.05);
}

.remove-text {
    display: inline;
}

@media (max-width: 576px) {
    .remove-text {
        display: none;
    }
}

/* ادامه خرید */
.cart-continue {
    margin-top: 20px;
}

.btn-continue {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    padding: 15px 25px;
    border: 2px solid #3498db;
    border-radius: 10px;
    transition: all 0.3s;
}

.btn-continue:hover {
    background: #3498db;
    color: white;
    transform: translateX(-5px);
}

/* خلاصه سفارش */
.summary-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    position: sticky;
    top: 100px;
}

.summary-header {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 25px;
    text-align: center;
}

.summary-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 1.4rem;
}

.summary-body {
    padding: 25px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row .label {
    color: #666;
    font-size: 0.95rem;
}

.summary-row .value {
    color: #2c3e50;
    font-weight: 600;
}

.summary-row.discount .value {
    color: #27ae60;
}

.summary-row.shipping .value {
    color: #3498db;
}

.summary-divider {
    height: 1px;
    background: #eee;
    margin: 20px 0;
}

.summary-row.total-row {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-top: 10px;
    border: none;
}

.total-row .label {
    font-size: 1.1rem;
    font-weight: bold;
    color: #2c3e50;
}

.total-row .value {
    font-size: 1.5rem;
    color: #e74c3c;
    font-weight: bold;
}

.summary-note {
    background: #e8f4fc;
    padding: 15px;
    border-radius: 10px;
    margin-top: 20px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: #2c3e50;
    font-size: 0.9rem;
}

.summary-note i {
    color: #3498db;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.summary-footer {
    padding: 0 25px 25px;
}

.btn-checkout {
    display: block;
    background: linear-gradient(135deg, #27ae60, #219653);
    color: white;
    text-align: center;
    padding: 18px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(39, 174, 96, 0.2);
}

.btn-checkout:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(39, 174, 96, 0.3);
    background: linear-gradient(135deg, #219653, #1e8449);
}

/* اطلاعات امنیتی */
.security-info {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-top: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.security-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.security-item:last-child {
    border-bottom: none;
}

.security-item i {
    font-size: 2rem;
    color: #3498db;
}

.security-item h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 1rem;
}

.security-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

/* سبد خرید خالی */
.empty-cart {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.empty-icon {
    font-size: 5rem;
    color: #bdc3c7;
    margin-bottom: 25px;
}

.empty-cart h2 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.empty-cart p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin-bottom: 30px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.btn-primary {
    background: #3498db;
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-3px);
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت تغییر تعداد با دکمه + و -
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item-card');
            const cartItemId = cartItem.getAttribute('data-id');
            const action = this.getAttribute('data-action');
            const quantityDisplay = cartItem.querySelector('.quantity-number');
            let quantity = parseInt(quantityDisplay.textContent);
            
            if(action === 'increase') {
                quantity++;
            } else if(action === 'decrease' && quantity > 1) {
                quantity--;
            }
            
            // نمایش سریع
            quantityDisplay.textContent = quantity;
            
            // ارسال درخواست به سرور
            updateCartQuantity(cartItemId, quantity);
        });
    });
    
    // حذف آیتم
    document.querySelectorAll('.btn-remove').forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-id');
            const cartItem = this.closest('.cart-item-card');
            
            // نمایش انیمیشن
            cartItem.style.opacity = '0.5';
            
            if(confirm('آیا از حذف این محصول مطمئن هستید؟')) {
                removeFromCart(cartId);
            } else {
                cartItem.style.opacity = '1';
            }
        });
    });
});

function updateCartQuantity(cartId, quantity) {
    // نمایش لودینگ
    const cartItem = document.querySelector(`[data-id="${cartId}"]`);
    const quantityControl = cartItem.querySelector('.quantity-control');
    quantityControl.style.opacity = '0.7';
    
    fetch('../ajax/update_cart_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_id=' + cartId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        quantityControl.style.opacity = '1';
        
        if(data.success) {
            // رفرش صفحه برای بروزرسانی قیمت کل
            location.reload();
        } else {
            showNotification('✗ ' + (data.message || 'خطا در بروزرسانی'), 'error');
            // بازگرداندن مقدار قبلی
            location.reload();
        }
    })
    .catch(error => {
        quantityControl.style.opacity = '1';
        console.error('Error:', error);
        showNotification('✗ خطا در ارتباط با سرور', 'error');
        location.reload();
    });
}

function removeFromCart(cartId) {
    fetch('../ajax/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_id=' + cartId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification('✓ محصول با موفقیت حذف شد', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showNotification('✗ ' + (data.message || 'خطا در حذف'), 'error');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('✗ خطا در ارتباط با سرور', 'error');
        location.reload();
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `cart-notification ${type}`;
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

// اضافه کردن استایل نوتیفیکیشن
const style = document.createElement('style');
style.textContent = `
.cart-notification {
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

.cart-notification.show {
    transform: translateX(0);
}

.cart-notification.success {
    border-color: #27ae60;
}

.cart-notification.error {
    border-color: #e74c3c;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.cart-notification i {
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
</script>

<?php require_once '../includes/footer.php'; ?>