class MinimalCarousel {
    constructor(products, slidesToShow = 3) {
        this.products = products;
        this.slidesToShow = slidesToShow;
        this.totalSlides = products.length;
        this.clonedCount = 2; // تعداد اسلایدهای کپی شده در هر طرف
        this.currentSlide = this.clonedCount; // شروع از اسلایدهای کپی شده ابتدا
        this.isAnimating = false;
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.createDots();
        this.update(true);
    }
    
    bindEvents() {
        document.getElementById('prevBtn')?.addEventListener('click', () => this.next()); // معکوس
        document.getElementById('nextBtn')?.addEventListener('click', () => this.prev()); // معکوس
        window.addEventListener('resize', () => this.handleResize());
    }
    
    update(instant = false) {
        const wrapper = document.getElementById('carouselWrapper');
        if (!wrapper) return;
        
        if (!instant) wrapper.style.transition = 'transform 0.4s ease';
        
        // برای فارسی: حرکت به راست با translateX مثبت
        wrapper.style.transform = `translateX(${this.currentSlide * (100 / this.slidesToShow)}%)`;
        
        // حلقه‌ای: اگر به انتهای کپی‌ها رسیدیم
        if (this.currentSlide >= this.totalSlides + this.clonedCount) {
            setTimeout(() => {
                this.currentSlide = this.clonedCount;
                wrapper.style.transition = 'none';
                wrapper.style.transform = `translateX(${this.currentSlide * (100 / this.slidesToShow)}%)`;
                setTimeout(() => wrapper.style.transition = 'transform 0.4s ease', 50);
            }, 400);
        }
        
        // حلقه‌ای: اگر به ابتدای کپی‌ها رسیدیم
        if (this.currentSlide <= -this.clonedCount) {
            setTimeout(() => {
                this.currentSlide = this.totalSlides - this.clonedCount;
                wrapper.style.transition = 'none';
                wrapper.style.transform = `translateX(${this.currentSlide * (100 / this.slidesToShow)}%)`;
                setTimeout(() => wrapper.style.transition = 'transform 0.4s ease', 50);
            }, 400);
        }
        
        this.updateDots();
    }
    
    createDots() {
        const container = document.getElementById('carouselDots');
        if (!container) return;
        
        container.innerHTML = '';
        
        for (let i = 0; i < this.totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = 'minimal-carousel-dot';
            
            // محاسبه اندیس واقعی برای دات‌ها
            const realIndex = this.getRealIndex();
            if (i === realIndex) {
                dot.classList.add('active');
            }
            
            dot.onclick = () => this.goTo(i);
            container.appendChild(dot);
        }
    }
    
    getRealIndex() {
        // محاسبه اندیس واقعی در آرایه اصلی
        let realIndex = (this.currentSlide - this.clonedCount) % this.totalSlides;
        if (realIndex < 0) {
            realIndex = this.totalSlides + realIndex;
        }
        return realIndex;
    }
    
    updateDots() {
        const dots = document.querySelectorAll('#carouselDots .minimal-carousel-dot');
        const realIndex = this.getRealIndex();
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === realIndex);
        });
    }
    
    // در فارسی: next یعنی به چپ حرکت کنیم (کاهش اندیس)
    next() {
        if (this.isAnimating || this.products.length <= this.slidesToShow) return;
        this.isAnimating = true;
        this.currentSlide--;
        this.update();
        setTimeout(() => this.isAnimating = false, 400);
    }
    
    // در فارسی: prev یعنی به راست حرکت کنیم (افزایش اندیس)
    prev() {
        if (this.isAnimating || this.products.length <= this.slidesToShow) return;
        this.isAnimating = true;
        this.currentSlide++;
        this.update();
        setTimeout(() => this.isAnimating = false, 400);
    }
    
    goTo(index) {
        if (this.isAnimating || index < 0 || index >= this.totalSlides) return;
        this.isAnimating = true;
        
        const realIndex = this.getRealIndex();
        const diff = index - realIndex;
        
        // حرکت مستقیم به اسلاید مورد نظر
        this.currentSlide = this.clonedCount + index;
        
        this.update();
        setTimeout(() => this.isAnimating = false, 400);
    }
    
    handleResize() {
        const newSlides = window.innerWidth <= 768 ? 1 : window.innerWidth <= 992 ? 2 : 3;
        if (newSlides !== this.slidesToShow) {
            this.slidesToShow = newSlides;
            this.currentSlide = this.clonedCount;
            this.createDots();
            this.update(true);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof productsData !== 'undefined' && productsData.length > 0) {
        window.carousel = new MinimalCarousel(productsData);
        
        // اتوپلی ساده (حرکت به چپ - next در فارسی)
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                window.carousel?.next();
            }
        }, 5000);
    }
});

