<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// بررسی ورود کاربر
if(!isLoggedIn()) {
    header('Location: pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// دریافت اطلاعات کاربر
$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    die("خطا در اتصال به پایگاه داده");
}

// دریافت سفارشات کاربر
$orders = [];
try {
    $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    // خطا در دریافت سفارشات
}

// دریافت تعداد آیتم‌های سبد خرید
$cart_count = 0;
try {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch();
    $cart_count = $result['total'] ?: 0;
} catch(PDOException $e) {
    $cart_count = 0;
}

// پردازش فرم ویرایش پروفایل
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    
    try {
        $sql = "UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':id' => $user_id
        ]);
        
        $_SESSION['full_name'] = $full_name;
        $success = "اطلاعات با موفقیت به‌روزرسانی شد.";
        
        // بروزرسانی اطلاعات کاربر
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch();
        
    } catch(PDOException $e) {
        $error = "خطا در به‌روزرسانی اطلاعات";
    }
}

// پردازش فرم تغییر رمز عبور
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(!verifyPassword($current_password, $user['password'])) {
        $password_error = "رمز عبور فعلی نادرست است.";
    } elseif($new_password !== $confirm_password) {
        $password_error = "رمز عبور جدید و تأیید آن مطابقت ندارند.";
    } elseif(strlen($new_password) < 6) {
        $password_error = "رمز عبور باید حداقل ۶ کاراکتر باشد.";
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':password' => $hashed_password,
                ':id' => $user_id
            ]);
            
            $password_success = "رمز عبور با موفقیت تغییر یافت.";
        } catch(PDOException $e) {
            $password_error = "خطا در تغییر رمز عبور";
        }
    }
}

$pageTitle = "پروفایل کاربری";
require_once 'includes/header.php';
?>

<div class="profile-page">
    <div class="container">
        <h1 class="page-title">پروفایل کاربری</h1>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-wrapper">
            <!-- سایدبار -->
            <div class="profile-sidebar">
                <div class="user-card">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h3>
                    <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="user-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo count($orders); ?></span>
                            <span class="stat-label">سفارشات</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $cart_count; ?></span>
                            <span class="stat-label">آیتم در سبد</span>
                        </div>
                    </div>
                </div>
                
                <nav class="profile-nav">
                    <a href="#info" class="nav-item active">
                        <i class="fas fa-user"></i> اطلاعات حساب
                    </a>
                    <a href="#orders" class="nav-item">
                        <i class="fas fa-shopping-bag"></i> سفارشات من
                    </a>
                    <a href="#password" class="nav-item">
                        <i class="fas fa-lock"></i> تغییر رمز عبور
                    </a>
                    <a href="pages/cart.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i> سبد خرید
                    </a>
                    <a href="logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i> خروج
                    </a>
                </nav>
            </div>
            
            <!-- محتوای اصلی -->
            <div class="profile-content">
                <!-- بخش اطلاعات حساب -->
                <div id="info" class="profile-section active">
                    <h2><i class="fas fa-user"></i> اطلاعات حساب کاربری</h2>
                    
                    <form method="POST" class="profile-form">
                        <div class="form-group">
                            <label>نام کاربری</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>ایمیل</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">نام کامل</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">شماره تماس</label>
                                <input type="tel" name="phone" id="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>تاریخ عضویت</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo date('Y/m/d', strtotime($user['created_at'])); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>نوع حساب</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo $user['is_admin'] ? 'مدیر' : 'کاربر عادی'; ?>" readonly>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> ذخیره تغییرات
                        </button>
                    </form>
                </div>
                
                <!-- بخش سفارشات -->
                <div id="orders" class="profile-section">
                    <h2><i class="fas fa-shopping-bag"></i> سفارشات من</h2>
                    
                    <?php if(empty($orders)): ?>
                    <div class="empty-orders">
                        <i class="fas fa-shopping-bag fa-3x"></i>
                        <p>هنوز سفارشی ثبت نکرده‌اید</p>
                        <a href="pages/products.php" class="btn btn-primary">مشاهده دوره‌ها</a>
                    </div>
                    <?php else: ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>شماره سفارش</th>
                                    <th>تاریخ</th>
                                    <th>مبلغ</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_code']; ?></td>
                                    <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['total_amount']); ?> تومان</td>
                                    <td>
                                        <?php 
                                        $status_text = [
                                            'pending' => 'در انتظار',
                                            'processing' => 'در حال پردازش',
                                            'completed' => 'تکمیل شده',
                                            'cancelled' => 'لغو شده'
                                        ];
                                        echo $status_text[$order['status']];
                                        ?>
                                    </td>
                                    <td>
                                        <a href="pages/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">
                                            مشاهده
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- بخش تغییر رمز عبور -->
                <div id="password" class="profile-section">
                    <h2><i class="fas fa-lock"></i> تغییر رمز عبور</h2>
                    
                    <?php if(isset($password_error)): ?>
                    <div class="alert alert-error"><?php echo $password_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($password_success)): ?>
                    <div class="alert alert-success"><?php echo $password_success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="profile-form">
                        <div class="form-group">
                            <label for="current_password">رمز عبور فعلی</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">رمز عبور جدید</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">تأیید رمز عبور</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> تغییر رمز عبور
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 600px;
}

