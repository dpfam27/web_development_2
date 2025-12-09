<?php

/**
 * Hàm tạo Cookie an toàn
 * @param string $name  : Tên cookie (ví dụ: 'remember_token')
 * @param string $value : Giá trị muốn lưu
 * @param int    $days  : Số ngày tồn tại (mặc định 30 ngày)
 */
function set_my_cookie($name, $value, $days = 30) {
    // Tính thời gian hết hạn (Hiện tại + số ngày * 86400 giây/ngày)
    $expires = time() + ($days * 86400);
    
    // setcookie(name, value, expires, path, domain, secure, httponly)
    // Quan trọng: tham số cuối cùng là true (HttpOnly) để chống hacker dùng Javascript lấy trộm cookie
    setcookie($name, $value, $expires, "/", "", false, true);
}

/**
 * Hàm lấy giá trị Cookie
 * @param string $name : Tên cookie cần lấy
 * @return string|null : Trả về giá trị hoặc null nếu không tìm thấy
 */
function get_my_cookie($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
}

/**
 * Hàm xóa Cookie
 * @param string $name : Tên cookie cần xóa
 */
function delete_my_cookie($name) {
    // Đặt thời gian hết hạn về quá khứ (trừ đi 1 giờ) để trình duyệt tự xóa
    setcookie($name, "", time() - 3600, "/");
}

?>