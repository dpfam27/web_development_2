# Hướng Dẫn Cài Đặt PHPMailer và Email Configuration

## 📋 Tổng Quan

Hệ thống email đã được triển khai với 3 file chính:
- **mail_config.php** - Cấu hình email credentials
- **mailer.php** - Helper functions để gửi email
- **checkout.php** & **contact.php** - Tích hợp gửi email

## 🔧 Bước 1: Cài Đặt PHPMailer via Composer

Nếu project của bạn chưa có `composer.json`, hãy chạy:

```bash
composer init
```

Sau đó cài đặt PHPMailer:

```bash
composer require phpmailer/phpmailer
```

Điều này sẽ tạo ra thư mục `vendor/` và file `composer.json`

## 🔐 Bước 2: Cấu Hình Gmail Account

### 2.1 Tạo App Password (nếu dùng Gmail)

1. Truy cập: https://myaccount.google.com/security
2. Bật "2-Step Verification" nếu chưa bật
3. Vào lại Security → App passwords (sẽ hiện sau khi bật 2FA)
4. Chọn "Mail" và "Windows Computer"
5. Sao chép App Password được cấp
6. Lưu ý: **Không** dùng mật khẩu Gmail thường, phải dùng App Password

### 2.2 Cấu Hình File mail_config.php

Mở file `mail_config.php` và điền thông tin:

```php
// Email configuration
define('MAIL_HOST', 'smtp.gmail.com'); // Gmail SMTP server
define('MAIL_PORT', 587); // TLS port
define('MAIL_ENCRYPTION', 'tls'); // Use TLS encryption

// Email account (Gmail)
define('MAIL_USERNAME', 'your_gmail@gmail.com'); // Your Gmail account
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Your Gmail app password (16 ký tự)

// Display name (Hiển thị là tên store)
define('MAIL_FROM_ADDRESS', 'support@wheystore.com');
define('MAIL_FROM_NAME', 'WheyStore Support');

// Support email (Thực chất nhận ở email cá nhân)
define('SUPPORT_EMAIL', 'your_personal_email@gmail.com');
```

**Ví dụ thực tế:**
```php
define('MAIL_USERNAME', 'wheystore.shop@gmail.com');
define('MAIL_PASSWORD', 'abcd efgh ijkl mnop'); // App password từ Google
define('SUPPORT_EMAIL', 'owner@gmail.com'); // Email cá nhân sẽ nhận liên hệ
```

## 📧 Bước 3: Hàm Email Sẵn Có

### Gửi Email Xác Nhận Đơn Hàng
```php
sendOrderConfirmationEmail(
    $customer_email,  // Email khách
    $customer_name,   // Tên khách
    $order_id,        // ID đơn hàng
    $total_amount,    // Tổng tiền
    $items            // Mảng chi tiết sản phẩm
);
```

### Gửi Email Liên Hệ Tới Support
```php
sendContactEmail(
    $customer_name,   // Tên khách
    $customer_email,  // Email khách
    $subject,         // Tiêu đề
    $message          // Nội dung tin nhắn
);
```

### Gửi Email Xác Nhận Liên Hệ Cho Khách
```php
sendContactConfirmationEmail(
    $customer_email,  // Email khách
    $customer_name    // Tên khách
);
```

## 🧪 Bước 4: Test Email

### 4.1 Tạo File Test (test_email.php)

```php
<?php
include 'connect.php';
include 'mailer.php';

// Test 1: Gửi email xác nhận đơn hàng
echo "Test 1: Gửi email xác nhận đơn hàng...\n";
$items = [
    [
        'prod_name' => 'Whey Protein',
        'variant_name' => '2kg',
        'quantity' => 1,
        'price' => 45.99
    ]
];

$result = sendOrderConfirmationEmail(
    'test@example.com',
    'John Doe',
    12345,
    45.99,
    $items
);

echo $result ? "✓ Gửi thành công!\n" : "✗ Gửi thất bại!\n";

// Test 2: Gửi email liên hệ
echo "\nTest 2: Gửi email liên hệ...\n";
$result = sendContactEmail(
    'John Doe',
    'customer@example.com',
    'Hỏi về sản phẩm',
    'Tôi muốn biết thêm về sản phẩm X'
);

echo $result ? "✓ Gửi thành công!\n" : "✗ Gửi thất bại!\n";
?>
```

