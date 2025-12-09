<?php
include 'connect.php';
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

$id = $_GET['id'] ?? null; // Lấy ID sản phẩm từ URL
$product = null;

// Xử lý khi người dùng nhấn nút UPDATE
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $image_path = $_POST['old_image'] ?? ''; // Giữ đường dẫn ảnh cũ mặc định

    // Kiểm tra xem có file ảnh mới được upload lên không
    if (isset($_FILES["image"]) && $_FILES["image"]["size"] > 0) {
        // 1. Xóa ảnh cũ (nếu có)
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
        
        // 2. Tải ảnh mới lên
        $target_dir = "uploads/";
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file; // Cập nhật đường dẫn ảnh mới
        } else {
             // Xử lý lỗi upload nếu cần
        }
    }

    // Cập nhật dữ liệu vào DB
    $sql_update = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql_update);
    // Chuỗi định dạng: s=string, d=double/float, s=string, i=integer
    mysqli_stmt_bind_param($stmt, "ssdsi", $name, $description, $price, $image_path, $id);
    mysqli_stmt_execute($stmt);

    // Cập nhật xong thì quay về trang chủ
    header("Location: index.php");
    exit();
} 

// Phần code hiển thị form
if ($id && is_numeric($id)) {
    $sql_select = "SELECT id, name, description, price, image_url FROM products WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql_select);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $product = mysqli_fetch_assoc($result);
    } else {
        // Không tìm thấy sản phẩm, chuyển hướng
        header("Location: index.php");
        exit();
    }
} else {
    // Không có ID, chuyển hướng
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h3>
            </div>
            <div class="card-body">
                <form action="edit.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" name="price" step="0.01" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Current Image</label><br>
                        <?php if(!empty($product['image_url']) && file_exists($product['image_url'])): ?>
                            <img src="<?php echo $product['image_url']; ?>" style="width: 150px;">
                        <?php else: ?>
                            <p>No current image.</p>
                        <?php endif; ?>
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Upload New Image (leave blank to keep current image)</label>
                        <input type="file" name="image" class="form-control-file">
                    </div>
                    
                    <button type="submit" name="update" class="btn btn-primary">Update Product</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>