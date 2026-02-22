<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/login.php');
    exit();
}

$message = $message_type = '';

// حذف کاربر
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    if ($user_id != $_SESSION['user_id']) {
        try {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            $message = 'کاربر حذف شد';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'خطا در حذف';
            $message_type = 'error';
        }
    } else {
        $message = 'نمی‌توانید خود را حذف کنید';
        $message_type = 'error';
    }
}

// تغییر سطح دسترسی
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $is_admin = intval($_POST['is_admin']);
    
    if ($user_id != $_SESSION['user_id']) {
        try {
            $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?")->execute([$is_admin, $user_id]);
            $message = 'سطح دسترسی به‌روز شد';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'خطا در به‌روزرسانی';
            $message_type = 'error';
        }
    } else {
        $message = 'نمی‌توانید سطح خود را تغییر دهید';
        $message_type = 'error';
    }
}

// دریافت لیست کاربران
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch(PDOException $e) {
    die("خطا در دریافت کاربران");
}

$pageTitle = "مدیریت کاربران";
require_once '../includes/header.php';

// آمارها
$total_users = count($users);
$admin_count = count(array_filter($users, fn($u) => $u['is_admin'] == 1));
$user_count = count(array_filter($users, fn($u) => $u['is_admin'] == 0));
$today_count = count(array_filter($users, fn($u) => date('Y-m-d', strtotime($u['created_at'])) == date('Y-m-d')));
?>

