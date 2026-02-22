// مدیریت سبد خرید
class CartManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateCartCount();
    }
    
    bindEvents() {
        // افزودن به سبد خرید
        document.addEventListener('click', (e) => {
            if(e.target.closest('.add-to-cart')) {
                const button = e.target.closest('.add-to-cart');
                const productId = button.dataset.id;
                this.addToCart(productId, button);
            }
            
            // حذف از سبد خرید
            if(e.target.closest('.remove-from-cart')) {
                const button = e.target.closest('.remove-from-cart');
                const cartId = button.dataset.id;
                this.removeFromCart(cartId);
            }
        });
    }
    
    async addToCart(productId, button) {
        try {
            // نمایش لودینگ
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال افزودن...';
            button.disabled = true;
            
            const response = await fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            });
            
            const data = await response.json();
            
            if(data.success) {
                this.showNotification('محصول با موفقیت به سبد خرید اضافه شد', 'success');
                this.updateCartCount();
                this.updateCartUI();
            } else {
                this.showNotification(data.message || 'خطا در افزودن به سبد خرید', 'error');
            }
        } catch(error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            // بازگرداندن دکمه به حالت اولیه
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 1000);
        }
    }
    
    async removeFromCart(cartId) {
        if(!confirm('آیا از حذف این محصول مطمئن هستید؟')) return;
        
        try {
            const response = await fetch('ajax/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            });
            
            const data = await response.json();
            
            if(data.success) {
                this.showNotification('محصول با موفقیت حذف شد', 'success');
                this.updateCartCount();
                this.updateCartUI();
                
                // ریلود صفحه اگر در صفحه سبد خرید هستیم
                if(window.location.pathname.includes('cart.php')) {
                    location.reload();
                }
            } else {
                this.showNotification('خطا در حذف محصول', 'error');
            }
        } catch(error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }
    
    async updateCartCount() {
        try {
            const response = await fetch('ajax/get_cart_count.php');
            const data = await response.json();
            
            if(data.success) {
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(element => {
                    element.textContent = data.count;
                    element.style.display = data.count > 0 ? 'flex' : 'none';
                });
            }
        } catch(error) {
            console.error('Error updating cart count:', error);
        }
    }
    
    updateCartUI() {
        // آپدیت UI سبد خرید در صورت نیاز
        // این متد می‌تواند سفارشی‌سازی شود
    }
    
    showNotification(message, type = 'info') {
        // ایجاد عنصر نوتیفیکیشن
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        // اضافه کردن به بدنه
        document.body.appendChild(notification);
        
        // نمایش
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // حذف بعد از ۳ ثانیه
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if(notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    async updateQuantity(cartId, quantity) {
        try {
            const response = await fetch('ajax/update_cart_quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}&quantity=${quantity}`
            });
            
            const data = await response.json();
            
            if(data.success) {
                if(window.location.pathname.includes('cart.php')) {
                    location.reload();
                } else {
                    this.updateCartCount();
                }
            } else {
                this.showNotification('خطا در به‌روزرسانی تعداد', 'error');
            }
        } catch(error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }
}

// راه‌اندازی کارت منیجر هنگام لود صفحه
document.addEventListener('DOMContentLoaded', function() {
    window.cartManager = new CartManager();
});

// استایل‌های CSS برای نوتیفیکیشن
const notificationStyles = `
.notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100%);
    background: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    min-width: 300px;
    max-width: 90%;
    transition: transform 0.3s ease;
    border-right: 4px solid #3498db;
}

.notification.show {
    transform: translateX(-50%) translateY(0);
}

.notification-success {
    border-right-color: #27ae60;
}

.notification-error {
    border-right-color: #e74c3c;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-content i {
    font-size: 1.2rem;
}

.notification-success .notification-content i {
    color: #27ae60;
}

.notification-error .notification-content i {
    color: #e74c3c;
}

.notification-content span {
    flex: 1;
}
`;

// اضافه کردن استایل‌ها به head
const styleSheet = document.createElement("style");
styleSheet.type = "text/css";
styleSheet.innerText = notificationStyles;
document.head.appendChild(styleSheet);