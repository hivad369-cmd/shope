<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit();
}

$user_id = $_SESSION['user_id'];

// بررسی اگر محصول مستقیم اضافه شده
if(isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    // حذف سبد خرید قبلی
    $sql = "DELETE FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    
    // اضافه کردن محصول جدید
    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
}

try {
    // دریافت آیتم‌های سبد خرید
    $sql = "SELECT c.id as cart_id, c.quantity, p.* 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $cart_items = $stmt->fetchAll();
    
    if(count($cart_items) == 0) {
        header('Location: cart.php');
        exit();
    }
    
    // دریافت اطلاعات کاربر
    $sql = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch();
    
    // محاسبه جمع کل
    $total = 0;
    foreach($cart_items as $item) {
        $price = $item['discount_price'] ?: $item['price'];
        $total += $price * $item['quantity'];
    }
    
} catch(PDOException $e) {
    die("خطا در دریافت اطلاعات: " . $e->getMessage());
}

$pageTitle = "تسویه حساب";
require_once '../includes/header.php';
?>

<div class="checkout-page">
    <div class="container">
        <h1 class="page-title">تسویه حساب</h1>
        
        <div class="checkout-steps">
            <div class="step active">
                <span class="step-number">1</span>
                <span class="step-title">اطلاعات خریدار</span>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <span class="step-title">پرداخت</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span class="step-title">تکمیل خرید</span>
            </div>
        </div>
        
        <div class="checkout-content">
            <div class="checkout-form-section">
                <form id="checkout-form" method="POST" action="../payment/process.php">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> اطلاعات شخصی</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">نام و نام خانوادگی *</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                                       class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">ایمیل *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="form-control" required readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">شماره تماس *</label>
                                <input type="text" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">آدرس *</label>
                                <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-credit-card"></i> روش پرداخت</h3>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="zarinpal" name="payment_method" value="zarinpal" checked>
                                <label for="zarinpal">
                                    <img src="../images/zarinpal-logo.png" alt="زرین‌پال">
                                    <span>پرداخت آنلاین زرین‌پال</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success btn-pay">
                            <i class="fas fa-lock"></i> پرداخت و تکمیل سفارش
                        </button>
                        <a href="cart.php" class="btn btn-outline">بازگشت به سبد خرید</a>
                    </div>
                </form>
            </div>
            
            <div class="order-summary-section">
                <div class="order-summary">
                    <h3>خلاصه سفارش</h3>
                    
                    <div class="order-items">
                        <?php foreach($cart_items as $item): 
                            $item_price = $item['discount_price'] ?: $item['price'];
                            $item_total = $item_price * $item['quantity'];
                        ?>
                        <div class="order-item">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <span class="item-quantity">تعداد: <?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="item-price"><?php echo number_format($item_total); ?> تومان</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>جمع کل:</span>
                            <span><?php echo number_format($total); ?> تومان</span>
                        </div>
                        <div class="total-row">
                            <span>تخفیف:</span>
                            <span>۰ تومان</span>
                        </div>
                        <div class="total-row final">
                            <span>مبلغ قابل پرداخت:</span>
                            <span class="final-amount"><?php echo number_format($total); ?> تومان</span>
                        </div>
                    </div>
                </div>
                
                <div class="secure-payment">
                    <i class="fas fa-shield-alt"></i>
                    <p>پرداخت امن با زرین‌پال</p>
                    <small>اطلاعات شما کاملاً محرمانه می‌ماند</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // اعتبارسنجی فرم
        const fullName = document.getElementById('full_name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        
        if(!fullName || !phone || !address) {
            showToast('لطفا تمام فیلدهای ضروری را پر کنید', 'error');
            return;
        }
        
        // نمایش لودینگ
        showLoading();
        
        // ارسال فرم
        this.submit();
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>