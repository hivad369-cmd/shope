<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    header('Location: ../login.php?redirect=checkout');
    exit();
}

// دریافت اطلاعات سبد خرید کاربر
$user_id = $_SESSION['user_id'];
$total_amount = calculateCartTotal($user_id);

if($total_amount <= 0) {
    header('Location: ../cart.php');
    exit();
}

// 🔴 بخش ایجاد سفارش که جا افتاده بود:
try {
    $order_code = generateOrderCode();
    
    $sql = "INSERT INTO orders (order_code, user_id, total_amount, payment_status) 
            VALUES (:order_code, :user_id, :total_amount, 'pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':order_code' => $order_code,
        ':user_id' => $user_id,
        ':total_amount' => $total_amount
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // ذخیره آیتم‌های سفارش
    $cart_sql = "SELECT c.*, p.price FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = :user_id";
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute([':user_id' => $user_id]);
    $cart_items = $cart_stmt->fetchAll();
    
    foreach($cart_items as $item) {
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                     VALUES (:order_id, :product_id, :quantity, :price)";
        $item_stmt = $pdo->prepare($item_sql);
        $item_stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price']
        ]);
    }
    
    $_SESSION['current_order_id'] = $order_id;
    
} catch(PDOException $e) {
    die("خطا در ایجاد سفارش: " . $e->getMessage());
}

// استفاده از API مستقیم زرین‌پال
$merchantID = '00000000-0000-0000-0000-000000000000'; // مرچنت تست

$data = array(
    'MerchantID' => $merchantID,
    'Amount' => $total_amount,
    'CallbackURL' => 'http://localhost/project-folder/payment/verify.php',
    'Description' => 'خرید پکیج آموزش زبان انگلیسی - کد سفارش: ' . $order_code,
);

// 🔴 ارسال مستقیم با file_get_contents (بدون cURL)
$jsonData = json_encode($data);

// گزینه ۱: با file_get_contents (اگر allow_url_fopen روشن باشد)
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
        'https://sandbox.zarinpal.com/pg/rest/WebGate/PaymentRequest.json',
        false,
        $context
    );
    
    $result = json_decode($response, true);
    
    if ($result["Status"] == 100) {
        // ذخیره Authority در دیتابیس
        $authority = $result["Authority"];
        
        $update_sql = "UPDATE orders SET ref_id = :authority WHERE id = :order_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            ':authority' => $authority,
            ':order_id' => $order_id
        ]);
        
        // هدایت به درگاه پرداخت
        header('Location: https://sandbox.zarinpal.com/pg/StartPay/' . $authority);
        exit();
    } else {
        echo 'خطا در اتصال به درگاه. کد خطا: ' . $result["Status"];
        echo '<br><a href="../cart.php">بازگشت به سبد خرید</a>';
    }
    
} catch (Exception $e) {
    // 🔴 اگر file_get_contents کار نکرد، با فرم مستقیم برویم
    echo '<h3>راه‌حل جایگزین:</h3>';
    echo '<p>درگاه مستقیم زرین‌پال</p>';
    
    // نمایش فرم مستقیم
    echo '<form action="https://sandbox.zarinpal.com/pg/StartPay/' . ($authority ?? '') . '" method="get">';
    echo '<input type="hidden" name="Amount" value="' . $total_amount . '">';
    echo '<input type="hidden" name="MerchantID" value="' . $merchantID . '">';
    echo '<input type="hidden" name="Description" value="خرید پکیج آموزش زبان انگلیسی">';
    echo '<input type="hidden" name="CallbackURL" value="http://localhost/project-folder/payment/verify.php">';
    echo '<button type="submit">ورود به درگاه پرداخت</button>';
    echo '</form>';
}
?>