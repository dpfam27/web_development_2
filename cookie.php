<?php

/**
 * Hàm thiết lập một Cookie mới.
 * * @param string $name Tên của Cookie (ví dụ: 'theme_preference').
 * @param string $value Giá trị của Cookie (ví dụ: 'dark').
 * @param int $expiry Thời gian hết hạn tính bằng giây. Mặc định là 30 ngày (86400 * 30).
 * @param string $path Đường dẫn áp dụng Cookie (mặc định là toàn bộ website '/').
 * @param bool $secure Chỉ gửi qua HTTPS (nên dùng TRUE trong môi trường sản phẩm).
 * @param bool $httponly Chỉ truy cập qua HTTP/PHP, không thể truy cập qua JavaScript (tăng bảo mật).
 */
function set_my_cookie(string $name, string $value, int $expiry = 86400 * 30, string $path = '/', bool $secure = false, bool $httponly = true) {
    // time() là thời điểm hiện tại. Cộng thêm $expiry để có thời gian hết hạn trong tương lai.
    setcookie($name, $value, [
        'expires' => time() + $expiry,
        'path' => $path,
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Lax', // Thêm SameSite để tăng cường bảo mật CSRF
    ]);
}

/**
 * Hàm lấy giá trị của một Cookie đã lưu.
 * * @param string $name Tên của Cookie.
 * @return string|null Trả về giá trị của Cookie hoặc NULL nếu không tìm thấy.
 */
function get_my_cookie(string $name): ?string {
    // Kiểm tra biến toàn cục $_COOKIE
    return $_COOKIE[$name] ?? null;
}

/**
 * Hàm xóa một Cookie (bằng cách thiết lập thời gian hết hạn về quá khứ).
 * * @param string $name Tên của Cookie cần xóa.
 * @param string $path Đường dẫn áp dụng Cookie.
 */
function delete_my_cookie(string $name, string $path = '/') {
    // Thiết lập thời gian hết hạn (expires) là 1 giờ trước
    setcookie($name, '', [
        'expires' => time() - 3600,
        'path' => $path,
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

?>