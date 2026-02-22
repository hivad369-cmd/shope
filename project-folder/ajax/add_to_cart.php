<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// بررسی لاگین بودن کاربر
if(!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'لطفاً ابتدا وارد حساب کاربری خود شوید'
    ]);
    exit();
}

// بررسی پارامترهای ورودی
if(!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'محصول نامعتبر است'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);

try {
    // بررسی وجود محصول
    $sql = "SELECT id, price FROM products WHERE id = :id AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'محصول یافت نشد'
        ]);
        exit();
    }
    
    // بررسی وجود محصول در سبد خرید
    $sql = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);
    $existing_item = $stmt->fetch();
    
    if($existing_item) {
        // افزایش تعداد
        $new_quantity = $existing_item['quantity'] + 1;
        $sql = "UPDATE cart SET quantity = :quantity, added_at = NOW() WHERE id = :cart_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':quantity' => $new_quantity,
            ':cart_id' => $existing_item['id']
        ]);
    } else {
        // اضافه کردن آیتم جدید
        $sql = "INSERT INTO cart (user_id, product_id, quantity, added_at) 
                VALUES (:user_id, :product_id, 1, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id
        ]);
    }
    
    // دریافت تعداد کل آیتم‌های سبد خرید
    $sql = "SELECT SUM(quantity) as total_count FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'محصول به سبد خرید اضافه شد',
        'count' => $result['total_count'] ?? 0
    ]);
    
} catch(PDOException $e) {
    error_log("Cart Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در افزودن به سبد خرید'
    ]);
}
?>