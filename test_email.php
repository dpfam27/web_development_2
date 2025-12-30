<?php
include 'connect.php';
include 'mailer.php';

echo "<h1>TEST GỬI EMAIL</h1>";

// Test 1: Email xác nhận đơn hàng
echo "<h2>Test 1: Email xác nhận đơn hàng</h2>";
$items = [
    [
        'prod_name' => 'Whey Protein',
        'variant_name' => '2kg',
        'quantity' => 1,
        'price' => 45.99
    ]
];

$result = sendOrderConfirmationEmail('nghthuong2707@gmail.com', 'John Doe', 12345, 45.99, $items);

if ($result) {
    echo "<p style='color:green;'><strong>✓ Gửi email xác nhận đơn hàng THÀNH CÔNG!</strong></p>";
} else {
    echo "<p style='color:red;'><strong>✗ Gửi THẤT BẠI!</strong></p>";
}

// Test 2: Email liên hệ
echo "<h2>Test 2: Email liên hệ</h2>";
$result = sendContactEmail('Test User', 'nghthuong2707@gmail.com', 'Hỏi về sản phẩm', 'Tôi muốn biết thêm thông tin');

if ($result) {
    echo "<p style='color:green;'><strong>✓ Gửi email liên hệ THÀNH CÔNG!</strong></p>";
    echo "<p>Email sẽ tới: <strong>nghthuong2707@gmail.com</strong></p>";
} else {
    echo "<p style='color:red;'><strong>✗ Gửi THẤT BẠI!</strong></p>";
}

// Test 3: Email xác nhận liên hệ
echo "<h2>Test 3: Email xác nhận liên hệ cho khách</h2>";
$result = sendContactConfirmationEmail('nghthuong2707@gmail.com', 'Test User');

if ($result) {
    echo "<p style='color:green;'><strong>✓ Gửi email xác nhận liên hệ THÀNH CÔNG!</strong></p>";
} else {
    echo "<p style='color:red;'><strong>✗ Gửi THẤT BẠI!</strong></p>";
}
?>
