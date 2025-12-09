<?php
// BƯỚC 1: BẬT SESSION ĐỂ CÓ THỂ TRUY CẬP VÀ HỦY NÓ
include 'connect.php';
include 'cookie.php';

// Lưu user id hiện tại (nếu có) để xóa remember_token trên DB
// Lưu ý: Đảm bảo session key khớp với lúc login (thường là 'user_id' hoặc 'id')
$user_id = $_SESSION['id'] ?? get_my_cookie('user_id');

// Dọn dẹp tất cả các biến Session
$_SESSION = array();

// Hủy Session (Session ID trên Server)
session_destroy();

// Nếu có user_id, xóa remember_token trên DB
if (!empty($user_id)) {
	$user_id_db = (int)$user_id;
    // ĐÃ SỬA: Đổi 'id' thành 'user_id' cho khớp với database
	@mysqli_query($link, "UPDATE users SET remember_token=NULL WHERE user_id=$user_id_db");
}

// Xóa tất cả các cookie liên quan đến đăng nhập
delete_my_cookie('user_loggedin');
delete_my_cookie('user_id');
delete_my_cookie('user_username');
delete_my_cookie('user_role');
delete_my_cookie('remember_token');

// Chuyển hướng người dùng về trang đăng nhập (login.php)
header("Location: login.php");
exit;
?>