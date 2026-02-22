<?php
// Best Sellers Section Module
?>
<div class="best-sellers-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">پرفروش‌ترین دوره‌ها</h2>
            <a href="pages/products.php?sort=popular" class="view-all">
                مشاهده همه <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        
        <div class="products-grid">
            <?php if(count($best_products) > 0): ?>
                <?php foreach($best_products as $index => $product): ?>
                <div class="product-card">
                    <div class="product-badge">
                        <span class="rank-badge rank-<?php echo $index + 1; ?>">
                            <?php echo $index + 1; ?>
                        </span>
                    </div>
                    <div class="product-image">
                    <img src="images/products/<?php echo !empty($product['image_url']) ? $product['image_url'] : 'default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description">
                            <?php echo substr(htmlspecialchars($product['description']), 0, 100) . '...'; ?>
                        </p>
                        
                        <div class="product-meta">
                            <span class="level"><?php echo htmlspecialchars($product['level']); ?></span>
                            <span class="duration"><?php echo htmlspecialchars($product['duration']); ?></span>
                            <?php if(isset($product['total_sold'])): ?>
                                <span class="sold-count">
                                    <i class="fas fa-shopping-cart"></i> 
                                    <?php echo $product['total_sold'] ?: 0; ?> فروش
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-price">
                            <?php if($product['discount_price']): ?>
                                <span class="original-price">
                                    <?php echo number_format($product['price']); ?> تومان
                                </span>
                                <span class="current-price">
                                    <?php echo number_format($product['discount_price']); ?> تومان
                                </span>
                            <?php else: ?>
                                <span class="current-price">
                                    <?php echo number_format($product['price']); ?> تومان
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <a href="pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn">
                                مشاهده جزئیات
                            </a>
                            <?php if(isLoggedIn()): ?>
                                <button class="btn btn-success add-to-cart" 
                                        data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i>
                                </button>
                            <?php else: ?>
                                <a href="pages/login.php" class="btn btn-success">
                                <i class="fas fa-cart-plus"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-chart-line"></i>
                    <h3>هنوز آماری برای نمایش وجود ندارد</h3>
                    <p>اولین خریدار باشید!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>