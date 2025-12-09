<?php
/**
 * Kiểm tra người dùng có phải admin không
 * @return bool
 */
function is_admin() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && 
           isset($_SESSION["role"]) && $_SESSION["role"] === 'admin';
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

/**
 * Bảo vệ trang: Yêu cầu đăng nhập
 */
function require_login() {
    if (!is_logged_in()) {
        header("location: login.php");
        exit;
    }
}

/**
 * Bảo vệ trang: Yêu cầu quyền admin
 */
function require_admin() {
    if (!is_admin()) {
        header("location: index.php");
        exit;
    }
}

?>