.page-title {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
    font-size: 2rem;
}

.profile-wrapper {
    display: flex;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-sidebar {
    flex: 0 0 280px;
}

.profile-content {
    flex: 1;
    min-width: 0;
}

.user-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.user-avatar {
    font-size: 60px;
    color: #3498db;
    margin-bottom: 15px;
}

.user-card h3 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.user-email {
    color: #7f8c8d;
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.user-stats {
    display: flex;
    justify-content: space-around;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
}

.stat-label {
    font-size: 0.85rem;
    color: #7f8c8d;
}

.profile-nav {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    color: #2c3e50;
    text-decoration: none;
    border-bottom: 1px solid #eee;
    transition: all 0.3s;
}

.nav-item:last-child {
    border-bottom: none;
}

.nav-item:hover,
.nav-item.active {
    background: #3498db;
    color: white;
}

.nav-item i {
    width: 20px;
    text-align: center;
}

.profile-section {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: none;
}

.profile-section.active {
    display: block;
}

.profile-section h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-section h2 i {
    color: #3498db;
}

.profile-form {
    max-width: 600px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.form-row .form-group {
    flex: 1;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
}

.form-control:readonly {
    background: #f5f5f5;
    cursor: not-allowed;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.9rem;
}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.empty-orders {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.empty-orders i {
    margin-bottom: 15px;
    color: #ddd;
}

.orders-table {
    overflow-x: auto;
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th,
.orders-table td {
    padding: 12px;
    text-align: right;
    border-bottom: 1px solid #eee;
}

.orders-table th {
    background: #f8f9fa;
    font-weight: bold;
    color: #2c3e50;
}

.orders-table tr:hover {
    background: #f8f9fa;
}

@media (max-width: 768px) {
    .profile-wrapper {
        flex-direction: column;
    }
    
    .profile-sidebar {
        flex: none;
        width: 100%;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .orders-table table {
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت کلیک روی منوی سایدبار
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.profile-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if(this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // حذف کلاس active از همه
                navItems.forEach(i => i.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                // افزودن کلاس active به آیتم جاری
                this.classList.add('active');
                
                // نمایش بخش مربوطه
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                if(targetSection) {
                    targetSection.classList.add('active');
                }
            }
        });
    });
    
    // اعتبارسنجی فرم تغییر رمز عبور
    const passwordForm = document.querySelector('form[name="change_password"]');
    if(passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if(newPass !== confirmPass) {
                e.preventDefault();
                alert('رمز عبور جدید و تأیید آن مطابقت ندارند.');
                return false;
            }
            
            if(newPass.length < 6) {
                e.preventDefault();
                alert('رمز عبور باید حداقل ۶ کاراکتر باشد.');
                return false;
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>