<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// بررسی دسترسی ادمین
if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) header('Location: manage-products.php');

// دریافت محصول
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if (!$product) header('Location: manage-products.php');
} catch(PDOException $e) {
    die("خطا در دریافت محصول");
}

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'price' => floatval($_POST['price']),
        'discount_price' => !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null,
        'duration' => trim($_POST['duration']),
        'level' => trim($_POST['level']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // ویژگی‌ها
    $features = [];
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        foreach ($_POST['features'] as $feature) {
            if (trim($feature)) $features[] = trim($feature);
        }
    }
    $data['features'] = json_encode($features, JSON_UNESCAPED_UNICODE);
    
    // تصویر
    $data['image_url'] = $product['image_url'];
    if (isset($_FILES['image']['error']) && $_FILES['image']['error'] == 0) {
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        
        if (in_array($file_type, $allowed)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'product_' . time() . '.' . $ext;
            $upload_dir = '../images/products/';
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                if ($data['image_url'] && file_exists($upload_dir . $data['image_url'])) {
                    unlink($upload_dir . $data['image_url']);
                }
                $data['image_url'] = $new_filename;
            }
        }
    }
    
    try {
        $sql = "UPDATE products SET 
                name = :name,
                description = :description,
                price = :price,
                discount_price = :discount_price,
                image_url = :image_url,
                duration = :duration,
                level = :level,
                features = :features,
                is_active = :is_active
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $data['id'] = $product_id;
        $stmt->execute($data);
        
        $message = '✅ محصول ویرایش شد';
        $message_type = 'success';
        
        // دریافت مجدد
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
    } catch(PDOException $e) {
        $message = '❌ خطا: ' . $e->getMessage();
        $message_type = 'error';
    }
}

