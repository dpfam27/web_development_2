<?php
include 'connect.php';
include 'auth.php';

// Chỉ admin mới được phép
require_admin();

if (isset($_POST['insert'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $image_path = '';

    // Xử lý upload ảnh
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            // Tạo thư mục nếu chưa tồn tại
            mkdir($target_dir, 0755, true);
        }
        // Tạo tên file độc đáo (thời gian + tên gốc)
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file; // Lưu đường dẫn tương đối vào DB
        }
    }

    // Chèn dữ liệu vào DB (Sử dụng Prepared Statements để an toàn hơn)
    $sql_insert = "INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql_insert);
    // Chuỗi định dạng: s=string, d=double/float, s=string
    mysqli_stmt_bind_param($stmt, "ssds", $name, $description, $price, $image_path);
    mysqli_stmt_execute($stmt);

    // Sau khi thêm thành công, chuyển hướng về trang chủ
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Add New Whey Product</h3>
            </div>
            <div class="card-body">
                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" name="price" step="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" class="form-control-file">
                    </div>
                    <button type="submit" name="insert" class="btn btn-primary">Save Product</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>