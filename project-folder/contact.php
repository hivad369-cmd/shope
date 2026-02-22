<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    // در اینجا می‌توانید ایمیل ارسال کنید یا در دیتابیس ذخیره کنید
    $success = 'پیام شما با موفقیت ارسال شد. به زودی با شما تماس می‌گیریم.';
}

$pageTitle = "تماس با ما";
require_once 'includes/header.php';
?>

<style>
/* استایل‌های صفحه تماس با ما */
.contact-page {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: calc(100vh - 400px);
    position: relative;
    overflow: hidden;
}

.contact-page::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="%233498db" opacity="0.03"/></svg>');
    background-size: cover;
    z-index: 1;
}

.contact-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.contact-page-title {
    text-align: center;
    font-size: 2.8rem;
    color: #2c3e50;
    margin-bottom: 60px;
    position: relative;
    padding-bottom: 20px;
}

.contact-page-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 50%;
    transform: translateX(50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    border-radius: 2px;
}

/* آلرت‌های صفحه تماس */
.contact-alert {
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    animation: contactSlideIn 0.5s ease;
    position: relative;
    overflow: hidden;
}

.contact-alert::before {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    width: 5px;
    height: 100%;
}

.contact-alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border: 1px solid #c3e6cb;
    color: #155724;
}

.contact-alert-success::before {
    background: #28a745;
}

.contact-alert-error {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.contact-alert-error::before {
    background: #dc3545;
}

.contact-alert i {
    font-size: 1.5rem;
}

@keyframes contactSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* محتوای اصلی صفحه تماس */
.contact-content {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 60px;
    align-items: start;
}

@media (max-width: 992px) {
    .contact-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
}

/* اطلاعات تماس صفحه */
.contact-info-box {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(52, 152, 219, 0.1);
    position: relative;
    overflow: hidden;
}

.contact-info-box::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
}

