<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// بررسی دسترسی ادمین
if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

$message = '';
$message_type = '';

// مقادیر پیش‌فرض
$name = $description = $price = $discount_price = $image_url = $duration = $level = '';
$features = [];
$is_active = 1;

// پردازش فرم ارسال شده
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // دریافت و اعتبارسنجی داده‌ها
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $duration = trim($_POST['duration']);
    $level = trim($_POST['level']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // تبدیل ویژگی‌ها به JSON
    $features_array = [];

    // بررسی وجود feature_values در POST
    if (isset($_POST['feature_names']) && is_array($_POST['feature_values'])) {
        foreach ($_POST['feature_names'] as $feature) {
            $feature = trim($feature);
            if (!empty($feature)) {
                $features_array[] =  $feature;  
            }
        }
    }
    $features_json = !empty($features_array) ? json_encode($features_array, JSON_UNESCAPED_UNICODE) : null;
    
    // اعتبارسنجی
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'نام محصول الزامی است';
    }
    
    if (empty($price) || $price <= 0) {
        $errors[] = 'قیمت محصول باید بزرگتر از صفر باشد';
    }
    
    if ($discount_price && $discount_price >= $price) {
        $errors[] = 'قیمت تخفیف باید کمتر از قیمت اصلی باشد';
    }
    
    // آپلود تصویر
    $uploaded_image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['image'], '../images/products/');
        if ($upload_result['success']) {
            $uploaded_image = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // اگر خطایی نبود، ذخیره در دیتابیس
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (name, description, price, discount_price, image_url, duration, level, features, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $description,
                $price,
                $discount_price,
                $uploaded_image,
                $duration,
                $level,
                $features_json,
                $is_active
            ]);
            
            $message = 'محصول با موفقیت افزوده شد';
            $message_type = 'success';
            
            // ریست فرم
            $name = $description = $price = $discount_price = $image_url = $duration = $level = '';
            $features = [];
            $is_active = 1;
            
        } catch(PDOException $e) {
            $message = 'خطا در افزودن محصول: ' . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}

$pageTitle = "افزودن محصول جدید";
require_once '../includes/header.php';
?>

