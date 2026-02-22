<?php
session_start();
include 'includes/db_connect.php';
include 'includes/functions.php';
$pageTitle = "فروشگاه پکیج آموزش زبان انگلیسی";
include 'includes/header.php';

// دریافت محصولات جدید و پرفروش
$sql_new = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8";
$stmt_new = $pdo->prepare($sql_new);
$stmt_new->execute();
$new_products = $stmt_new->fetchAll();

$sql_best = "SELECT p.*, SUM(oi.quantity) as total_sold 
             FROM products p 
             LEFT JOIN order_items oi ON p.id = oi.product_id 
             WHERE p.is_active = 1 
             GROUP BY p.id 
             ORDER BY total_sold DESC 
             LIMIT 6";
$stmt_best = $pdo->prepare($sql_best);
$stmt_best->execute();
$best_products = $stmt_best->fetchAll();
?>


<?php
// نمایش ماژول‌ها به ترتیب
include 'includes/modules/hero_section.php';
include 'includes/modules/search_module.php';
include 'includes/modules/categories_section.php';
include 'includes/modules/new_products_slider.php';
include 'includes/modules/best_sellers_section.php';
//include 'includes/modules/cta_section.php';
?>

<!-- اسکریپت‌های اختصاصی -->
<script src="js/slider.js"></script> 
<script src="js/cart.js"></script>
<script src="js/homepage.js"></script>
<?php include 'includes/footer.php'; ?>