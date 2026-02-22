<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    header('Location: profile.php');
    exit();
}

// بررسی وجود سفارش و مالکیت
try {
    $sql = "SELECT o.*, COUNT(oi.id) as item_count 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.id = ? AND o.user_id = ? AND o.status = 'completed' 
            GROUP BY o.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: profile.php');
        exit();
    }
    
    // دریافت محصولات سفارش
    $sql = "SELECT p.*, oi.quantity 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    $products = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("خطا در دریافت اطلاعات سفارش");
}

$pageTitle = "دریافت فایل‌ها";
require_once '../includes/header.php';
?>

<div class="download-page">
    <div class="container">
        <!-- هدر -->
        <div class="page-header">
            <a href="order-details.php?id=<?php echo $order_id; ?>" class="btn-back">
                <i class="fas fa-arrow-right"></i>
            </a>
            <div class="header-info">
                <h1>دریافت فایل‌ها</h1>
                <p class="order-info">سفارش: <strong>#<?php echo $order['order_code']; ?></strong></p>
            </div>
        </div>

        <!-- پیام موفقیت -->
        <div class="success-message">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="success-content">
                <h3>سفارش شما با موفقیت پرداخت شده است!</h3>
                <p>هم اکنون می‌توانید فایل‌های دوره‌های آموزشی را دریافت کنید.</p>
            </div>
        </div>

        <!-- اطلاعات دانلود -->
        <div class="download-info">
            <div class="info-card">
                <i class="fas fa-info-circle"></i>
                <div class="info-content">
                    <h4>نکات مهم:</h4>
                    <ul>
                        <li>فایل‌ها به صورت فشرده (ZIP) ارائه شده‌اند</li>
                        <li>حجم کل فایل‌ها: حدود 2.5 گیگابایت</li>
                        <li>پس از دانلود، فایل را با نرم‌افزار WinRAR یا 7-Zip از حالت فشرده خارج کنید</li>
                        <li>در صورت مشکل در دانلود، با پشتیبانی تماس بگیرید</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- لیست دوره‌ها -->
        <div class="downloads-section">
            <div class="section-header">
                <h2><i class="fas fa-download"></i> دوره‌های قابل دانلود</h2>
                <span class="count"><?php echo count($products); ?> دوره</span>
            </div>

            <div class="downloads-grid">
                <?php foreach($products as $product): ?>
                <div class="download-card">
                    <div class="card-header">
                        <div class="course-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="course-info">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <div class="course-meta">
                                <span class="badge level-<?php echo strtolower($product['level']); ?>">
                                    <?php echo htmlspecialchars($product['level']); ?>
                                </span>
                                <span class="duration">
                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($product['duration']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="file-info">
                            <div class="file-item">
                                <i class="fas fa-file-video"></i>
                                <div>
                                    <span class="file-name">فیلم‌های آموزشی</span>
                                    <span class="file-size">1.2 GB</span>
                                </div>
                            </div>
                            <div class="file-item">
                                <i class="fas fa-file-pdf"></i>
                                <div>
                                    <span class="file-name">جزوات PDF</span>
                                    <span class="file-size">150 MB</span>
                                </div>
                            </div>
                            <div class="file-item">
                                <i class="fas fa-file-code"></i>
                                <div>
                                    <span class="file-name">تمرین‌ها</span>
                                    <span class="file-size">50 MB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button class="btn-download" data-course="<?php echo $product['id']; ?>">
                            <i class="fas fa-download"></i> دانلود کامل دوره
                        </button>
                        <button class="btn-parts" onclick="showParts(<?php echo $product['id']; ?>)">
                            <i class="fas fa-list"></i> دانلود تکی
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- بخش دانلود تکی -->
        <div class="parts-modal" id="partsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-list"></i> دانلود بخش‌های دوره</h3>
                    <button class="modal-close" onclick="hideParts()">×</button>
                </div>
                <div class="modal-body" id="partsList">
                    <!-- لیست بخش‌ها -->
                </div>
            </div>
        </div>

        <!-- راهنمای نصب -->
        <div class="guide-section">
            <div class="section-header">
                <h2><i class="fas fa-question-circle"></i> راهنمای نصب و استفاده</h2>
            </div>
            
            <div class="guide-steps">
                <div class="step">
                    <div class="step-number">۱</div>
                    <div class="step-content">
                        <h4>دانلود فایل‌ها</h4>
                        <p>دوره مورد نظر را انتخاب و دانلود کنید</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">۲</div>
                    <div class="step-content">
                        <h4>خارج کردن از حالت فشرده</h4>
                        <p>با نرم‌افزار WinRAR یا 7-Zip فایل را Extract کنید</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">۳</div>
                    <div class="step-content">
                        <h4>پخش فیلم‌ها</h4>
                        <p>برای پخش فیلم‌ها از VLC Media Player استفاده کنید</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">۴</div>
                    <div class="step-content">
                        <h4>شروع یادگیری</h4>
                        <p>به ترتیب سرفصل‌ها پیش بروید</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- پشتیبانی -->
        <div class="support-section">
            <div class="support-card">
                <i class="fas fa-headset"></i>
                <div class="support-content">
                    <h4>نیاز به کمک دارید؟</h4>
                    <p>در صورت بروز مشکل در دانلود یا پخش فایل‌ها، با پشتیبانی تماس بگیرید</p>
                    <div class="support-actions">
                        <a href="mailto:support@english-courses.com" class="btn-support">
                            <i class="fas fa-envelope"></i> ایمیل پشتیبانی
                        </a>
                        <a href="tel:+982100000000" class="btn-support">
                            <i class="fas fa-phone"></i> تماس تلفنی
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل کلی */
.download-page {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

/* هدر */
.page-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-back {
    width: 45px;
    height: 45px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #3b82f6;
    color: white;
    transform: translateX(-3px);
}

.header-info h1 {
    margin: 0;
    font-size: 1.5rem;
    color: #1e293b;
}

.order-info {
    margin-top: 5px;
    color: #64748b;
    font-size: 0.95rem;
}

/* پیام موفقیت */
.success-message {
    background: linear-gradient(135deg, #27ae60, #219653);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.success-icon {
    font-size: 2.5rem;
}

.success-content h3 {
    margin: 0 0 8px 0;
    font-size: 1.3rem;
}

.success-content p {
    margin: 0;
    opacity: 0.9;
}

/* اطلاعات دانلود */
.download-info {
    margin-bottom: 25px;
}

.info-card {
    background: #e8f4fc;
    border: 1px solid #c4e1ff;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.info-card i {
    color: #3b82f6;
    font-size: 1.5rem;
    flex-shrink: 0;
    margin-top: 3px;
}

.info-content h4 {
    margin: 0 0 10px 0;
    color: #1e293b;
}

.info-content ul {
    margin: 0;
    padding-right: 20px;
    color: #475569;
}

.info-content li {
    margin-bottom: 5px;
    line-height: 1.5;
}

/* لیست دوره‌ها */
.downloads-section {
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-header h2 {
    font-size: 1.2rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h2 i {
    color: #3b82f6;
}

.count {
    background: #f1f5f9;
    color: #475569;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

/* کارت دانلود */
.downloads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

@media (max-width: 768px) {
    .downloads-grid {
        grid-template-columns: 1fr;
    }
}

.download-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.download-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.card-header {
    background: #f8fafc;
    padding: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
}

.course-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.course-info h4 {
    margin: 0 0 8px 0;
    color: #1e293b;
    font-size: 1.1rem;
}

.course-meta {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.level-مقدماتی {
    background: #dcfce7;
    color: #166534;
}

.level-متوسط {
    background: #fef3c7;
    color: #92400e;
}

.level-پیشرفته {
    background: #f3e8ff;
    color: #6b21a8;
}

.duration {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #64748b;
    font-size: 0.85rem;
}

.duration i {
    font-size: 0.8rem;
}

.card-body {
    padding: 20px;
}

.file-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 8px;
    transition: background 0.3s;
}

.file-item:hover {
    background: #e2e8f0;
}

.file-item i {
    color: #3b82f6;
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
}

.file-name {
    display: block;
    color: #1e293b;
    font-weight: 500;
    margin-bottom: 3px;
}

.file-size {
    display: block;
    color: #64748b;
    font-size: 0.85rem;
}

.card-footer {
    padding: 0 20px 20px;
    display: flex;
    gap: 10px;
}

.btn-download, .btn-parts {
    padding: 10px 15px;
    border-radius: 8px;
    border: none;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex: 1;
    transition: all 0.3s;
}

.btn-download {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.btn-download:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-2px);
}

.btn-parts {
    background: white;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.btn-parts:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

/* مودال بخش‌ها */
.parts-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 100%;
    max-height: 80vh;
    overflow: hidden;
}

.modal-header {
    background: #3b82f6;
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.8rem;
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.part-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.part-item:last-child {
    border-bottom: none;
}

.part-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.part-icon {
    color: #3b82f6;
    font-size: 1.2rem;
}

.part-details h5 {
    margin: 0 0 5px 0;
    color: #1e293b;
    font-size: 0.95rem;
}

.part-size {
    color: #64748b;
    font-size: 0.85rem;
}

.btn-part-download {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: background 0.3s;
}

.btn-part-download:hover {
    background: #2563eb;
}

/* راهنما */
.guide-section {
    margin-bottom: 30px;
}

.guide-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.step {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.step-number {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h4 {
    margin: 0 0 8px 0;
    color: #1e293b;
    font-size: 1rem;
}

.step-content p {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* پشتیبانی */
.support-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.support-card i {
    color: #3b82f6;
    font-size: 2rem;
    flex-shrink: 0;
}

.support-content h4 {
    margin: 0 0 10px 0;
    color: #1e293b;
}

.support-content p {
    margin: 0 0 20px 0;
    color: #475569;
    line-height: 1.6;
}

.support-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-support {
    background: #f1f5f9;
    color: #475569;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-support:hover {
    background: #3b82f6;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت دانلود
    const downloadButtons = document.querySelectorAll('.btn-download');
    downloadButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course');
            startDownload(courseId, 'full');
        });
    });
    
    // مدیریت دانلود تکی
    document.querySelectorAll('.btn-part-download').forEach(btn => {
        btn.addEventListener('click', function() {
            const partId = this.getAttribute('data-part');
            startDownload(null, 'part', partId);
        });
    });
    
    // تایمر دانلود
    let downloadTimers = {};
    
    // نمایش پیام شروع دانلود
    function showDownloadMessage(type = 'full') {
        const messages = {
            'full': 'دانلود دوره شروع شد. لطفاً منتظر بمانید...',
            'part': 'دانلود بخش شروع شد. لطفاً منتظر بمانید...'
        };
        
        const message = messages[type] || 'دانلود شروع شد. لطفاً منتظر بمانید...';
        alert(message);
    }
});

// دانلود کامل
function startDownload(courseId, type, partId = null) {
    showDownloadMessage(type);
    
    // شبیه‌سازی دانلود
    const downloadBtn = document.querySelector(`[data-course="${courseId}"]`);
    if (downloadBtn) {
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال دانلود...';
        downloadBtn.disabled = true;
        
        // شبیه‌سازی تاخیر دانلود
        setTimeout(() => {
            downloadBtn.innerHTML = '<i class="fas fa-check"></i> دانلود تکمیل شد';
            downloadBtn.style.background = '#27ae60';
            
            setTimeout(() => {
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
                downloadBtn.style.background = '';
                
                // نمایش پیام موفقیت
                showNotification('✅ دانلود با موفقیت انجام شد', 'success');
            }, 2000);
        }, 3000);
    }
}

// نمایش لیست بخش‌ها
function showParts(courseId) {
    const parts = [
        {id: 1, name: 'فصل ۱: مقدمه و آشنایی', size: '150 MB', icon: 'fas fa-play-circle'},
        {id: 2, name: 'فصل ۲: گرامر پایه', size: '200 MB', icon: 'fas fa-book'},
        {id: 3, name: 'فصل ۳: مکالمه روزمره', size: '300 MB', icon: 'fas fa-comments'},
        {id: 4, name: 'فصل ۴: تمرین‌ها و آزمون', size: '100 MB', icon: 'fas fa-clipboard-check'},
        {id: 5, name: 'فصل ۵: منابع تکمیلی', size: '150 MB', icon: 'fas fa-file-pdf'}
    ];
    
    let partsHTML = '';
    parts.forEach(part => {
        partsHTML += `
            <div class="part-item">
                <div class="part-info">
                    <i class="${part.icon} part-icon"></i>
                    <div class="part-details">
                        <h5>${part.name}</h5>
                        <span class="part-size">${part.size}</span>
                    </div>
                </div>
                <button class="btn-part-download" onclick="startDownload(${courseId}, 'part', ${part.id})">
                    <i class="fas fa-download"></i> دانلود
                </button>
            </div>
        `;
    });
    
    document.getElementById('partsList').innerHTML = partsHTML;
    document.getElementById('partsModal').style.display = 'flex';
}

// مخفی کردن مودال
function hideParts() {
    document.getElementById('partsModal').style.display = 'none';
}

// نمایش نوتیفیکیشن
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// استایل نوتیفیکیشن
const style = document.createElement('style');
style.textContent = `
.notification {
    position: fixed;
    bottom: 30px;
    left: 30px;
    background: white;
    padding: 15px 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-right: 4px solid;
}

.notification.show {
    transform: translateX(0);
}

.notification.success {
    border-color: #27ae60;
}

.notification.error {
    border-color: #e74c3c;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.notification i {
    font-size: 1.2rem;
}

.success .notification-content i {
    color: #27ae60;
}

.error .notification-content i {
    color: #e74c3c;
}
`;
document.head.appendChild(style);

// بستن مودال با کلیک خارج از آن
document.getElementById('partsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideParts();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>