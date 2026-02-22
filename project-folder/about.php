<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$pageTitle = "درباره ما";
require_once 'includes/header.php';
?>

<style>
/* استایل‌های صفحه درباره ما */
.about-page {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: calc(100vh - 400px);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-title {
    text-align: center;
    font-size: 2.8rem;
    color: #2c3e50;
    margin-bottom: 60px;
    position: relative;
    padding-bottom: 20px;
}

.page-title::after {
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

.about-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;
}

@media (max-width: 992px) {
    .about-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
}

/* بخش متن درباره ما */
.about-text {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(52, 152, 219, 0.1);
    position: relative;
    overflow: hidden;
}

.about-text::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(to bottom, #3498db, #2ecc71);
}

.about-text h2 {
    color: #2c3e50;
    font-size: 1.6rem;
    margin: 30px 0 15px;
    padding-right: 15px;
    position: relative;
}

.about-text h2:first-child {
    margin-top: 0;
}

.about-text h2::before {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background: #3498db;
    border-radius: 50%;
}

.about-text p {
    color: #4a5568;
    line-height: 1.8;
    font-size: 1.05rem;
    text-align: justify;
    margin-bottom: 20px;
    padding-right: 15px;
}

/* بخش آمار */
.about-stats {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(44, 62, 80, 0.2);
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    color: white;
    position: relative;
    overflow: hidden;
}

.about-stats::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(52, 152, 219, 0.1) 1px, transparent 1px);
    background-size: 30px 30px;
    animation: float 20s linear infinite;
    z-index: 1;
}

