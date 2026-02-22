<?php
// Categories Section Module
?>
<div class="categories-section">
    <div class="container">
        <h2 class="section-title">دسته‌بندی دوره‌ها</h2>
        <div class="categories-grid">
            <a href="pages/products.php?level=مقدماتی" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3>مقدماتی</h3>
                <p>آغاز یادگیری زبان از پایه</p>
                <span class="category-count">
                    <?php 
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE level = 'مقدماتی' AND is_active = 1");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?> دوره
                </span>
            </a>
            
            <a href="pages/products.php?level=متوسط" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>متوسط</h3>
                <p>تقویت مهارت‌های زبانی</p>
                <span class="category-count">
                    <?php 
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE level = 'متوسط' AND is_active = 1");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?> دوره
                </span>
            </a>
            
            <a href="pages/products.php?level=پیشرفته" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>پیشرفته</h3>
                <p>تسلط کامل بر زبان انگلیسی</p>
                <span class="category-count">
                    <?php 
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE level = 'پیشرفته' AND is_active = 1");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?> دوره
                </span>
            </a>
            
            <a href="pages/products.php" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3>ویژه</h3>
                <p>دوره‌های تخصصی و حرفه‌ای</p>
                <span class="category-count">
                    <?php 
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE is_active = 1");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?> دوره
                </span>
            </a>
        </div>
    </div>
</div>