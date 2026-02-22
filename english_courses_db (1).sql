-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 07:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `english_courses_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(2, 2, 3, 1, '2026-02-05 17:18:14'),
(3, 3, 2, 1, '2024-02-02 05:50:00'),
(4, 3, 5, 3, '2024-02-02 11:15:00'),
(5, 4, 4, 1, '2024-02-03 12:40:00'),
(6, 4, 6, 2, '2024-02-03 12:45:00'),
(7, 5, 7, 1, '2024-02-04 05:00:00'),
(8, 5, 8, 1, '2024-02-04 05:30:00'),
(9, 6, 9, 1, '2024-02-05 09:55:00'),
(10, 6, 10, 2, '2024-02-05 10:00:00'),
(13, 2, 7, 2, '2026-02-05 17:12:30'),
(14, 1, 9, 2, '2026-02-03 19:10:18'),
(15, 2, 4, 3, '2026-02-05 18:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `created_at`) VALUES
(1, 'ORD-65A3B1C2', 2, 1398000.00, 'completed', 'زرین‌پال', 'paid', '2024-01-15 11:00:00'),
(2, 'ORD-65A3B1D3', 3, 1497000.00, 'completed', 'زرین‌پال', 'paid', '2024-01-16 08:15:00'),
(3, 'ORD-65A3B1E4', 4, 957000.00, 'processing', 'زرین‌پال', 'pending', '2024-01-17 12:50:00'),
(4, 'ORD-65A3B1F5', 5, 1048000.00, 'completed', 'زرین‌پال', 'paid', '2024-01-18 05:45:00'),
(5, 'ORD-65A3B1G6', 6, 2157000.00, 'pending', NULL, 'pending', '2024-01-19 10:10:00'),
(6, 'ORD-65A3B1H7', 7, 199000.00, 'cancelled', NULL, 'failed', '2024-01-20 06:40:00'),
(7, 'ORD-65A3B1I8', 8, 399000.00, 'completed', 'زرین‌پال', 'paid', '2024-01-21 12:00:00'),
(8, 'ORD-65A3B1J9', 9, 249000.00, 'processing', 'زرین‌پال', 'pending', '2024-01-22 05:15:00'),
(9, 'ORD-65A3B1K0', 10, 299000.00, 'completed', 'زرین‌پال', 'paid', '2024-01-23 08:50:00'),
(10, 'ORD-65A3B1L1', 2, 599000.00, 'completed', 'زرین‌پال', 'paid', '2024-01-24 14:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 199000.00),
(2, 1, 3, 1, 999000.00),
(3, 1, 5, 1, 249000.00),
(4, 2, 2, 2, 399000.00),
(5, 2, 4, 1, 299000.00),
(6, 2, 6, 1, 199000.00),
(7, 3, 7, 1, 449000.00),
(8, 3, 8, 1, 149000.00),
(9, 3, 9, 1, 359000.00),
(10, 4, 10, 2, 179000.00),
(11, 4, 1, 1, 199000.00),
(12, 4, 2, 1, 399000.00),
(13, 5, 3, 1, 999000.00),
(14, 5, 5, 1, 249000.00),
(15, 5, 7, 1, 449000.00),
(16, 5, 9, 1, 359000.00),
(17, 6, 1, 1, 199000.00),
(18, 7, 2, 1, 399000.00),
(19, 8, 5, 1, 249000.00),
(20, 9, 4, 1, 299000.00),
(21, 10, 7, 1, 449000.00),
(22, 10, 8, 1, 149000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `ref_id` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount`, `ref_id`, `status`, `payment_date`) VALUES
(1, 1, 1398000.00, 'ZP123456789', 'success', '2024-01-15 11:05:00'),
(2, 2, 1497000.00, 'ZP123456790', 'success', '2024-01-16 08:20:00'),
(3, 4, 1048000.00, 'ZP123456791', 'success', '2024-01-18 05:50:00'),
(4, 7, 399000.00, 'ZP123456792', 'success', '2024-01-21 12:05:00'),
(5, 9, 299000.00, 'ZP123456793', 'success', '2024-01-23 08:55:00'),
(6, 10, 599000.00, 'ZP123456794', 'success', '2024-01-24 14:30:00'),
(7, 3, 957000.00, NULL, 'pending', '2026-02-02 18:06:57'),
(8, 5, 2157000.00, NULL, 'pending', '2026-02-02 18:06:57'),
(9, 6, 199000.00, NULL, 'failed', '2024-01-20 06:45:00'),
(10, 8, 249000.00, NULL, 'pending', '2026-02-02 18:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `discount_price`, `image_url`, `duration`, `level`, `features`, `created_at`, `is_active`) VALUES
(1, 'پکیج مبتدی مطلق', 'آموزش زبان انگلیسی از صفر مطلق، مناسب برای افرادی که هیچ آشنایی با زبان انگلیسی ندارند. این دوره با روش‌های ساده و روان تدریس شده است.', 299000.00, 199000.00, '1.jpg', '۳ ماه', 'مقدماتی', '[\"۲۰ ساعت ویدیو آموزشی\", \"۵۰ تمرین عملی\", \"پشتیبانی آنلاین\", \"گواهینامه پایان دوره\", \"دسترسی مادام‌العمر\"]', '2024-01-01 05:30:00', 1),
(2, 'پکیج مکالمه روزمره', 'آموزش مکالمه انگلیسی در موقعیت‌های روزمره مانند خرید، رستوران، فرودگاه و... مناسب برای سفر و زندگی در کشورهای انگلیسی زبان.', 499000.00, 399000.00, '2.jpg', '۴ ماه', 'متوسط', '[\"۳۰ ساعت ویدیو آموزشی\", \"۱۰۰ تمرین مکالمه\", \"تصحیح تلفظ\", \"گواهینامه بین‌المللی\", \"کارگاه‌های آنلاین هفتگی\"]', '2024-01-02 06:30:00', 1),
(3, 'پکیج آمادگی آیلتس', 'آمادگی کامل برای آزمون آیلتس با تدریس اساتید بین‌المللی. پوشش کامل تمام مهارت‌های Listening, Reading, Writing, Speaking.', 1299000.00, 999000.00, '3.jpg', '۶ ماه', 'پیشرفته', '[\"۶۰ ساعت ویدیو آموزشی\", \"۲۰ آزمون آزمایشی\", \"راهنمای نوشتن Essay\", \"مصاحبه شبیه‌سازی شده\", \"گواهینامه معتبر\"]', '2024-01-03 07:30:00', 1),
(4, 'پکیج تلفظ پیشرفته', 'آموزش تلفظ صحیح تمام اصوات انگلیسی با لهجه American. مناسب برای افرادی که می‌خواهند مثل native صحبت کنند.', 399000.00, 299000.00, '4.jpg', '۲ ماه', 'متوسط', '[\"۱۵ ساعت ویدیو آموزشی\", \"تمرین‌های تلفظ\", \"آنالیز صدای شما\", \"گواهینامه تخصصی\", \"دسترسی دائمی\"]', '2024-01-04 08:30:00', 1),
(5, 'پکیج گرامر جامع', 'آموزش کامل گرامر انگلیسی از ساده تا پیشرفته با مثال‌های کاربردی. مناسب برای تقویت مهارت نوشتاری و گفتاری.', 349000.00, 249000.00, 'product_1770303111.jpg', '۳ ماه', 'مقدماتی', '[\"۲۵ ساعت ویدیو آموزشی\",\"۲۰۰ تمرین گرامری\",\"تست‌های ارزیابی\",\"راهنمای خطاهای رایج\",\"گواهینامه تکمیل دوره\"]', '2024-01-05 09:30:00', 1),
(6, 'پکیج لغات ضروری', 'آموزش ۲۰۰۰ لغت پرکاربرد انگلیسی با روش‌های یادگیری سریع و ماندگار. مناسب برای افزایش دایره لغات در کوتاه‌ترین زمان.', 299000.00, 199000.00, '6.jpg', '۲ ماه', 'مقدماتی', '[\"۱۸ ساعت ویدیو آموزشی\", \"فلش کارت دیجیتال\", \"تمرین‌های مرور\", \"اپلیکیشن موبایل\", \"گواهینامه پایان دوره\"]', '2024-01-06 10:30:00', 1),
(7, 'پکیج Business English', 'آموزش انگلیسی تجاری برای محیط کار، مذاکرات، ایمیل‌نویسی و ارائه‌های کاری. مناسب برای مدیران و کارمندان.', 599000.00, 449000.00, '7.jpg', '۴ ماه', 'پیشرفته', '[\"۳۵ ساعت ویدیو آموزشی\", \"مکالمه‌های تجاری\", \"نمونه ایمیل و گزارش\", \"کارگاه‌های عملی\", \"گواهینامه حرفه‌ای\"]', '2024-01-07 11:30:00', 1),
(8, 'پکیج کودکان', 'آموزش انگلیسی به روش بازی و سرگرمی برای کودکان ۶ تا ۱۲ سال. با استفاده از انیمیشن و آهنگ‌های آموزشی.', 249000.00, 149000.00, '8.jpg', '۶ ماه', 'مقدماتی', '[\"۴۰ ساعت ویدیو آموزشی\", \"بازی‌های تعاملی\", \"آهنگ‌های آموزشی\", \"ورک‌شیت چاپی\", \"گواهینامه کودک\"]', '2024-01-08 12:30:00', 1),
(9, 'پکیج TOEFL iBT', 'آمادگی کامل برای آزمون TOEFL با استراتژی‌های تست‌زنی و تکنیک‌های زمان‌بندی. شامل نمونه سوالات واقعی.', 1199000.00, 899000.00, '9.jpg', '۵ ماه', 'پیشرفته', '[\"۵۵ ساعت ویدیو آموزشی\", \"۱۵ آزمون آزمایشی\", \"راهنمای Integrated Tasks\", \"تصحیح Writing\", \"گواهینامه آمادگی\"]', '2024-01-09 13:30:00', 1),
(10, 'پکیج Listening حرفه‌ای', 'تقویت مهارت شنیداری با لهجه‌های مختلف انگلیسی. شامل اخبار، مکالمات، پادکست‌ها و فیلم‌های آموزشی.', 279000.00, 179000.00, '10.jpg', '۲ ماه', 'متوسط', '[\"۲۴ ساعت ویدیو آموزشی\",\"۲۰۰ تمرین شنیداری\",\"تمرین با فیلم و سریال\",\"گواهینامه تخصصی\",\"دسترسی دائمی\"]', '2024-01-10 14:30:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `created_at`, `is_admin`) VALUES
(1, 'admin', 'admin@englishmaster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', '09123456789', '2024-01-01 06:30:00', 1),
(2, 'john_doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'جان دو', '09121111111', '2024-01-02 08:00:00', 0),
(3, 'maryam89', 'maryam89@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مریم محمدی', '09122222222', '2024-01-03 10:50:00', 0),
(4, 'ali_reza', 'ali.reza@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'علی رضایی', '09123333333', '2024-01-04 05:45:00', 0),
(5, 'sara_j', 'sara.j@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'سارا جعفری', '09124444444', '2024-01-05 13:15:00', 0),
(6, 'hossein_t', 'hossein.t@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'حسین طالبی', '09125555555', '2024-01-06 09:40:00', 0),
(7, 'fatemeh_a', 'fatemeh.a@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'فاطمه امینی', '09126666666', '2024-01-07 05:00:00', 0),
(8, 'mohammad_k', 'mohammad.k@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'محمد کریمی', '09127777777', '2024-01-08 08:55:00', 0),
(9, 'narges_m', 'narges.m@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'نرگس موسوی', '09128888888', '2024-01-09 12:10:00', 0),
(10, 'reza_h', 'reza.h@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'رضا حسینی', '09129999999', '2024-01-10 14:25:00', 0),
(11, 'mohammad', 'mohammad@gmail.com', '$2y$10$F0/OGevCA1.udyiyf/FZru90qaEf8DJZ7ZnV3Pq1Xws8OyTYcQoBe', '', '', '2026-02-05 16:48:49', 0),
(12, 'aminooo', 'amin369@gmail.com', '$2y$10$aurq.PBfEIA1Rc7qsyBKNuc2aybGj02bF7ZjAs2yJg.AejHko0xye', 'امین نظری', '09121111111', '2026-02-05 17:00:47', 0),
(13, 'admir', 'admir@englishmaster.com', '$2y$10$AGg60ro3L/hVVXI43MmLGuPFwRQsJ70H2tVvZzaRRu7wSN5d5qgr6', 'امیر نظری', '09123456789', '2026-02-05 18:22:03', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
