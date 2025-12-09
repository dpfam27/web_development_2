<?php
// BƯỚC 1: BẬT SESSION ĐỂ CÓ THỂ TRUY CẬP VÀ HỦY NÓ
include 'connect.php'; 

// Dọn dẹp tất cả các biến Session
$_SESSION = array();

// Hủy Session (Session ID trên Server)
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập (login.php)
header("Location: login.php");
exit;
?>