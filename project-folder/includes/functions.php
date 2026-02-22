<?php
// تابع بررسی ورود کاربر
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// تابع بررسی ادمین بودن
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

// تابع هش کردن رمز عبور
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// تابع بررسی رمز عبور
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// تابع ایجاد کد سفارش منحصر به فرد
function generateOrderCode() {
    return 'ORD-' . strtoupper(uniqid());
}

// تابع محاسبه جمع سبد خرید
function calculateCartTotal($user_id) {
    global $pdo;
    
    $sql = "SELECT SUM(p.price * c.quantity) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetch();
    
    return $result['total'] ?: 0;
}

// تابع نمایش پیام‌ها
function displayMessage() {
    if(isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        
        echo "<div class='alert alert-$type'>$message</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// تابع اعتبارسنجی ایمیل
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// تابع جلوگیری از حمله XSS
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// تابع آپلود تصویر
function uploadImage($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // بررسی اینکه فایل تصویر است
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'فایل انتخاب شده تصویر نیست.'];
    }
    
    // بررسی سایز فایل (حداکثر ۵MB)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'حجم فایل نباید بیشتر از ۵ مگابایت باشد.'];
    }
    
    // بررسی فرمت فایل
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return ['success' => false, 'message' => 'فقط فایل‌های JPG, JPEG, PNG مجاز هستند.'];
    }
    
    // تغییر نام فایل برای جلوگیری از تداخل
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_path = $target_dir . $new_filename;
    
    // آپلود فایل
    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'خطا در آپلود فایل.'];
    }
    // تابع بررسی ادمین بودن
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}
}
?>