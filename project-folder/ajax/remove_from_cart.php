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
if(!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'آیتم نامعتبر است'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);

try {
    // حذف آیتم از سبد خرید
    $sql = "DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cart_id' => $cart_id,
        ':user_id' => $user_id
    ]);
    
    if($stmt->rowCount() > 0) {
        // دریافت تعداد باقیمانده
        $sql = "SELECT SUM(quantity) as total_count FROM cart WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'محصول از سبد خرید حذف شد',
            'count' => $result['total_count'] ?? 0
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'آیتم یافت نشد'
        ]);
    }
    
} catch(PDOException $e) {
    error_log("Remove Cart Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در حذف از سبد خرید'
    ]);
}
?>