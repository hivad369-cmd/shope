<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// اگر کاربر از قبل وارد شده، به صفحه اصلی هدایت شود
if(isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

// بررسی ارسال فرم
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    try {
        // بررسی وجود کاربر
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if($user && verifyPassword($password, $user['password'])) {
            // ایجاد سشن
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // هدایت به صفحه مورد نظر یا صفحه اصلی
            if(isset($_GET['redirect'])) {
                header('Location: ' . urldecode($_GET['redirect']));
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $error = 'ایمیل یا رمز عبور اشتباه است';
        }
    } catch(PDOException $e) {
        $error = 'خطا در ورود به سیستم';
    }
}

$pageTitle = "ورود به حساب کاربری";
require_once '../includes/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-grid">
            <!-- کارت ورود -->
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h1>ورود به حساب کاربری</h1>
                    <p>اطلاعات حساب خود را وارد کنید</p>
                </div>
                
                <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            آدرس ایمیل
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="example@email.com"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            رمز عبور
                        </label>
                        <div class="password-input">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="رمز عبور خود را وارد کنید"
                                   required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            مرا به خاطر بسپار
                        </label>
                        <a href="forgot-password.php" class="forgot-link">
                            <i class="fas fa-key"></i>
                            رمز عبور را فراموش کرده‌ام
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i>
                        ورود به حساب
                    </button>
                    
                    <div class="divider">
                        <span>یا</span>
                    </div>
                    
                    <a href="register.php" class="btn btn-outline btn-block">
                        <i class="fas fa-user-plus"></i>
                        ساخت حساب جدید
                    </a>
                </form>
            </div>
            
            <!-- مزایای حساب کاربری -->
            <div class="benefits-card">
                <div class="benefits-header">
                    <h2><i class="fas fa-crown"></i> مزایای حساب کاربری</h2>
                    <p>با داشتن حساب کاربری از امکانات ویژه برخوردار شوید</p>
                </div>
                
                <div class="benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>مدیریت سفارشات</h4>
                            <p>پیگیری و مدیریت تمام سفارشات خود</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>دسترسی دائمی</h4>
                            <p>دسترسی همیشگی به دوره‌های خریداری شده</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>تخفیف‌های ویژه</h4>
                            <p>دریافت تخفیف‌های اختصاصی اعضا</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>پشتیبانی VIP</h4>
                            <p>پشتیبانی اولویت‌دار برای اعضا</p>
                        </div>
                    </div>
                </div>
                
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-number">۲,۵۰۰+</span>
                        <span class="stat-label">دانشجو</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">۵۰+</span>
                        <span class="stat-label">دوره آموزشی</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">۹۸٪</span>
                        <span class="stat-label">رضایت‌مندی</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل کلی */
.auth-page {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 40px 0;
    display: flex;
    align-items: center;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* گرید صفحه */
.auth-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: start;
}

@media (max-width: 992px) {
    .auth-grid {
        grid-template-columns: 1fr;
        max-width: 500px;
        margin: 0 auto;
    }
}

/* کارت ورود */
.auth-card {
    background: white;
    border-radius: 12px;
    padding: 35px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border: 1px solid #eaeaea;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    margin: 0 auto 20px;
}

.auth-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.6rem;
}

.auth-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.95rem;
}

/* پیام‌ها */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert i {
    font-size: 1.2rem;
}

/* فرم */
.auth-form {
    margin-top: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 500;
    font-size: 0.95rem;
}

.form-group label i {
    color: #3498db;
    font-size: 1rem;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
    background: #f8f9fa;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    background: white;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.password-input {
    position: relative;
}

.toggle-password {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    font-size: 1rem;
    padding: 5px;
}

.toggle-password:hover {
    color: #3498db;
}

/* گزینه‌ها */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 10px;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #2c3e50;
    font-size: 0.9rem;
}

.checkbox input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 4px;
    position: relative;
    transition: all 0.3s;
}

