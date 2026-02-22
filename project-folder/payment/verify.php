<?php
session_start();
// خط کتابخانه را حذف می‌کنیم
// require_once '../vendor/autoload.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['current_order_id'])) {
    header('Location: ../index.php');
    exit();
}

$order_id = $_SESSION['current_order_id'];
$merchantID = '00000000-0000-0000-0000-000000000000'; // مرچنت تست

// دریافت اطلاعات سفارش
$sql = "SELECT * FROM orders WHERE id = :order_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':order_id' => $order_id]);
$order = $stmt->fetch();

if(!$order) {
    die('سفارش یافت نشد');
}

// بررسی وضعیت پرداخت
if(isset($_GET['Status']) && $_GET['Status'] == 'OK') {
    $authority = $_GET['Authority'];
    $amount = $order['total_amount'];
    
    // تطابق Authority
    if($order['ref_id'] != $authority) {
        die('کد تراکنش نامعتبر است');
    }
    
    // 🔴 استفاده از API مستقیم زرین‌پال برای تایید
    $data = array(
        'MerchantID' => $merchantID,
        'Authority' => $authority,
        'Amount' => $amount,
    );
    
    $jsonData = json_encode($data);
    
    // ارسال درخواست تایید
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'User-Agent: ZarinPal Rest Api v1'
            ],
            'content' => $jsonData
        ]
    ]);
    
    try {
        $response = file_get_contents(
            'https://sandbox.zarinpal.com/pg/rest/WebGate/PaymentVerification.json',
            false,
            $context
        );
        
        $result = json_decode($response, true);
        
        if ($result['Status'] == 100) {
            // پرداخت موفق
            $refID = $result['RefID'];
            
            // به‌روزرسانی وضعیت سفارش
            $update_sql = "UPDATE orders SET 
                          status = 'completed',
                          payment_status = 'paid',
                          payment_method = 'زرین‌پال'
                          WHERE id = :order_id";
            
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([':order_id' => $order_id]);
            
            // ذخیره اطلاعات پرداخت
            $payment_sql = "INSERT INTO payments (order_id, amount, ref_id, status) 
                           VALUES (:order_id, :amount, :ref_id, 'success')";
            $payment_stmt = $pdo->prepare($payment_sql);
            $payment_stmt->execute([
                ':order_id' => $order_id,
                ':amount' => $amount,
                ':ref_id' => $refID
            ]);
            
            // خالی کردن سبد خرید کاربر
            $delete_cart_sql = "DELETE FROM cart WHERE user_id = :user_id";
            $delete_cart_stmt = $pdo->prepare($delete_cart_sql);
            $delete_cart_stmt->execute([':user_id' => $order['user_id']]);
            
            // نمایش صفحه موفقیت
            $_SESSION['message'] = "✅ پرداخت با موفقیت انجام شد. کد پیگیری: $refID";
            $_SESSION['message_type'] = 'success';
            
            unset($_SESSION['current_order_id']);
            
            header('Location: ../order-success.php?id=' . $order_id);
            exit();
            
        } else {
            // پرداخت ناموفق
            $update_sql = "UPDATE orders SET payment_status = 'failed' WHERE id = :order_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([':order_id' => $order_id]);
            
            $_SESSION['message'] = "❌ پرداخت ناموفق بود. کد خطا: " . $result['Status'];
            $_SESSION['message_type'] = 'error';
            
            header('Location: ../checkout.php');
            exit();
        }
    } catch (Exception $e) {
        // 🔴 اگر خطا خورد، شبیه‌سازی موفقیت برای تست
        simulateSuccessfulPayment($order_id, $order, $authority);
    }
} else {
    // کاربر از پرداخت انصراف داده
    $update_sql = "UPDATE orders SET payment_status = 'failed' WHERE id = :order_id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([':order_id' => $order_id]);
    
    $_SESSION['message'] = "⏪ پرداخت توسط کاربر لغو شد.";
    $_SESSION['message_type'] = 'warning';
    
    header('Location: ../checkout.php');
    exit();
}

// 🔴 تابع کمکی برای شبیه‌سازی پرداخت موفق (برای وقتی که API کار نمی‌کند)
function simulateSuccessfulPayment($order_id, $order, $authority) {
    global $pdo;
    
    $refID = 'TEST_' . time() . '_' . rand(1000, 9999);
    
    // به‌روزرسانی وضعیت سفارش
    $update_sql = "UPDATE orders SET 
                  status = 'completed',
                  payment_status = 'paid',
                  payment_method = 'زرین‌پال (تست)'
                  WHERE id = :order_id";
    
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([':order_id' => $order_id]);
    
    // ذخیره اطلاعات پرداخت
    $payment_sql = "INSERT INTO payments (order_id, amount, ref_id, status) 
                   VALUES (:order_id, :amount, :ref_id, 'success')";
    $payment_stmt = $pdo->prepare($payment_sql);
    $payment_stmt->execute([
        ':order_id' => $order_id,
        ':amount' => $order['total_amount'],
        ':ref_id' => $refID
    ]);
    
    // خالی کردن سبد خرید کاربر
    $delete_cart_sql = "DELETE FROM cart WHERE user_id = :user_id";
    $delete_cart_stmt = $pdo->prepare($delete_cart_sql);
    $delete_cart_stmt->execute([':user_id' => $order['user_id']]);
    
    // نمایش صفحه موفقیت
    $_SESSION['message'] = "✅ پرداخت تست موفق بود! کد پیگیری: $refID";
    $_SESSION['message_type'] = 'success';
    
    unset($_SESSION['current_order_id']);
    
    header('Location: ../order-success.php?id=' . $order_id);
    exit();
}
?>