<div class="admin-page">
    <div class="container">
        <div class="page-header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
            <div class="header-title">
                <h1><i class="fas fa-users"></i> مدیریت کاربران</h1>
                <p>مدیریت حساب‌های کاربری</p>
            </div>
        </div>

        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- آمار سریع -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div>
                    <h3><?php echo $total_users; ?></h3>
                    <p>کل کاربران</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-shield"></i>
                <div>
                    <h3><?php echo $admin_count; ?></h3>
                    <p>مدیران</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user"></i>
                <div>
                    <h3><?php echo $user_count; ?></h3>
                    <p>کاربران عادی</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-day"></i>
                <div>
                    <h3><?php echo $today_count; ?></h3>
                    <p>امروز ثبت‌نام</p>
                </div>
            </div>
        </div>

        <!-- جدول کاربران -->
        <div class="card">
            <div class="card-header">
                <h3>لیست کاربران (<?php echo $total_users; ?>)</h3>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="جستجوی کاربر...">
                </div>
            </div>
            
            <div class="card-body">
                <?php if($total_users > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>کاربر</th>
                                <th>ایمیل</th>
                                <th>سطح</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $index => $user): ?>
                            <tr>
                                <td class="text-muted"><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div class="user-info">
                                            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                            <p class="text-muted"><?php echo htmlspecialchars($user['full_name'] ?: 'نام کامل ثبت نشده'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                    <?php if($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge-you">شما</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="is_admin" class="role-select" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                            <option value="0" <?php echo $user['is_admin'] == 0 ? 'selected' : ''; ?>>کاربر</option>
                                            <option value="1" <?php echo $user['is_admin'] == 1 ? 'selected' : ''; ?>>مدیر</option>
                                        </select>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="submit" name="update_role" class="btn-icon save" title="ذخیره">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td class="text-muted">
                                    <?php echo date('Y/m/d', strtotime($user['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="btn-icon view" 
                                                onclick="showUserDetails(<?php echo htmlspecialchars(json_encode([
                                                    'username' => $user['username'],
                                                    'full_name' => $user['full_name'],
                                                    'email' => $user['email'],
                                                    'phone' => $user['phone'],
                                                    'role' => $user['is_admin'] ? 'مدیر' : 'کاربر',
                                                    'created' => date('Y/m/d H:i', strtotime($user['created_at']))
                                                ])); ?>)" 
                                                title="جزئیات">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage-users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn-icon delete" 
                                           title="حذف"
                                           onclick="return confirm('حذف «<?php echo htmlspecialchars($user['username']); ?>»؟')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h4>کاربری یافت نشد</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- مودال -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user"></i> جزئیات کاربر</h3>
            <button class="close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="details">
                <div class="detail">
                    <label><i class="fas fa-user-circle"></i> نام کاربری:</label>
                    <span id="m-username"></span>
                </div>
                <div class="detail">
                    <label><i class="fas fa-id-card"></i> نام کامل:</label>
                    <span id="m-fullname"></span>
                </div>
                <div class="detail">
                    <label><i class="fas fa-envelope"></i> ایمیل:</label>
                    <span id="m-email"></span>
                </div>
                <div class="detail">
                    <label><i class="fas fa-phone"></i> تلفن:</label>
                    <span id="m-phone"></span>
                </div>
                <div class="detail">
                    <label><i class="fas fa-shield-alt"></i> سطح:</label>
                    <span id="m-role"></span>
                </div>
                <div class="detail">
                    <label><i class="fas fa-calendar"></i> تاریخ:</label>
                    <span id="m-created"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-page { padding: 20px; background: #f5f7fa; min-height: 100vh; }
.container { max-width: 1200px; margin: 0 auto; }

table {
    width: 100%;
    min-width: 1200px;
    border-collapse: separate;
    border-spacing: 2vw 0;  /* 2% از عرض صفحه */
}
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

/* پیام‌ها */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
.alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

/* آمار */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.stat-card i {
    font-size: 2rem;
    color: #3498db;
}
.stat-card h3 { margin: 0; font-size: 1.8rem; color: #2c3e50; }
.stat-card p { margin: 5px 0 0 0; color: #6c757d; }

/* کارت و جدول */
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

/* سلول کاربر */
.user-cell { display: flex; align-items: center; gap: 15px; }
.avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}
.user-info h4 { margin: 0 0 5px 0; font-size: 15px; }
.user-info p { margin: 0; font-size: 13px; }

/* سطح دسترسی */
.role-form { display: flex; align-items: center; gap: 10px; }
.role-select {
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    min-width: 100px;
}

/* دکمه‌ها */
.actions { display: flex; gap: 8px; }
.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}
.save { background: #e3f2fd; color: #1976d2; }
.save:hover { background: #1976d2; color: white; }
.view { background: #e8f5e9; color: #388e3c; }
.view:hover { background: #388e3c; color: white; }
.delete { background: #fce4ec; color: #c2185b; }
.delete:hover { background: #c2185b; color: white; }

.badge-you {
    background: #3498db;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    margin-right: 5px;
}

/* وضعیت خالی */
.empty-state {
    padding: 60px 20px;
    text-align: center;
}
.empty-state i { font-size: 48px; color: #bdc3c7; margin-bottom: 20px; }
.empty-state h4 { margin: 0; color: #7f8c8d; }

/* مودال */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 450px;
    animation: fadeIn 0.3s;
}
.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 { margin: 0; color: #2c3e50; }
.close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #7f8c8d;
}
.modal-body { padding: 20px; }
.details .detail {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f8f9fa;
}
.details .detail:last-child { border: none; margin: 0; padding: 0; }
.details label {
    width: 120px;
    color: #6c757d;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.details span { color: #2c3e50; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* رسپانسیو */
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .back-btn { justify-content: center; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .search-box { width: 100%; margin-top: 15px; }
    .card-header { flex-direction: column; align-items: stretch; }
    .details .detail { flex-direction: column; }
    .details label { width: 100%; margin-bottom: 5px; }
}
</style>

<script>
function showUserDetails(userData) {
    document.getElementById('m-username').textContent = userData.username;
    document.getElementById('m-fullname').textContent = userData.full_name || 'ثبت نشده';
    document.getElementById('m-email').textContent = userData.email;
    document.getElementById('m-phone').textContent = userData.phone || 'ثبت نشده';
    document.getElementById('m-role').textContent = userData.role;
    document.getElementById('m-created').textContent = userData.created;
    
    document.getElementById('userModal').style.display = 'flex';
}

// بستن مودال
document.querySelector('.close').addEventListener('click', () => {
    document.getElementById('userModal').style.display = 'none';
});

// بستن با کلیک بیرون
window.addEventListener('click', (e) => {
    if (e.target == document.getElementById('userModal')) {
        document.getElementById('userModal').style.display = 'none';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>