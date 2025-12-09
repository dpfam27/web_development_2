<?php
// 1. Chỉ bật session nếu nó CHƯA chạy (Fix lỗi Notice hình 2)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'connect.php'; 
include_once 'cookie.php';

function is_logged_in() {
    global $link;

    // Check Session trước
    if (isset($_SESSION['user_id'])) {
        return true;
    }

    // Check Cookie Remember Me
    $token = get_my_cookie('remember_token');
    
    // Chỉ chạy query nếu có token và biến $link kết nối thành công
    if ($token && $link) {
        $token_safe = mysqli_real_escape_string($link, $token);
        
        // Cột remember_token giờ đã có trong DB nhờ Bước 1
        $sql = "SELECT * FROM users WHERE remember_token = '$token_safe' LIMIT 1";
        $result = mysqli_query($link, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Gia hạn cookie
            set_my_cookie('remember_token', $token, 30);
            return true;
        }
    }

    return false;
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function is_admin() {
    if (is_logged_in()) {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    return false;
}

function require_admin() {
    if (!is_admin()) {
        // Có thể redirect về index thay vì die để thân thiện hơn
        echo "<script>alert('Bạn không có quyền Admin!'); window.location='index.php';</script>";
        exit;
    }
}
?>