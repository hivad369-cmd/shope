// مدیریت سبد خرید
document.addEventListener('DOMContentLoaded', function() {
    // به‌روزرسانی تعداد آیتم‌های سبد خرید
    updateCartCount();
    // دکمه برگشت به بالا
    initBackToTop();
    // افزودن محصول به سبد خرید
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            addToCart(productId);
        });
    });
    
    // حذف آیتم از سبد خرید
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const cartItemId = this.getAttribute('data-id');
            removeFromCart(cartItemId);
        });
    });
    
    // تغییر تعداد محصول در سبد خرید
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartItemId = this.getAttribute('data-id');
            const action = this.getAttribute('data-action');
            updateCartQuantity(cartItemId, action);
        });
    });
    
    // مدیریت فرم ثبت‌نام
    const registerForm = document.getElementById('registerForm');
    if(registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if(!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }
    
    // مدیریت فرم پرداخت
    const paymentForm = document.getElementById('paymentForm');
    if(paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            showLoading();
        });
    }
    
    // همچنین برای اطمینان بیشتر هنگام لود کامل صفحه
    window.addEventListener('load', function() {
        if (typeof initProductsSlider === 'function') {
            initProductsSlider();
        }
    });
});

// تابع افزودن به سبد خرید
function addToCart(productId) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            updateCartCount();
            showToast('محصول با موفقیت به سبد خرید اضافه شد', 'success');
        } else {
            showToast(data.message || 'خطا در افزودن به سبد خرید', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('خطا در ارتباط با سرور', 'error');
    });
}

// تابع به‌روزرسانی تعداد سبد خرید
function updateCartCount() {
    fetch('ajax/get_cart_count.php')
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const cartCount = document.getElementById('cartCount');
            if(cartCount) {
                cartCount.textContent = data.count;
            }
        }
    });
}

// تابع حذف از سبد خرید
function removeFromCart(cartItemId) {
    if(confirm('آیا از حذف این محصول از سبد خرید مطمئن هستید؟')) {
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartItemId}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                showToast('خطا در حذف محصول', 'error');
            }
        });
    }
}

// تابع به‌روزرسانی تعداد محصول
function updateCartQuantity(cartItemId, action) {
    fetch('ajax/update_cart_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartItemId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        }
    });
}

// تابع نمایش نوتیفیکیشن
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// تابع اعتبارسنجی فرم ثبت‌نام
function validateRegisterForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const email = document.getElementById('email').value;
    
    // بررسی تطابق رمز عبور
    if(password !== confirmPassword) {
        showToast('رمز عبور و تکرار آن مطابقت ندارند', 'error');
        return false;
    }
    
    // بررسی طول رمز عبور
    if(password.length < 6) {
        showToast('رمز عبور باید حداقل ۶ کاراکتر باشد', 'error');
        return false;
    }
    
    // بررسی ایمیل
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRegex.test(email)) {
        showToast('لطفا یک ایمیل معتبر وارد کنید', 'error');
        return false;
    }
    
    return true;
}

// تابع نمایش اسپینر لودینگ
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>در حال پردازش...</p>
        </div>
    `;
    document.body.appendChild(loading);
}

function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) return;
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}



// استایل برای توست و لودینگ
const style = document.createElement('style');
style.textContent = `
.toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100%);
    background: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    transition: transform 0.3s ease;
}

.toast.show {
    transform: translateX(-50%) translateY(0);
}

.toast-success {
    border-right: 4px solid #27ae60;
}

.toast-error {
    border-right: 4px solid #e74c3c;
}

#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

.loading-spinner {
    text-align: center;
    color: white;
}

.spinner {
    border: 4px solid rgba(255,255,255,0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
`;
document.head.appendChild(style);