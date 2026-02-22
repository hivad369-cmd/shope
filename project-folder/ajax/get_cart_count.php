<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// اگر کاربر لاگین نیست، صفر برگردان
if(!isLoggedIn()) {
    echo json_encode([
        'success' => true,
        'count' => 0
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT SUM(quantity) as total_count FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'count' => $result['total_count'] ?? 0
    ]);
    
} catch(PDOException $e) {
    error_log("Cart Count Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'count' => 0,
        'message' => 'خطا در دریافت تعداد سبد خرید'
    ]);
}
?>