<?php
// تابع محاسبه مسیر پایه
function getBasePath() {
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $levels = substr_count($path, '/') - 1;
    $base = '';
    for ($i = 0; $i < $levels; $i++) {
        $base .= '../';
    }
    return $base ?: './';
}

$base = getBasePath();

// شروع session فقط اگر قبلاً شروع نشده باشد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>English Master</title>
    <link rel="stylesheet" href="<?php echo $base; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo $base; ?>css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/slider.css">
</head>
<body>
    <!-- نوار بالایی -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> ۰۲۱-۱۲۳۴۵۶۷۸</span>
                <span><i class="fas fa-envelope"></i> info@english-courses.ir</span>
            </div>
            <div class="auth-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- نمایش لینک مدیریت برای ادمین -->
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <a href="<?php echo $base; ?>admin/dashboard.php" class="admin-link">
                            <i class="fas fa-cog"></i> مدیریت
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $base; ?>profile.php">
                        <i class="fas fa-user"></i> پروفایل
                    </a>
                    <a href="<?php echo $base; ?>logout.php">
                        <i class="fas fa-sign-out-alt"></i> خروج
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base; ?>pages/login.php">
                        <i class="fas fa-sign-in-alt"></i> ورود
                    </a>
                    <a href="<?php echo $base; ?>pages/register.php">
                        <i class="fas fa-user-plus"></i> ثبت‌نام
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- هدر اصلی -->
    <header class="main-header">
        <div class="container">
            <div class="logo-container">
                <div class="logo-header">
                    <i class="fas fa-graduation-cap"></i>
                    <div class="logo-text">
                        <h1><a href="<?php echo $base; ?>index.php">English Master</a></h1>
                        <p>پکیج‌های آموزشی زبان انگلیسی</p>
                    </div>
                </div>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo $base; ?>index.php" 
                           <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
                           <i class="fas fa-home"></i> خانه
                        </a></li>
                    <li><a href="<?php echo $base; ?>pages/products.php"
                           <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>
                           <i class="fas fa-box"></i> پکیج‌ها
                        </a></li>
                    <li><a href="<?php echo $base; ?>about.php"
                           <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'class="active"' : ''; ?>>
                           <i class="fas fa-info-circle"></i> درباره ما
                        </a></li>
                    <li><a href="<?php echo $base; ?>contact.php"
                           <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'class="active"' : ''; ?>>
                           <i class="fas fa-phone-alt"></i> تماس با ما
                        </a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <a href="<?php echo $base; ?>pages/cart.php" class="cart-icon" title="سبد خرید">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">
                        <?php
                        if(isset($_SESSION['user_id'])) {
                            try {
                                require_once $base . 'includes/db_connect.php';
                                $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetchColumn() ?: 0;
                            } catch(Exception $e) {
                                echo '0';
                            }
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </a>
            </div>
        </div>
    </header>

<style>
/* استایل لوگوی هدر */
.logo-container {
    display: flex;
    align-items: center;
}

.logo-header {
    display: flex;
    align-items: center;
    gap: 15px;
    text-decoration: none;
}

.logo-header i {
    font-size: 2.5rem;
    color: #3498db;
    background: white;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
    transition: all 0.3s ease;
}

.logo-header:hover i {
    transform: rotate(-10deg) scale(1.1);
    background: #3498db;
    color: white;
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-text h1 {
    margin: 0;
    font-size: 1.8rem;
}

.logo-text h1 a {
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.3s ease;
}

.logo-text h1 a:hover {
    color: #3498db;
}

.logo-text p {
    margin: 5px 0 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

/* استایل برای موبایل */
@media (max-width: 768px) {
    .logo-header {
        gap: 10px;
    }
    
    .logo-header i {
        font-size: 2rem;
        padding: 10px;
    }
    
    .logo-text h1 {
        font-size: 1.5rem;
    }
    
    .logo-text p {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .logo-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .logo-header i {
        font-size: 1.8rem;
        padding: 8px;
    }
}
</style>
<!-- دکمه شناور برگشت به بالا -->
<button id="backToTop" class="back-to-top" aria-label="برگشت به بالا">
    <i class="fas fa-chevron-up"></i>
</button>

    <!-- بخش اصلی محتوا -->
    <main class="main-content">