<div class="admin-page">
    <div class="container">
        <!-- دکمه برگشت -->
        <div class="page-actions">
            <a href="manage-products.php" class="back-btn">
                <i class="fas fa-arrow-right"></i> بازگشت به لیست محصولات
            </a>
        </div>
        
        <!-- هدر صفحه -->
        <div class="admin-header">
            <div class="header-title">
                <h1 class="page-title">افزودن محصول جدید</h1>
                <p class="page-subtitle">ایجاد پکیج آموزشی جدید</p>
            </div>
        </div>
        
        <!-- پیام‌ها -->
        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <!-- فرم افزودن محصول -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">اطلاعات محصول</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">نام محصول *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($name); ?>" required>
                            <small class="form-text">نام کامل دوره آموزشی</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="level">سطح دوره *</label>
                            <select id="level" name="level" class="form-control" required>
                                <option value="">انتخاب سطح</option>
                                <option value="مقدماتی" <?php echo $level == 'مقدماتی' ? 'selected' : ''; ?>>مقدماتی</option>
                                <option value="متوسط" <?php echo $level == 'متوسط' ? 'selected' : ''; ?>>متوسط</option>
                                <option value="پیشرفته" <?php echo $level == 'پیشرفته' ? 'selected' : ''; ?>>پیشرفته</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">توضیحات محصول</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                        <small class="form-text">توضیحات کامل درباره دوره</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">قیمت اصلی (تومان) *</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   value="<?php echo $price; ?>" min="0" step="1000" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="discount_price">قیمت با تخفیف (تومان)</label>
                            <input type="number" id="discount_price" name="discount_price" class="form-control" 
                                   value="<?php echo $discount_price; ?>" min="0" step="1000">
                            <small class="form-text">در صورت عدم تخفیف خالی بگذارید</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">مدت زمان دوره</label>
                            <input type="text" id="duration" name="duration" class="form-control" 
                                   value="<?php echo htmlspecialchars($duration); ?>" placeholder="مثال: ۲۰ ساعت">
                        </div>
                    </div>
                    
                    <!-- ویژگی‌های محصول -->
                    <div class="form-group">
                        <label>ویژگی‌های دوره</label>
                        <div class="features-container" id="features-container">
                            <div class="feature-item">
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 2;">
                                        <input type="text" name="feature_values[]" class="form-control" placeholder="مثال: ۲۰ ساعت ویدیو آموزشی">
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-sm btn-danger remove-feature" onclick="removeFeature(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline" onclick="addFeature()">
                            <i class="fas fa-plus"></i> افزودن ویژگی
                        </button>
                    </div>
                    
                    <!-- آپلود تصویر -->
                    <div class="form-group">
                        <label for="image">تصویر محصول</label>
                        <div class="image-upload">
                            <input type="file" id="image" name="image" class="form-control-file" accept="image/*">
                            <small class="form-text">حداکثر ۵MB - فرمت‌های مجاز: JPG, JPEG, PNG</small>
                        </div>
                        <div class="image-preview" id="image-preview"></div>
                    </div>
                    
                    <!-- وضعیت فعال بودن -->
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" <?php echo $is_active ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            محصول فعال باشد
                        </label>
                    </div>
                    
                    <!-- دکمه‌های فرم -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> ذخیره محصول
                        </button>
                        <button type="reset" class="btn btn-outline">
                            <i class="fas fa-redo"></i> ریست فرم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.admin-page {
    padding: 20px;
    background: #f8fafc;
    min-height: 100vh;
}

.container {
    max-width: 900px;
    margin: 0 auto;
}

/* دکمه برگشت */
.page-actions {
    margin-bottom: 20px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    color: #2c3e50;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    border: 1px solid #dee2e6;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.back-btn:hover {
    background: #e9ecef;
    border-color: #bdc3c7;
    transform: translateX(-5px);
    color: #3498db;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* هدر */
.admin-header {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.page-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8rem;
}

.page-subtitle {
    margin: 8px 0 0;
    color: #7f8c8d;
    font-size: 0.95rem;
}

/* کارت فرم */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    overflow: hidden;
    margin-bottom: 30px;
}

.card-header {
    padding: 18px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.card-title {
    margin: 0;
    font-size: 1.2rem;
    color: #2c3e50;
}

.card-body {
    padding: 30px;
}

/* فرم */
.product-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-control-file {
    width: 100%;
    padding: 12px 0;
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 0.85rem;
    color: #7f8c8d;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

/* ویژگی‌ها */
.features-container {
    margin-bottom: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
}

.feature-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.feature-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.remove-feature {
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-feature:hover {
    background: #c0392b;
    transform: scale(1.05);
}

/* آپلود تصویر */
.image-upload {
    margin-bottom: 15px;
}

.image-preview {
    margin-top: 15px;
    padding: 15px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    text-align: center;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 6px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

/* چک‌باکس */
.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    padding: 10px 0;
}

.checkbox-label input {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    margin-left: 10px;
    position: relative;
    transition: all 0.3s ease;
}

.checkbox-label input:checked + .checkmark {
    background: #27ae60;
    border-color: #27ae60;
}

.checkbox-label input:checked + .checkmark:after {
    content: '';
    position: absolute;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* دکمه‌های فرم */
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

.btn-outline {
    background: white;
    color: #2c3e50;
    border: 1px solid #dee2e6;
}

.btn-outline:hover {
    background: #f8f9fa;
    border-color: #bdc3c7;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 0.9rem;
}

/* هشدار */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid transparent;
}

.alert-success {
    background-color: #e8f8f0;
    color: #27ae60;
    border-color: #abebc6;
}

.alert-error {
    background-color: #fdedec;
    color: #e74c3c;
    border-color: #fadbd8;
}

/* ویژگی‌ها - برای نسخه تک فیلدی */
.feature-item .form-group[style*="grid-column: span 2"] {
    grid-column: span 2;
}
/* ریسپانسیو */
@media (max-width: 768px) {
    .feature-item .form-group[style*="grid-column: span 2"] {
        grid-column: span 1;
    }
    .container {
        padding: 0 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .card-body {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .admin-header {
        padding: 15px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .back-btn {
        width: 100%;
        justify-content: center;
        padding: 10px 15px;
    }
}
</style>

<script>
// اضافه کردن ویژگی جدید
function addFeature() {
    const container = document.getElementById('features-container');
    const newFeature = document.createElement('div');
    newFeature.className = 'feature-item';
    newFeature.innerHTML = `
        <div class="form-row">
            <div class="form-group" style="grid-column: span 2;">
                <input type="text" name="feature_values[]" class="form-control" placeholder="عنوان ویژگی">
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-sm btn-danger remove-feature" onclick="removeFeature(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newFeature);
}

// حذف ویژگی
function removeFeature(button) {
    const featureItem = button.closest('.feature-item');
    if (document.querySelectorAll('.feature-item').length > 1) {
        featureItem.remove();
    } else {
        // اگر تنها ویژگی است، فقط محتوای آن را خالی کن
        const inputs = featureItem.querySelectorAll('input');
        inputs.forEach(input => input.value = '');
    }
}

// پیش‌نمایش تصویر
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="پیش‌نمایش تصویر">`;
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '<p>تصویری انتخاب نشده است</p>';
    }
});

// اعتبارسنجی قیمت تخفیف
document.getElementById('discount_price').addEventListener('input', function() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const discount = parseFloat(this.value) || 0;
    
    if (discount > 0 && discount >= price) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
    } else {
        this.style.borderColor = '#ddd';
        this.style.boxShadow = 'none';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>