.checkbox input:checked + .checkmark {
    background: #3498db;
    border-color: #3498db;
}

.checkbox input:checked + .checkmark::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.forgot-link {
    color: #3498db;
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s;
}

.forgot-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

/* دکمه‌ها */
.btn {
    padding: 14px 20px;
    border-radius: 8px;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s;
    text-decoration: none;
}

.btn-block {
    width: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2980b9, #1c6ea4);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-outline {
    background: white;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

/* جداکننده */
.divider {
    position: relative;
    text-align: center;
    margin: 25px 0;
    color: #7f8c8d;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #ddd;
}

.divider span {
    background: white;
    padding: 0 15px;
    position: relative;
    font-size: 0.9rem;
}

/* کارت مزایا */
.benefits-card {
    background: white;
    border-radius: 12px;
    padding: 35px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border: 1px solid #eaeaea;
    height: 100%;
}

.benefits-header {
    text-align: center;
    margin-bottom: 30px;
}

.benefits-header h2 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.benefits-header h2 i {
    color: #f39c12;
}

.benefits-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.95rem;
}

/* لیست مزایا */
.benefits-list {
    margin-bottom: 30px;
}

.benefit-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.benefit-item:last-child {
    border-bottom: none;
}

.benefit-icon {
    width: 45px;
    height: 45px;
    background: #e8f4fc;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.benefit-content h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 1rem;
}

.benefit-content p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* آمار */
.stats {
    display: flex;
    justify-content: space-around;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-top: 25px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 5px;
}

.stat-label {
    display: block;
    color: #7f8c8d;
    font-size: 0.85rem;
}

/* رسپانسیو */
@media (max-width: 576px) {
    .auth-page {
        padding: 20px 0;
    }
    
    .auth-card, .benefits-card {
        padding: 25px;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-number {
        margin-bottom: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // نمایش/مخفی کردن رمز عبور
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // تغییر آیکون
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.className = 'fas fa-eye-slash';
                this.title = 'مخفی کردن رمز عبور';
            } else {
                icon.className = 'fas fa-eye';
                this.title = 'نمایش رمز عبور';
            }
        });
    }
    
    // اعتبارسنجی فرم
    const form = document.querySelector('.auth-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let isValid = true;
            
            // ریست کردن خطاها
            [email, password].forEach(input => {
                input.style.borderColor = '#ddd';
                const errorMsg = input.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            });
            
            // اعتبارسنجی ایمیل
            if (!email.value.trim()) {
                showError(email, 'ایمیل الزامی است');
                isValid = false;
            } else if (!isValidEmail(email.value)) {
                showError(email, 'ایمیل معتبر نیست');
                isValid = false;
            }
            
            // اعتبارسنجی رمز عبور
            if (!password.value.trim()) {
                showError(password, 'رمز عبور الزامی است');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // نمایش خطا
    function showError(input, message) {
        input.style.borderColor = '#e74c3c';
        
        // ایجاد پیام خطا
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.style.display = 'flex';
        errorDiv.style.alignItems = 'center';
        errorDiv.style.gap = '5px';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        input.parentNode.appendChild(errorDiv);
    }
    
    // اعتبارسنجی ایمیل
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // انیمیشن ورود
    const authCard = document.querySelector('.auth-card');
    const benefitsCard = document.querySelector('.benefits-card');
    
    if (authCard) {
        authCard.style.opacity = '0';
        authCard.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            authCard.style.transition = 'opacity 0.5s, transform 0.5s';
            authCard.style.opacity = '1';
            authCard.style.transform = 'translateX(0)';
        }, 100);
    }
    
    if (benefitsCard) {
        benefitsCard.style.opacity = '0';
        benefitsCard.style.transform = 'translateX(20px)';
        
        setTimeout(() => {
            benefitsCard.style.transition = 'opacity 0.5s, transform 0.5s';
            benefitsCard.style.opacity = '1';
            benefitsCard.style.transform = 'translateX(0)';
        }, 200);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>