.contact-info-box h3 {
    color: #2c3e50;
    font-size: 1.8rem;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.contact-info-box h3 i {
    color: #3498db;
}

.contact-detail {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    transition: all 0.3s ease;
}

.contact-detail:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.contact-detail:hover {
    transform: translateX(-5px);
}

.contact-detail i {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.contact-detail:hover i {
    transform: rotate(10deg) scale(1.1);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.contact-detail div {
    flex: 1;
}

.contact-detail h4 {
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1.1rem;
}

.contact-detail p {
    color: #4a5568;
    line-height: 1.6;
    margin-bottom: 5px;
}

/* فرم تماس صفحه */
.contact-form-box {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(52, 152, 219, 0.1);
    position: relative;
    overflow: hidden;
}

.contact-form-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, #2ecc71, #3498db);
}

.contact-form-box h3 {
    color: #2c3e50;
    font-size: 1.8rem;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.contact-form-box h3 i {
    color: #2ecc71;
}

.contact-field {
    margin-bottom: 25px;
    position: relative;
}

.contact-field label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95rem;
}

.contact-field label::after {
    content: ' *';
    color: #e74c3c;
}

.contact-input {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8fafc;
    color: #2c3e50;
}

.contact-input:focus {
    outline: none;
    border-color: #3498db;
    background: white;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.contact-input:hover {
    border-color: #b0bec5;
}

textarea.contact-input {
    resize: vertical;
    min-height: 150px;
    line-height: 1.6;
}

.contact-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.contact-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.contact-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
}

.contact-btn:hover::before {
    left: 100%;
}

.contact-btn i {
    font-size: 1.2rem;
}

/* نقشه صفحه تماس */
.contact-map-section {
    margin-top: 80px;
    padding: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.contact-map-title {
    text-align: center;
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.contact-map-container {
    height: 400px;
    border-radius: 15px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.contact-map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    text-align: center;
}

.contact-map-placeholder i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.8;
}

.contact-map-placeholder h4 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.contact-map-placeholder p {
    font-size: 1rem;
    opacity: 0.9;
    max-width: 500px;
    line-height: 1.6;
}

/* سوالات متداول صفحه تماس */
.contact-faq-section {
    margin-top: 80px;
    padding: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.contact-faq-title {
    text-align: center;
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.contact-faq-item {
    margin-bottom: 15px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8fafc;
    transition: all 0.3s ease;
}

.contact-faq-question {
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.contact-faq-question i {
    transition: transform 0.3s ease;
}

.contact-faq-answer {
    padding: 0;
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.contact-faq-answer.show {
    padding: 20px;
    max-height: 500px;
}

.contact-faq-answer p {
    color: #4a5568;
    line-height: 1.6;
}

.contact-faq-item.active .contact-faq-question i {
    transform: rotate(180deg);
}

.contact-faq-item.active .contact-faq-question {
    background: linear-gradient(135deg, #2980b9, #3498db);
}
</style>

<div class="contact-page">
    <div class="contact-container">
        <h1 class="contact-page-title">
            <i class="fas fa-comments"></i>
            تماس با ما
            <i class="fas fa-comments"></i>
        </h1>
        
        <?php if($success): ?>
        <div class="contact-alert contact-alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="contact-alert contact-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <div class="contact-content">
            <div class="contact-info-box">
                <h3><i class="fas fa-address-card"></i> اطلاعات تماس</h3>
                
                <div class="contact-detail">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>تلفن تماس</h4>
                        <p>۰۲۱-۱۲۳۴۵۶۷۸</p>
                        <p>۰۹۱۲-۱۲۳۴۵۶۷ (پشتیبانی واتساپ)</p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>ایمیل</h4>
                        <p>info@english-courses.ir</p>
                        <p>support@english-courses.ir</p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>آدرس دفتر</h4>
                        <p>تهران، خیابان ولیعصر، بالاتر از میدان ونک، پلاک ۱۲۳۴</p>
                        <p>طبقه ۵، واحد ۱۰</p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>ساعات کاری</h4>
                        <p><strong>شنبه تا چهارشنبه:</strong> ۸:۰۰ صبح تا ۱۷:۰۰ عصر</p>
                        <p><strong>پنجشنبه:</strong> ۸:۰۰ صبح تا ۱۳:۰۰ ظهر</p>
                        <p><strong>جمعه:</strong> تعطیل</p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h4>پشتیبانی</h4>
                        <p>پشتیبانی ۲۴ ساعته از طریق واتساپ</p>
                        <p>پاسخگویی ایمیل در کمتر از ۲۴ ساعت</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-box">
                <h3><i class="fas fa-paper-plane"></i> فرم تماس</h3>
                <form method="POST" id="contactForm">
                    <div class="contact-field">
                        <label for="name">نام کامل</label>
                        <input type="text" id="name" name="name" class="contact-input" 
                               placeholder="نام و نام خانوادگی خود را وارد کنید" required>
                    </div>
                    
                    <div class="contact-field">
                        <label for="email">آدرس ایمیل</label>
                        <input type="email" id="email" name="email" class="contact-input" 
                               placeholder="example@domain.com" required>
                    </div>
                    
                    <div class="contact-field">
                        <label for="subject">موضوع پیام</label>
                        <input type="text" id="subject" name="subject" class="contact-input" 
                               placeholder="موضوع پیام خود را وارد کنید" required>
                    </div>
                    
                    <div class="contact-field">
                        <label for="message">متن پیام</label>
                        <textarea id="message" name="message" class="contact-input" 
                                  placeholder="پیام خود را با جزئیات بنویسید..." required></textarea>
                    </div>
                    
                    <button type="submit" class="contact-btn">
                        <i class="fas fa-paper-plane"></i>
                        ارسال پیام
                    </button>
                </form>
            </div>
        </div>
        
        <!-- بخش نقشه -->
        <div class="contact-map-section">
            <h3 class="contact-map-title">
                <i class="fas fa-map-marked-alt"></i>
                موقعیت ما روی نقشه
            </h3>
            <div class="contact-map-container">
                <div class="contact-map-placeholder">
                    <i class="fas fa-map"></i>
                    <h4>موقعیت ما در تهران</h4>
                    <p>برای مشاهده دقیق‌تر موقعیت دفتر ما روی نقشه، روی دکمه زیر کلیک کنید</p>
                    <button class="contact-btn" style="width: auto; margin-top: 20px; padding: 12px 30px;">
                        <i class="fas fa-external-link-alt"></i>
                        مشاهده در Google Maps
                    </button>
                </div>
            </div>
        </div>
        
        <!-- سوالات متداول -->
        <div class="contact-faq-section">
            <h3 class="contact-faq-title">
                <i class="fas fa-question-circle"></i>
                سوالات متداول
            </h3>
            
            <div class="contact-faq-item">
                <div class="contact-faq-question">
                    <span>چطور می‌توانم از کیفیت پکیج‌های آموزشی مطمئن شوم؟</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="contact-faq-answer">
                    <p>تمام پکیج‌های آموزشی ما دارای نمونه رایگان هستند که می‌توانید قبل از خرید مشاهده کنید. همچنین تمام پکیج‌ها دارای گارانتی بازگشت وجه ۷ روزه هستند.</p>
                </div>
            </div>
            
            <div class="contact-faq-item">
                <div class="contact-faq-question">
                    <span>چقدر طول می‌کشد تا به پیام من پاسخ داده شود؟</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="contact-faq-answer">
                    <p>ما حداکثر تا ۲۴ ساعت کاری به تمام پیام‌ها پاسخ می‌دهیم. در صورت فوریت می‌توانید از طریق شماره واتساپ با ما در ارتباط باشید.</p>
                </div>
            </div>
            
            <div class="contact-faq-item">
                <div class="contact-faq-question">
                    <span>آیا امکان پرداخت اقساطی وجود دارد؟</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="contact-faq-answer">
                    <p>بله، برای پکیج‌های بالای ۱ میلیون تومان امکان پرداخت اقساطی وجود دارد. برای اطلاعات بیشتر با پشتیبانی تماس بگیرید.</p>
                </div>
            </div>
            
            <div class="contact-faq-item">
                <div class="contact-faq-question">
                    <span>چطور می‌توانم مدرس شوم؟</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="contact-faq-answer">
                    <p>اگر مدرس زبان انگلیسی هستید و تجربه تدریس دارید، رزومه خود را به ایمیل jobs@english-courses.ir ارسال کنید.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// اسکریپت برای سوالات متداول
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.contact-faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.contact-faq-question');
        
        question.addEventListener('click', () => {
            // بستن بقیه آیتم‌ها
            faqItems.forEach(otherItem => {
                if(otherItem !== item) {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.contact-faq-answer').classList.remove('show');
                }
            });
            
            // باز/بستن آیتم جاری
            item.classList.toggle('active');
            const answer = item.querySelector('.contact-faq-answer');
            answer.classList.toggle('show');
        });
    });
    
    // اعتبارسنجی فرم
    const contactForm = document.getElementById('contactForm');
    
    contactForm.addEventListener('submit', function(e) {
        let isValid = true;
        const inputs = contactForm.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            if(!input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#e74c3c';
            } else {
                input.style.borderColor = '#3498db';
            }
        });
        
        if(!isValid) {
            e.preventDefault();
            alert('لطفا تمام فیلدهای ضروری را پر کنید.');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>