$features = json_decode($product['features'], true) ?: [];
$pageTitle = "ویرایش محصول";
require_once '../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <!-- هدر -->
        <div class="page-header">
            <a href="manage-products.php" class="btn-back">
                <i class="fas fa-arrow-right"></i>
            </a>
            <h1>ویرایش محصول</h1>
            <a href="../pages/product-detail.php?id=<?php echo $product_id; ?>" 
               class="btn-view" target="_blank">
                <i class="fas fa-eye"></i>
            </a>
        </div>

        <?php if(isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- فرم -->
        <form class="edit-form" method="POST" enctype="multipart/form-data">
            
            <!-- اطلاعات -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>اطلاعات محصول</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>نام محصول *</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>سطح *</label>
                            <select name="level" required>
                                <option value="مقدماتی" <?php if($product['level'] == 'مقدماتی') echo 'selected'; ?>>مقدماتی</option>
                                <option value="متوسط" <?php if($product['level'] == 'متوسط') echo 'selected'; ?>>متوسط</option>
                                <option value="پیشرفته" <?php if($product['level'] == 'پیشرفته') echo 'selected'; ?>>پیشرفته</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>مدت زمان *</label>
                            <input type="text" name="duration" value="<?php echo htmlspecialchars($product['duration']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>توضیحات *</label>
                            <textarea name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قیمت -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tag"></i>
                    <h3>قیمت‌گذاری</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>قیمت اصلی *</label>
                            <input type="number" name="price" value="<?php echo $product['price']; ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>قیمت تخفیف</label>
                            <input type="number" name="discount_price" value="<?php echo $product['discount_price']; ?>" min="0">
                            <small>خالی = بدون تخفیف</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تصویر -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-image"></i>
                    <h3>تصویر</h3>
                </div>
                <div class="card-body">
                    <div class="image-section">
                        <div class="current-img">
                            <img src="../images/products/<?php echo $product['image_url'] ?: 'default.jpg'; ?>" 
                                 onerror="this.src='../images/products/default.jpg'">
                        </div>
                        <div class="upload-box">
                            <label class="upload-btn">
                                <i class="fas fa-upload"></i>
                                <span>انتخاب تصویر جدید</span>
                                <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
                            </label>
                            <small>JPG, PNG, WebP - حداکثر ۲MB</small>
                            <div class="preview" id="preview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ویژگی‌ها -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i>
                    <h3>ویژگی‌ها</h3>
                </div>
                <div class="card-body">
                    <div id="features-container">
                        <?php foreach($features as $feature): ?>
                        <div class="feature">
                            <input type="text" name="features[]" value="<?php echo htmlspecialchars($feature); ?>">
                            <button type="button" class="btn-remove" onclick="removeFeature(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($features)): ?>
                        <div class="feature">
                            <input type="text" name="features[]" placeholder="ویژگی">
                            <button type="button" class="btn-remove" onclick="removeFeature(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-add" onclick="addFeature()">
                        <i class="fas fa-plus"></i> افزودن ویژگی
                    </button>
                </div>
            </div>

            <!-- وضعیت -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cog"></i>
                    <h3>وضعیت</h3>
                </div>
                <div class="card-body">
                    <label class="toggle">
                        <input type="checkbox" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                        <span>محصول فعال</span>
                    </label>
                    <small>محصول غیرفعال در سایت نمایش داده نمی‌شود</small>
                </div>
            </div>

            <!-- دکمه‌ها -->
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> ذخیره تغییرات
                </button>
                <a href="manage-products.php" class="btn">
                    <i class="fas fa-times"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.admin-page {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 0 auto;
}

/* هدر */
.page-header {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.btn-back {
    width: 40px;
    height: 40px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    text-decoration: none;
}

.btn-back:hover {
    background: #3b82f6;
    color: white;
}

.page-header h1 {
    flex: 1;
    font-size: 1.3rem;
    color: #1e293b;
}

.btn-view {
    width: 40px;
    height: 40px;
    background: #e0f2fe;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0ea5e9;
    text-decoration: none;
}

.btn-view:hover {
    background: #0ea5e9;
    color: white;
}

/* پیام */
.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* کارت */
.card {
    background: white;
    border-radius: 12px;
    margin-bottom: 15px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.card-header {
    padding: 15px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header i {
    color: #3b82f6;
}

.card-header h3 {
    font-size: 1rem;
    color: #1e293b;
}

.card-body {
    padding: 20px;
}

/* فرم */
.form-grid {
    display: grid;
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    color: #475569;
    font-size: 0.9rem;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #f8fafc;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #94a3b8;
    font-size: 0.8rem;
}

/* تصویر */
.image-section {
    display: flex;
    gap: 20px;
    align-items: start;
}

.current-img {
    width: 150px;
    height: 150px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.current-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-box {
    flex: 1;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    color: #475569;
    padding: 10px 15px;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 8px;
    border: 1px solid #cbd5e1;
}

.upload-btn:hover {
    background: #e2e8f0;
}

.upload-btn input {
    display: none;
}

#preview {
    margin-top: 10px;
    display: none;
}

#preview img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
}

/* ویژگی‌ها */
#features-container {
    margin-bottom: 15px;
}

.feature {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.feature input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.9rem;
}

.btn-remove {
    width: 35px;
    height: 35px;
    background: #f1f5f9;
    border: none;
    border-radius: 6px;
    color: #64748b;
    cursor: pointer;
}

.btn-remove:hover {
    background: #fee2e2;
    color: #dc2626;
}

.btn-add {
    background: #f1f5f9;
    border: 1px dashed #cbd5e1;
    color: #475569;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-add:hover {
    background: #e2e8f0;
}

/* وضعیت */
.toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    margin-bottom: 8px;
}

.toggle input {
    display: none;
}

.toggle-slider {
    width: 44px;
    height: 24px;
    background: #cbd5e1;
    border-radius: 12px;
    position: relative;
    transition: background 0.3s;
}

.toggle-slider:before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.toggle input:checked + .toggle-slider {
    background: #3b82f6;
}

.toggle input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

/* دکمه‌ها */
.form-footer {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    flex: 1;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn {
    background: #f1f5f9;
    color: #475569;
}

.btn:hover {
    background: #e2e8f0;
}

/* رسپانسیو */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .page-header {
        padding: 12px;
    }
    
    .image-section {
        flex-direction: column;
    }
    
    .current-img {
        width: 100%;
        max-width: 200px;
        margin: 0 auto;
    }
    
    .card-body {
        padding: 15px;
    }
}
</style>

<script>
// پیش‌نمایش تصویر
function previewImage(input) {
    const preview = document.getElementById('preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}">`;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
        preview.style.display = 'none';
    }
}

// ویژگی‌ها
function addFeature() {
    const container = document.getElementById('features-container');
    const div = document.createElement('div');
    div.className = 'feature';
    div.innerHTML = `
        <input type="text" name="features[]" placeholder="ویژگی">
        <button type="button" class="btn-remove" onclick="removeFeature(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeFeature(btn) {
    const container = document.getElementById('features-container');
    if (container.children.length > 1) {
        btn.parentNode.remove();
    } else {
        btn.previousElementSibling.value = '';
    }
}

// اعتبارسنجی
document.querySelector('form').addEventListener('submit', function(e) {
    const price = this.querySelector('[name="price"]');
    const discount = this.querySelector('[name="discount_price"]');
    
    // قیمت
    if (!price.value || parseFloat(price.value) <= 0) {
        e.preventDefault();
        alert('قیمت باید بیشتر از صفر باشد');
        price.focus();
        return;
    }
    
    // تخفیف
    if (discount.value) {
        const priceVal = parseFloat(price.value);
        const discountVal = parseFloat(discount.value);
        
        if (discountVal <= 0) {
            e.preventDefault();
            alert('قیمت تخفیف باید بیشتر از صفر باشد');
            discount.focus();
            return;
        }
        
        if (discountVal >= priceVal) {
            e.preventDefault();
            alert('قیمت تخفیف باید کمتر از قیمت اصلی باشد');
            discount.focus();
            return;
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>