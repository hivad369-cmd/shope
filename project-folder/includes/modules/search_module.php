<?php
// search_module.php - ماژول جستجوی ساده محصولات
?>

<section class="search-module">
    <div class="container">
        <div class="search-container">
            <div class="search-header">
                <h2><i class="fas fa-search"></i> جستجوی دوره‌ها</h2>
                <p>دوره مورد نظر خود را پیدا کنید</p>
            </div>
            
            <form action="pages/products.php" method="GET" class="search-form">
                <div class="search-wrapper">
                    <input type="text" 
                           name="search" 
                           placeholder="جستجوی دوره‌ها (مکالمه، گرامر، آیلتس...)"
                           autocomplete="off"
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                        جستجو
                    </button>
                </div>
            </form>
            
            <div class="search-tags">
                <span class="tags-title">پیشنهادات:</span>
                <div class="tags-container">
                    <a href="pages/products.php?search=مکالمه" class="search-tag">مکالمه</a>
                    <a href="pages/products.php?search=گرامر" class="search-tag">گرامر</a>
                    <a href="pages/products.php?search=آیلتس" class="search-tag">آیلتس</a>
                    <a href="pages/products.php?search=تلفظ" class="search-tag">تلفظ</a>
                    <a href="pages/products.php?search=کودکان" class="search-tag">کودکان</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* استایل ماژول جستجو - هماهنگ با سایت */
.search-module {
    background: #f8f9fa;
    padding: 30px 0;
    margin: 20px 0;
    border-radius: 10px;
    border: 1px solid #eaeaea;
}

.search-container {
    max-width: 700px;
    margin: 0 auto;
    text-align: center;
}

.search-header h2 {
    color: #2c3e50;
    font-size: 1.5rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.search-header h2 i {
    color: #3498db;
}

.search-header p {
    color: #7f8c8d;
    font-size: 0.95rem;
    margin-bottom: 25px;
}

/* فرم جستجو */
.search-form {
    margin-bottom: 20px;
}

.search-wrapper {
    display: flex;
    gap: 10px;
    max-width: 600px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .search-wrapper {
        flex-direction: column;
    }
}

.search-wrapper input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
    background: white;
}

.search-wrapper input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.search-button {
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0 25px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    min-width: 100px;
    justify-content: center;
}

.search-button:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

/* تگ‌های جستجو */
.search-tags {
    margin-top: 20px;
}

.tags-title {
    display: block;
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.tags-container {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.search-tag {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s;
    border: 1px solid #ddd;
}

.search-tag:hover {
    background: #3498db;
    color: white;
    border-color: #3498db;
    transform: translateY(-2px);
}

/* پیشنهادات خودکار (اختیاری) */
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 100;
    max-height: 250px;
    overflow-y: auto;
    display: none;
}

.suggestion-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    text-align: right;
    transition: background 0.3s;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* انیمیشن */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-module {
    animation: fadeIn 0.5s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-wrapper input');
    const searchForm = document.querySelector('.search-form');
    
    // فقط اگر input وجود دارد
    if (!searchInput) return;
    
    // ایجاد کانتینر برای پیشنهادات (اختیاری)
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'search-suggestions';
    searchInput.parentNode.appendChild(suggestionsContainer);
    
    // تایمر برای جلوگیری از درخواست‌های زیاد
    let searchTimer;
    
    // رویداد تایپ برای پیشنهادات (اختیاری)
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        // تاخیر برای جلوگیری از درخواست‌های زیاد
        searchTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });
    
    // کلیک خارج از باکس
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // ارسال فرم
    searchForm.addEventListener('submit', function(e) {
        const query = searchInput.value.trim();
        if (!query) {
            e.preventDefault();
            searchInput.focus();
            searchInput.style.borderColor = '#e74c3c';
            
            // نمایش پیام خطا
            setTimeout(() => {
                searchInput.style.borderColor = '#ddd';
            }, 2000);
            
            return false;
        }
    });
    
    // جستجوی پیشنهادات (اختیاری)
    async function fetchSuggestions(query) {
        try {
            const response = await fetch('../ajax/search_suggestions.php?q=' + encodeURIComponent(query));
            const data = await response.json();
            
            if (data.success && data.suggestions && data.suggestions.length > 0) {
                showSuggestions(data.suggestions);
            } else {
                suggestionsContainer.style.display = 'none';
            }
        } catch (error) {
            console.error('خطا در دریافت پیشنهادات:', error);
            suggestionsContainer.style.display = 'none';
        }
    }
    
    // نمایش پیشنهادات (اختیاری)
    function showSuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';
        
        suggestions.forEach(item => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.textContent = item.name;
            
            div.addEventListener('click', function() {
                searchInput.value = item.name;
                suggestionsContainer.style.display = 'none';
                searchForm.submit();
            });
            
            suggestionsContainer.appendChild(div);
        });
        
        suggestionsContainer.style.display = 'block';
    }
    
    // کلیک روی تگ‌های جستجو
    document.querySelectorAll('.search-tag').forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            window.location.href = href;
        });
    });
});
</script>