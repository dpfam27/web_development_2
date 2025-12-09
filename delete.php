<?php
include "connect.php";
include 'auth.php';

require_admin();

// *************************************************************
// BẢO VỆ TRANG: NẾU CHƯA ĐĂNG NHẬP, CHUYỂN HƯỚNG VỀ LOGIN
// *************************************************************
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
// *************************************************************

// Kiểm tra xem có ID được gửi qua URL không và có phải là số không
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = $_GET["id"];

    // --- Bước 1: Lấy đường dẫn ảnh để xóa file vật lý (nếu có) ---
    $sql_get_image = "SELECT image_url FROM products WHERE id = ?";
    $stmt_get = mysqli_prepare($link, $sql_get_image);
    mysqli_stmt_bind_param($stmt_get, "i", $id);
    mysqli_stmt_execute($stmt_get);
    $result = mysqli_stmt_get_result($stmt_get);

    if ($row = mysqli_fetch_assoc($result)) {
        $image_path = $row['image_url'];

        // Kiểm tra file có tồn tại không rồi mới xóa
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // --- Bước 2: Xóa sản phẩm trong database ---
    $sql_delete = "DELETE FROM products WHERE id = ?";
    $stmt_delete = mysqli_prepare($link, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);
    mysqli_stmt_execute($stmt_delete);

    // --- Bước 3: Quay lại trang chính ---
    header("Location: index.php");
    exit();
} else {
    // Nếu không có ID hợp lệ, cũng quay lại trang chính
    header("Location: index.php");
    exit();
}
?>