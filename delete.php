<?php
include 'connect.php';
include 'auth.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'product'; // Mặc định xóa product

if ($id) {
    if ($type == 'variant') {
        // Xóa 1 biến thể
        $pid = $_GET['pid']; // Lấy ID sản phẩm cha để quay lại
        mysqli_query($link, "DELETE FROM product_variants WHERE variant_id = $id");
        header("Location: edit.php?id=$pid"); // Quay lại trang sửa
    } else {
        // Xóa toàn bộ sản phẩm
        // (Do đã set ON DELETE CASCADE trong DB ở Bước 1, variants sẽ tự mất)
        mysqli_query($link, "DELETE FROM products WHERE product_id = $id");
        header("Location: index.php"); // Quay lại trang chủ
    }
} else {
    header("Location: index.php");
}
?>