Chạy bằng browser: `http://localhost/web_development_2/test_email.php`

### 4.2 Kiểm Tra Error Log

Nếu có lỗi, kiểm tra file log:
```bash
# Trên Windows
type php_error.log

# Hoặc xem trong thư mục project
dir | find "error"
```

## 🐛 Khắc Phục Sự Cố

### Lỗi: "SMTP Error: Could not authenticate"
- ✓ Kiểm tra MAIL_USERNAME và MAIL_PASSWORD trong mail_config.php
- ✓ Đảm bảo đã tạo App Password (không phải mật khẩu Gmail thường)
- ✓ Gmail account hỗ trợ SMTP (không phải tài khoản work/business)

### Lỗi: "SSL: Certificate problem"
Thêm vào mail_config.php hoặc mailer.php:
```php
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
```

### Email không gửi nhưng không có lỗi
- ✓ Kiểm tra SUPPORT_EMAIL có chính xác không
- ✓ Xem spam folder
- ✓ Kiểm tra connection internet

### Lỗi "Class not found: PHPMailer"
```bash
composer dump-autoload
```

## 📊 Flow Hiện Tại

### Khi Khách Đặt Hàng:
```
1. Khách nhấn "XÁC NHẬN ĐẶT HÀNG" (checkout.php)
2. Hệ thống lưu đơn vào database
3. Tự động gửi email xác nhận tới customer_email
4. Chuyển hướng đến trang Home với thông báo thành công
```

### Khi Khách Liên Hệ:
```
1. Khách điền form và nhấn "Send Message" (contact.php)
2. Email được gửi tới SUPPORT_EMAIL (email cá nhân)
3. Tự động gửi email xác nhận tới customer_email
4. Hiển thị thông báo thành công trên giao diện
```

## ✨ Tính Năng Nâng Cao

### Tùy Chỉnh Email Template

Mở file `mailer.php`, tìm phần `$html_body = '...'` để chỉnh sửa:
- Đổi logo, màu sắc
- Thêm banner quảng cáo
- Thay đổi copy/text

### Gửi Email Cho Admin Khi Có Đơn Hàng Mới

Thêm vào checkout.php (sau `sendOrderConfirmationEmail`):
```php
// Gửi email thông báo cho admin
sendAdminOrderNotification($order_id, $customer_name, $final_total);
```

### Gửi Email Nhắc Nhở Khách Abandon Cart

Cần tạo cron job hoặc scheduled task để chạy:
```php
include 'mailer.php';
// Lấy danh sách cart cũ chưa checkout
// Gửi email nhắc nhở
```

## 📝 Lưu Ý Quan Trọng

1. **Bảo Mật:** Không commit file `mail_config.php` lên Git nếu đó là public repo
2. **Rate Limit:** Gmail giới hạn ~500 email/ngày, nên dùng SendGrid/Mailgun cho production
3. **Domain Email:** "support@wheystore.com" chỉ là hiển thị, không cần domain thực
4. **Testing:** Luôn test trước khi triển khai live
5. **Encoding:** Tất cả email dùng UTF-8 để hỗ trợ Tiếng Việt

## 🚀 Triển Khai Production

Để gửi email từ domain riêng (ví dụ: support@wheystore.com thực sự):

1. Mua hosting + domain
2. Setup SPF, DKIM, DMARC DNS records
3. Dùng SendGrid, Mailgun, hoặc Amazon SES
4. Update MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD

## 📞 Hỗ Trợ

Nếu có lỗi, kiểm tra:
- [ ] Composer đã cài PHPMailer?
- [ ] Mail_config.php có thông tin chính xác?
- [ ] Gmail App Password có đúng format?
- [ ] Database có bảng `users` với cột `email`?
- [ ] Firewall/ISP có chặn SMTP port 587?

---

**Versionn:** 1.0  
**Last Updated:** 2025-12-30
