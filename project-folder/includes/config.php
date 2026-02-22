<?php
// تنظیمات پایه
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/');
define('SITE_TITLE', 'فروشگاه پکیج‌های آموزش زبان انگلیسی');

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'english_courses_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// تنظیمات ایمیل
define('SITE_EMAIL', 'info@english-courses.ir');
define('ADMIN_EMAIL', 'admin@english-courses.ir');