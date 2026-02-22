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
if(!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id']) || 
   !isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'اطلاعات نامعتبر است'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);
$quantity = intval($_POST['quantity']);

// بررسی مقدار quantity
if($quantity < 1) {
    $quantity = 1;
}

try {
    // بررسی مالکیت آیتم
    $sql = "SELECT id FROM cart WHERE id = :cart_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cart_id' => $cart_id,
        ':user_id' => $user_id
    ]);
    
    if($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'آیتم یافت نشد'
        ]);
        exit();
    }
    
    // به‌روزرسانی تعداد
    $sql = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':quantity' => $quantity,
        ':cart_id' => $cart_id
    ]);
    
    // محاسبه جمع کل جدید
    $sql = "SELECT SUM(p.price * c.quantity) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'تعداد به‌روزرسانی شد',
        'total' => $result['total'] ?? 0
    ]);
    
} catch(PDOException $e) {
    error_log("Update Cart Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در به‌روزرسانی تعداد'
    ]);
}
?>