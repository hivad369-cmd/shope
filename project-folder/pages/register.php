<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if(isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    // اعتبارسنجی
    if(empty($username) || strlen($username) < 3) $errors[] = 'نام کاربری باید حداقل ۳ کاراکتر باشد';
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'ایمیل معتبر وارد کنید';
    if(strlen($password) < 6) $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
    elseif($password !== $confirm_password) $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند';
    
    if(empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if($stmt->rowCount() > 0) $errors[] = 'ایمیل یا نام کاربری از قبل ثبت شده است';
        } catch(PDOException $e) {
            $errors[] = 'خطا در بررسی اطلاعات';
        }
    }
    
    if(empty($errors)) {
        try {
            $hashed_password = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $full_name, $phone]);
            $success = 'ثبت‌نام با موفقیت انجام شد. می‌توانید وارد شوید.';
            header('refresh:3;url=login.php');
        } catch(PDOException $e) {
            $errors[] = 'خطا در ثبت‌نام';
        }
    }
}

$pageTitle = "ثبت‌نام کاربر جدید";
require_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-page {background: #f8f9fa; min-height: 100vh; padding: 40px 0;}
        .container {max-width: 1200px; margin: 0 auto; padding: 0 20px;}
        .auth-grid {display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;}
        @media (max-width: 992px) {.auth-grid {grid-template-columns: 1fr; max-width: 600px; margin: 0 auto;}}
        
        .auth-card, .benefits-card {
            background: white; border-radius: 12px; padding: 35px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: 1px solid #eaeaea;
            animation: slideIn 0.6s ease-out;
        }
        
        .auth-header {text-align: center; margin-bottom: 30px;}
        .auth-icon {
            width: 70px; height: 70px; background: linear-gradient(135deg, #27ae60, #219653);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.8rem; margin: 0 auto 20px;
        }
        
        .alert {
            padding: 15px 20px; border-radius: 8px; margin-bottom: 25px;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .alert-error {background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;}
        .alert-success {background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;}
        
        .auth-form {margin-top: 25px;}
        .form-row {display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;}
        @media (max-width: 768px) {.form-row {grid-template-columns: 1fr;}}
        
        .form-control {
            width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 0.95rem; transition: all 0.3s; background: #f8f9fa;
        }
        .form-control:focus {
            outline: none; border-color: #3498db; background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .password-input {position: relative;}
        .toggle-password {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #7f8c8d; cursor: pointer;
            font-size: 1rem; padding: 5px;
        }
        
        .btn {
            padding: 14px 20px; border-radius: 8px; border: none; font-size: 1rem;
            font-weight: 600; cursor: pointer; display: flex; align-items: center;
            justify-content: center; gap: 10px; transition: all 0.3s; text-decoration: none;
        }

        .btn-outline {
            background: white;
            color: #3498db;
            border: 2px solid #3498db;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
        }

        .btn-block {
            width: 100%;
            width: 100%;
            box-sizing: border-box;
        }
        .btn-primary {
            background: linear-gradient(135deg, #27ae60, #219653); color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #219653, #1e8449);
            transform: translateY(-2px); box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }

        .btn i {
            margin-left: 8px;
            font-size: 14px;
        }
        
        .divider {position: relative; text-align: center; margin: 25px 0; color: #7f8c8d;}
        .divider::before {content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #ddd;}
        .divider span {background: white; padding: 0 15px; position: relative; font-size: 0.9rem;}
        
        .benefits-list {display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;}
        @media (max-width: 1200px) {.benefits-list {grid-template-columns: 1fr;}}
        
        .benefit-item {
            display: flex; gap: 15px; padding: 15px; background: #f8f9fa;
            border-radius: 10px; border: 1px solid #eee; transition: all 0.3s;
        }
        .benefit-item:hover {border-color: #3498db; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05);}
        
        .benefit-icon {
            width: 45px; height: 45px; background: #e8f4fc; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #3498db; font-size: 1.2rem; flex-shrink: 0;
        }
        
        .terms-group {margin: 25px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #eaeaea;}
        
        @keyframes slideIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="container">
        <div class="auth-grid">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon"><i class="fas fa-user-plus"></i></div>
                    <h1>ایجاد حساب کاربری</h1>
                    <p>فرم زیر را پر کنید تا حساب شما ساخته شود</p>
                </div>
                
                <?php if(!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="error-content">
                        <strong>لطفا موارد زیر را اصلاح کنید:</strong>
                        <ul><?php foreach($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div class="success-content">
                        <strong>موفقیت!</strong>
                        <p><?= htmlspecialchars($success) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form" id="registerForm">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> اطلاعات شخصی</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name"><i class="fas fa-id-card"></i> نام کامل</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       placeholder="نام و نام خانوادگی"
                                       value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> شماره تماس</label>
                                <input type="text" id="phone" name="phone" class="form-control" 
                                       placeholder="09xxxxxxxxx"
                                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-key"></i> اطلاعات ورود</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user-circle"></i> نام کاربری *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       placeholder="حداقل ۳ کاراکتر" required
                                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> ایمیل *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       placeholder="example@email.com" required
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> رمز عبور *</label>
                                <div class="password-input">
                                    <input type="password" id="password" name="password" class="form-control" 
                                           placeholder="حداقل ۶ کاراکتر" required>
                                    <button type="button" class="toggle-password" data-target="password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-lock"></i> تکرار رمز عبور *</label>
                                <div class="password-input">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                           placeholder="تکرار رمز عبور" required>
                                    <button type="button" class="toggle-password" data-target="confirm_password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group terms-group">
                        <label class="checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <span class="checkmark"></span>
                            <span>با <a href="terms.php">قوانین و مقررات</a> سایت موافقم *</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> ایجاد حساب کاربری
                    </button>
                    
                    <div class="divider"><span>یا</span></div>
                    <a href="login.php" class="btn btn-outline btn-block">
                        <i class="fas fa-sign-in-alt"></i> ورود به حساب کاربری
                    </a>
                </form>
            </div>
            
            <div class="benefits-card">
                <div class="benefits-header">
                    <h2><i class="fas fa-star"></i> مزایای عضویت</h2>
                    <p>با عضویت در سایت از امکانات ویژه برخوردار شوید</p>
                </div>
                
                <div class="benefits-list">
                    <?php 
                    $benefits = [
                        ['icon' => 'fa-shopping-cart', 'title' => 'خرید آسان و سریع', 'desc' => 'تجربه خریدی راحت و بدون دردسر'],
                        ['icon' => 'fa-download', 'title' => 'دسترسی دائمی', 'desc' => 'دسترسی همیشگی به دوره‌های خریداری شده'],
                        ['icon' => 'fa-gift', 'title' => 'تخفیف‌های ویژه', 'desc' => 'دریافت کدهای تخفیف اختصاصی برای اعضا'],
                        ['icon' => 'fa-headset', 'title' => 'پشتیبانی اولویت‌دار', 'desc' => 'پشتیبانی سریع و اختصاصی برای اعضا']
                    ];
                    foreach($benefits as $benefit): ?>
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas <?= $benefit['icon'] ?>"></i></div>
                        <div class="benefit-content">
                            <h4><?= $benefit['title'] ?></h4>
                            <p><?= $benefit['desc'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // نمایش/مخفی کردن رمز عبور
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const input = document.getElementById(target);
            const icon = this.querySelector('i');
            
            if(input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                icon.className = type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
                this.title = type === 'text' ? 'مخفی کردن رمز عبور' : 'نمایش رمز عبور';
            }
        });
    });
    
    // اعتبارسنجی فرم
    const form = document.getElementById('registerForm');
    if(form) {
        form.addEventListener('submit', function(e) {
            const errors = [];
            
            // بررسی نام کاربری
            const username = document.getElementById('username');
            if(!username.value.trim() || username.value.length < 3) {
                errors.push('نام کاربری باید حداقل ۳ کاراکتر باشد');
                username.style.borderColor = '#e74c3c';
            }
            
            // بررسی ایمیل
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!email.value.trim() || !emailRegex.test(email.value)) {
                errors.push('ایمیل معتبر نیست');
                email.style.borderColor = '#e74c3c';
            }
            
            // بررسی رمز عبور
            const password = document.getElementById('password');
            const confirmPass = document.getElementById('confirm_password');
            if(!password.value.trim() || password.value.length < 6) {
                errors.push('رمز عبور باید حداقل ۶ کاراکتر باشد');
                password.style.borderColor = '#e74c3c';
            } else if(password.value !== confirmPass.value) {
                errors.push('رمز عبور و تکرار آن مطابقت ندارند');
                confirmPass.style.borderColor = '#e74c3c';
            }
            
            // بررسی قوانین
            const terms = document.getElementById('terms');
            if(!terms.checked) {
                errors.push('پذیرش قوانین الزامی است');
                terms.style.outline = '2px solid #e74c3c';
            }
            
            if(errors.length > 0) {
                e.preventDefault();
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-error';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="error-content">
                        <strong>لطفا موارد زیر را اصلاح کنید:</strong>
                        <ul>${errors.map(e => `<li>${e}</li>`).join('')}</ul>
                    </div>
                `;
                
                const existing = document.querySelector('.alert');
                if(existing) existing.replaceWith(errorDiv);
                else document.querySelector('.auth-card').insertBefore(errorDiv, document.querySelector('.auth-form'));
                
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        });
    }
    
    // اعتبارسنجی بلادرنگ
    const emailInput = document.getElementById('email');
    if(emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#e74c3c';
                if(!this.nextElementSibling?.classList.contains('error-message')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.style.cssText = 'color:#e74c3c;font-size:0.85rem;margin-top:5px;';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ایمیل معتبر نیست';
                    this.parentNode.appendChild(errorDiv);
                }
            } else {
                this.style.borderColor = '#ddd';
                const errorMsg = this.parentNode.querySelector('.error-message');
                if(errorMsg) errorMsg.remove();
            }
        });
    }
});
</script>

</body>
</html>