DROP DATABASE IF EXISTS product_manage;

CREATE DATABASE product_manage;
USE product_manage;


CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,   -- không cho trùng username
    email VARCHAR(255) NOT NULL UNIQUE,      -- không cho trùng email
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,        -- mỗi sản phẩm có tên riêng
    description TEXT,
    category VARCHAR(100),
    base_price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE product_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_name VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    
    -- Ngăn chặn TRÙNG tên variant của cùng một sản phẩm 
    UNIQUE(product_id, variant_name), 

    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,           -- 1 người chỉ có 1 giỏ hàng
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);


CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    variant_id INT NOT NULL,
    quantity INT NOT NULL,

    -- Ngăn chặn việc 1 variant bị thêm 2 lần vào cùng 1 cart
    UNIQUE(cart_id, variant_id),

    FOREIGN KEY (cart_id) REFERENCES carts(cart_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);


CREATE TABLE coupons (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,         -- mã giảm giá không được trùng
    discount_type ENUM('percent','fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    start_date DATE,
    end_date DATE,
    minimum_order_amount DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);


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


CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    variant_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,

    -- 1 đơn hàng không thể có 1 variant xuất hiện 2 lần
    UNIQUE(order_id, variant_id),

    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);


CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,            -- mỗi order chỉ có 1 payment
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('pending','paid','failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);


-- Tạo tài khoản admin mẫu (password: admin123)
INSERT INTO users (username, email, password_hash, role)
VALUES ('admin', 'admin@wheyshop.com', MD5('admin123'), 'admin');


--thêm cookie remember_token vào bảng users
USE product_manage;

-- 1. Tắt kiểm tra khóa ngoại tạm thời để xóa bảng cũ không bị lỗi
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Sửa bảng users: Thêm cột remember_token nếu chưa có
-- (Lệnh này sẽ chạy êm kể cả khi cột đã tồn tại hay chưa)
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "remember_token";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE users ADD remember_token VARCHAR(255) NULL;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Xóa bảng cũ gây lỗi
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS order_items;

-- 4. Tạo lại bảng cart_items (GIỎ HÀNG)
-- Logic: Xóa biến thể -> Xóa luôn dòng trong giỏ (CASCADE)
CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    variant_id INT NOT NULL, 
    quantity INT NOT NULL,
    UNIQUE(cart_id, variant_id),
    FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE
);

-- 5. Tạo lại bảng order_items (ĐƠN HÀNG)
-- Logic: Xóa biến thể -> Giữ đơn hàng, nhưng mã SP thành NULL (SET NULL)
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    variant_id INT NULL, -- QUAN TRỌNG: Phải cho phép NULL
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    UNIQUE(order_id, variant_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE SET NULL
);

-- 6. Bật lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;

-- Cập nhật mật khẩu admin sang bcrypt (mật khẩu vẫn là admin123)
-- Chạy lệnh này trong thẻ SQL của phpMyAdmin
UPDATE users 
SET password_hash = '$2y$10$2zcXNOZpZKTcyD6Rk0Fm4u9tZbmKddGVG.KeUDO2WEjM/aSkW2jJe' 
WHERE username = 'admin';