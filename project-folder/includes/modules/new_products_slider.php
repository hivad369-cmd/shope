<div class="new-products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">جدیدترین دوره‌ها</h2>
            <a href="pages/products.php?sort=newest" class="view-all">
                مشاهده همه <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        
        <div class="minimal-carousel">
            <div class="carousel-wrapper">
                <div class="carousel-inner" id="carouselWrapper">
                    <?php 
                    // حلقه‌ای کردن برای فارسی (۲ اسلاید اضافه در ابتدا و ۲ اسلاید در انتها)
                    $looped_products = array_merge(
                        array_slice($new_products, -2), // ۲ عنصر آخر به ابتدا
                        $new_products,                 // کل آرایه اصلی
                        array_slice($new_products, 0, 2) // ۲ عنصر اول به انتها
                    );
                    foreach($looped_products as $index => $product): 
                    ?>
                    <div class="carousel-slide" data-index="<?= ($index - 2 + count($new_products)) % count($new_products) ?>">
                        <div class="product-minimal-card">
                            <div class="product-image">
                                <img src="images/products/<?= $product['image_url'] ?: 'default.jpg' ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     onerror="this.src='https://via.placeholder.com/280x180/3498db/ffffff?text=بدون+تصویر'">
                                
                                <?php if($product['discount_price'] < $product['price']): ?>
                                    <span class="minimal-discount-badge">تخفیف</span>
                                <?php endif; ?>
                                
                                <div class="signal-icon level-<?= $product['level'] ?>" 
                                    title="سطح: <?= $product['level'] ?>">
                                    <i class="fas fa-signal"></i>
                                    <span class="signal-text">
                                        <?php 
                                        if($product['level'] == 'مقدماتی') echo 'ضعیف';
                                        elseif($product['level'] == 'متوسط') echo 'متوسط';
                                        else echo 'قوی';
                                        ?>
                                    </span>
                                </div>

                                <div class="minimal-hover-layer">
                                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    <a href="pages/product-detail.php?id=<?= $product['id'] ?>" class="minimal-btn-view ">
                                        <i class="fas fa-eye"></i> مشاهده
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- تغییر جهت آیکون‌ها برای فارسی -->
            <button class="carousel-btn minimal-carousel-btn minimal-carousel-prev" id="prevBtn">
                <i class="fas fa-chevron-left"></i> <!-- قبلا right بود -->
            </button>
            
            <button class="carousel-btn minimal-carousel-btn minimal-carousel-next" id="nextBtn">
                <i class="fas fa-chevron-right"></i> <!-- قبلا left بود -->
            </button>
            
            <div class="minimal-carousel-dots" id="carouselDots"></div>
        </div>
    </div>
</div>

<script>
var productsData = <?= json_encode($new_products) ?>;
</script>