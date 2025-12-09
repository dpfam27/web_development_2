DROP DATABASE IF EXISTS product_manage;

CREATE DATABASE product_manage;
USE product_manage;

-- Lệnh tạo bảng 'products'
CREATE TABLE products (
    
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10, 3) NOT NULL,
    image_url VARCHAR(255) NULL,
    created_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



DROP TABLE if EXISTS users;
CREATE TABLE users (
    id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tạo tài khoản admin mẫu (password: admin123)
INSERT INTO users (username, password, role) VALUES ('admin', md5('admin123'), 'admin');
ALTER TABLE users ADD COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user';