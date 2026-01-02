DROP DATABASE IF EXISTS product_manage;
CREATE DATABASE product_manage;
USE product_manage;

-- 2. Bảng Users (Đã thêm cột remember_token)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    remember_token VARCHAR(255) NULL, -- Cột này quan trọng cho chức năng "Ghi nhớ đăng nhập"
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Bảng Products (Thông tin chung)
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    category VARCHAR(100),
    base_price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Bảng Product Variants (Biến thể: Vị, Size...)
CREATE TABLE product_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_name VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    
    UNIQUE(product_id, variant_name),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- 5. Bảng Coupons (Mã giảm giá)
CREATE TABLE coupons (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent','fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    start_date DATE,
    end_date DATE,
    minimum_order_amount DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

-- 6. Bảng Orders (Đơn hàng)
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    coupon_id INT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    shipping_address TEXT,
    payment_method VARCHAR(50),
    status ENUM('pending','paid','processing','shipped','completed','cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id)
);

-- 7. Bảng Order Items 
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    variant_id INT NULL, 
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    UNIQUE(order_id, variant_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE SET NULL
);

-- 8. Bảng Carts 
CREATE TABLE carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE, 
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 9. Bảng Cart Items 
CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    variant_id INT NOT NULL, 
    quantity INT NOT NULL,
    UNIQUE(cart_id, variant_id),
    FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE
);

-- 10. Bảng Payments (Thanh toán)
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('pending','paid','failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- 11. TẠO TÀI KHOẢN ADMIN CHUẨN (Password: 123456)
-- Hash này tương ứng với chuỗi '123456', đảm bảo đăng nhập được ngay với file login.php hiện tại.
INSERT INTO users (username, email, password_hash, role) 
VALUES (
    'admin', 
    'admin@wheyshop.com', 
    '$2y$10$2zcXNOZpZKTcyD6Rk0Fm4u9tZbmKddGVG.KeUDO2WEjM/aSkW2jJe', 
    'admin'
);

-- 12. Bảng Product Reviews (Đánh giá sản phẩm - Giống Shopee)
CREATE TABLE product_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT NOT NULL,
    review_images TEXT NULL, -- Lưu nhiều ảnh (phân cách bằng dấu phẩy)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id)
);

-- (Tùy chọn) Thêm 1 Coupon mẫu để test
INSERT INTO coupons (code, discount_type, value, start_date, end_date, minimum_order_amount)
VALUES ('SALE10', 'percent', 10.00, '2025-12-01', '2026-01-31', 50.00);