<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

$message = $message_type = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    try {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$product_id]);
        $message = 'محصول با موفقیت حذف شد';
        $message_type = 'success';
    } catch(PDOException $e) {
        $message = 'خطا در حذف محصول';
        $message_type = 'error';
    }
}

try {
    $products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
} catch(PDOException $e) {
    die("خطا در دریافت محصولات: " . $e->getMessage());
}

$pageTitle = "مدیریت محصولات";
require_once '../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <div class="page-header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
            <div class="header-title">
                <h1><i class="fas fa-boxes"></i> مدیریت محصولات</h1>
                <p>مدیریت پکیج‌های آموزشی</p>
            </div>
            <a href="add-product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> محصول جدید
            </a>
        </div>

        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>لیست محصولات (<?php echo count($products); ?>)</h3>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="جستجوی محصول...">
                </div>
            </div>
            
            <div class="card-body">
                <?php if(count($products) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>محصول</th>
                                <th>قیمت</th>
                                <th>سطح</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $index => $product): ?>
                            <tr>
                                <td class="text-muted"><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="product-cell">
                                        <div class="product-img">
                                            <img src="<?php echo $product['image_url'] ? '../images/products/' . $product['image_url'] : '../images/products/default.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php if($product['discount_price']): ?>
                                                <span class="discount-badge">%<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-info">
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="text-muted"><?php echo substr(strip_tags($product['description']), 0, 50) . '...'; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($product['discount_price']): ?>
                                        <div class="price-old"><?php echo number_format($product['price']); ?> تومان</div>
                                        <div class="price-new"><?php echo number_format($product['discount_price']); ?> تومان</div>
                                    <?php else: ?>
                                        <div class="price"><?php echo number_format($product['price']); ?> تومان</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge level-<?php echo $product['level']; ?>">
                                        <?php echo htmlspecialchars($product['level']); ?>
                                    </span>
                                </td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="status <?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $product['is_active'] ? 'فعال' : 'غیرفعال'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn-icon edit" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn-icon view" title="مشاهده" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="manage-products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                           class="btn-icon delete" 
                                           title="حذف"
                                           onclick="return confirm('حذف «<?php echo htmlspecialchars($product['name']); ?>»؟')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h4>محصولی یافت نشد</h4>
                    <p>اولین محصول را اضافه کنید</p>
                    <a href="add-product.php" class="btn btn-primary">افزودن محصول</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.admin-page { padding: 20px; background: #f5f7fa; min-height: 100vh; }
.container { max-width: 1200px; margin: 0 auto; }

/* هدر */
.page-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.back-btn {
    padding: 10px 20px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: #495057;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}
.back-btn:hover { background: #e9ecef; color: #3498db; }
.header-title { flex: 1; }
.header-title h1 { margin: 0 0 5px 0; color: #2c3e50; }
.header-title p { margin: 0; color: #6c757d; }
.btn-primary {
    background: #3498db;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* پیام‌ها */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.alert-success { background: #d4edda; color: #155724; }
.alert-error { background: #f8d7da; color: #721c24; }

/* کارت */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-header h3 { margin: 0; color: #2c3e50; }
.search-box {
    position: relative;
    width: 300px;
}
.search-box input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
}
.search-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* جدول */
.card-body { padding: 0; }
.table { width: 100%; border-collapse: collapse; }
.table th { padding: 15px 20px; background: #f8f9fa; color: #495057; text-align: right; }
.table td { padding: 15px 20px; border-bottom: 1px solid #eee; }

.product-cell { display: flex; align-items: center; gap: 15px; }
.product-img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}
.product-img img { width: 100%; height: 100%; object-fit: cover; }
.discount-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #e74c3c;
    color: white;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
}
.product-info h4 { margin: 0 0 5px 0; font-size: 15px; }
.product-info p { margin: 0; font-size: 13px; }

/* قیمت */
.price { color: #2c3e50; font-weight: 600; }
.price-old { text-decoration: line-through; color: #95a5a6; font-size: 13px; }
.price-new { color: #27ae60; font-weight: 600; }

/* بج سطح */
.badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.level-مقدماتی { background: #e3f2fd; color: #1976d2; }
.level-متوسط { background: #e8f5e9; color: #388e3c; }
.level-پیشرفته { background: #fce4ec; color: #c2185b; }

/* وضعیت */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    vertical-align: middle;
    margin-left: 10px;
}
.switch input { opacity: 0; }
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #ccc;
    transition: .4s;
    border-radius: 24px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 16px; width: 16px;
    left: 4px; bottom: 4px;
    background: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider { background: #27ae60; }
input:checked + .slider:before { transform: translateX(26px); }
.status { font-size: 13px; }
.status.active { color: #27ae60; }
.status.inactive { color: #95a5a6; }

/* دکمه‌ها */
.actions { display: flex; gap: 8px; }
.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}
.edit { background: #e3f2fd; color: #1976d2; }
.edit:hover { background: #1976d2; color: white; }
.view { background: #e8f5e9; color: #388e3c; }
.view:hover { background: #388e3c; color: white; }
.delete { background: #fce4ec; color: #c2185b; }
.delete:hover { background: #c2185b; color: white; }

/* وضعیت خالی */
.empty-state {
    padding: 60px 20px;
    text-align: center;
}
.empty-state i { font-size: 48px; color: #bdc3c7; margin-bottom: 20px; }
.empty-state h4 { margin: 0 0 10px 0; color: #7f8c8d; }
.empty-state p { margin: 0 0 20px 0; color: #95a5a6; }

/* رسپانسیو */
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .search-box { width: 100%; }
    .table-responsive { overflow-x: auto; }
    .product-cell { flex-direction: column; align-items: flex-start; }
    .product-img { align-self: center; }
}
</style>

<?php require_once '../includes/footer.php'; ?>