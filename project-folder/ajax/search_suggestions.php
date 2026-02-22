<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_connect.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'حداقل ۲ حرف وارد کنید']);
    exit();
}

try {
    $sql = "SELECT name 
            FROM products 
            WHERE is_active = 1 
            AND (name LIKE :query OR description LIKE :query)
            GROUP BY name
            ORDER BY name ASC
            LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':query' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'suggestions' => $results
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطا در جستجو'
    ]);
}
?>