@keyframes float {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.stat {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 30px 20px;
    border-radius: 15px;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.stat:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(52, 152, 219, 0.5);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.stat-number {
    display: block;
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    margin-bottom: 10px;
    position: relative;
}

.stat-number::after {
    content: '';
    position: absolute;
    bottom: -5px;
    right: 50%;
    transform: translateX(50%);
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    border-radius: 2px;
}

.stat-label {
    font-size: 1rem;
    color: #e2e8f0;
    display: block;
}

/* آیکون‌های استات */
.stat:nth-child(1)::before {
    content: '👥';
    font-size: 1.8rem;
    position: absolute;
    top: -15px;
    right: 15px;
    background: rgba(52, 152, 219, 0.2);
    padding: 5px;
    border-radius: 50%;
}

.stat:nth-child(2)::before {
    content: '📚';
    font-size: 1.8rem;
    position: absolute;
    top: -15px;
    right: 15px;
    background: rgba(46, 204, 113, 0.2);
    padding: 5px;
    border-radius: 50%;
}

.stat:nth-child(3)::before {
    content: '⭐';
    font-size: 1.8rem;
    position: absolute;
    top: -15px;
    right: 15px;
    background: rgba(241, 196, 15, 0.2);
    padding: 5px;
    border-radius: 50%;
}

.stat:nth-child(4)::before {
    content: '👨‍🏫';
    font-size: 1.8rem;
    position: absolute;
    top: -15px;
    right: 15px;
    background: rgba(155, 89, 182, 0.2);
    padding: 5px;
    border-radius: 50%;
}

/* بخش تایم‌لاین (اختیاری) */
.timeline-section {
    margin-top: 80px;
    padding: 60px 0;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.timeline-title {
    text-align: center;
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 40px;
}

.timeline {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.timeline-item {
    margin-bottom: 30px;
    padding-right: 40px;
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    width: 20px;
    height: 20px;
    background: #3498db;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 0 0 4px #3498db;
}

.timeline-item::after {
    content: '';
    position: absolute;
    right: 8px;
    top: 20px;
    width: 4px;
    height: calc(100% + 30px);
    background: #e2e8f0;
    z-index: -1;
}

.timeline-item:last-child::after {
    display: none;
}

.timeline-year {
    background: linear-gradient(90deg, #3498db, #2ecc71);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 10px;
}

.timeline-content {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    border-right: 4px solid #3498db;
}

.timeline-content h3 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.timeline-content p {
    color: #4a5568;
    line-height: 1.6;
}
</style>

<div class="about-page">
    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-star"></i>
            درباره English Master
            <i class="fas fa-star"></i>
        </h1>
        
        <div class="about-content">
            <div class="about-text">
                <h2><i class="fas fa-bullseye"></i> ماموریت ما</h2>
                <p>
                    فروشگاه English Master با هدف ارائه باکیفیت‌ترین پکیج‌های آموزش زبان انگلیسی تأسیس شده است. 
                    ما اعتقاد داریم یادگیری زبان انگلیسی نه تنها یک مهارت، بلکه دریچه‌ای به سوی فرصت‌های بی‌شماری است 
                    که می‌تواند زندگی افراد را متحول کند. تیم ما متعهد است تا با ارائه محتوای آموزشی استاندارد و 
                    به‌روز، فرآیند یادگیری را لذت‌بخش و مؤثر کند.
                </p>
                
                <h2><i class="fas fa-users"></i> تیم متخصص ما</h2>
                <p>
                    تیم ما از مدرسین مجرب و متخصصین آموزش زبان تشکیل شده که هر کدام سال‌ها تجربه عملی در زمینه 
                    تدریس زبان انگلیسی دارند. ما با بهره‌گیری از روش‌های نوین آموزشی و فناوری‌های روز دنیا، 
                    محیطی پویا و تعاملی برای یادگیری فراهم کرده‌ایم. هر یک از اعضای تیم ما علاوه بر تسلط کامل بر 
                    زبان انگلیسی، دارای مدارک بین‌المللی و تجربه آموزش به هزاران زبان‌آموز هستند.
                </p>
                
                <h2><i class="fas fa-eye"></i> چشم‌انداز ما</h2>
                <p>
                    هدف ما تبدیل شدن به برترین و جامع‌ترین پلتفرم آموزش زبان انگلیسی در ایران است. 
                    ما می‌خواهیم با ایجاد جامعه‌ای از زبان‌آموزان مشتاق و موفق، نه تنها مهارت زبانی آن‌ها را 
                    تقویت کنیم، بلکه اعتماد به نفس لازم برای استفاده عملی از این مهارت را در محیط‌های بین‌المللی 
                    فراهم آوریم. چشم‌انداز ما ایجاد تحولی پایدار در صنعت آموزش زبان کشور است.
                </p>
                
                <h2><i class="fas fa-award"></i> ارزش‌های ما</h2>
                <p>
                    کیفیت بی‌چون و چرا، نوآوری مستمر، شفافیت کامل و تعهد به موفقیت دانشجویان، چهار ستون اصلی 
                    ارزش‌های ما را تشکیل می‌دهند. ما برای هر دانشجو وقت می‌گذاریم، به پیشرفت آن‌ها افتخار می‌کنیم 
                    و همراهیشان در مسیر یادگیری را افتخاری بزرگ برای خود می‌دانیم.
                </p>
            </div>
            
            <div class="about-stats">
                <div class="stat">
                    <span class="stat-number">۵۰۰+</span>
                    <span class="stat-label">دانشجوی فعال</span>
                </div>
                <div class="stat">
                    <span class="stat-number">۵۰+</span>
                    <span class="stat-label">پکیج آموزشی</span>
                </div>
                <div class="stat">
                    <span class="stat-number">۹۸٪</span>
                    <span class="stat-label">رضایت‌مندی</span>
                </div>
                <div class="stat">
                    <span class="stat-number">۱۰+</span>
                    <span class="stat-label">مدرس مجرب</span>
                </div>
            </div>
        </div>
        
        <!-- بخش تایم‌لاین -->
        <div class="timeline-section">
            <h2 class="timeline-title">مسیر پیشرفت ما</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <span class="timeline-year">۱۳۹۸</span>
                    <div class="timeline-content">
                        <h3>شروع فعالیت</h3>
                        <p>تأسیس English Master با هدف ارائه آموزش‌های زبان انگلیسی به صورت آنلاین</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <span class="timeline-year">۱۴۰۰</span>
                    <div class="timeline-content">
                        <h3>اولین پکیج‌های آموزشی</h3>
                        <p>انتشار اولین مجموعه پکیج‌های آموزشی و جذب اولین دانشجویان</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <span class="timeline-year">۱۴۰۲</span>
                    <div class="timeline-content">
                        <h3>توسعه پلتفرم</h3>
                        <p>بهبود سامانه آموزشی و اضافه شدن پکیج‌های تخصصی</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <span class="timeline-year">۱۴۰۳</span>
                    <div class="timeline-content">
                        <h3>دستاورد جدید</h3>
                        <p>رسیدن به ۵۰۰ دانشجوی فعال و کسب گواهینامه کیفیت آموزشی</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>