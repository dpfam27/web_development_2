<?php
include 'connect.php';
include 'auth.php';
require_admin();

$msg = "";
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $cat = mysqli_real_escape_string($link, $_POST['category']);
    $desc = mysqli_real_escape_string($link, $_POST['description']);
    $base_price = (float)$_POST['base_price'];
    
    // --- XỬ LÝ UPLOAD ẢNH ---
    $img_url = "";
    // Kiểm tra có file upload không và không có lỗi
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        // Tạo thư mục uploads nếu chưa có
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Tạo tên file ngẫu nhiên theo thời gian để tránh trùng
        $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        
        // Di chuyển file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $img_url = $target_file;
        } else {
            $msg = "<div class='alert alert-warning'>Không thể upload ảnh, vui lòng kiểm tra quyền thư mục.</div>";
        }
    }
    // ------------------------

    // Insert Product
    $sql1 = "INSERT INTO products (name, description, category, base_price, image_url) VALUES ('$name', '$desc', '$cat', $base_price, '$img_url')";
    
    if (mysqli_query($link, $sql1)) {
        $pid = mysqli_insert_id($link);
        
        // Insert Variant Mặc Định
        $var_name = mysqli_real_escape_string($link, $_POST['variant_name']);
        $var_price = (float)$_POST['variant_price'];
        $stock = (int)$_POST['stock'];
        
        $sql2 = "INSERT INTO product_variants (product_id, variant_name, price, stock) VALUES ($pid, '$var_name', $var_price, $stock)";
        if (mysqli_query($link, $sql2)) {
            $msg = "<div class='alert alert-success'>Thêm sản phẩm thành công!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Lỗi thêm biến thể: " . mysqli_error($link) . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Lỗi thêm sản phẩm: " . mysqli_error($link) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Add Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5 mb-5">
    <h2>Thêm Sản Phẩm Mới</h2>
    <?php echo $msg; ?>
    <form method="post" enctype="multipart/form-data" class="card p-4">
        <div class="row">
            <div class="col-md-6">
                <h4 class="text-primary">1. Thông tin chung</h4>
                <div class="form-group">
                    <label>Tên SP</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <input type="text" name="category" class="form-control">
                </div>
                <div class="form-group">
                    <label>Giá hiển thị (Base)</label>
                    <input type="number" step="0.01" name="base_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Ảnh sản phẩm (Upload từ máy)</label>
                    <input type="file" name="image" class="form-control-file">
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="text-success">2. Biến thể đầu tiên</h4>
                <div class="form-group">
                    <label>Tên biến thể (VD: 5Lbs Vani)</label>
                    <input type="text" name="variant_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Giá bán thật</label>
                    <input type="number" step="0.01" name="variant_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tồn kho</label>
                    <input type="number" name="stock" class="form-control" value="100">
                </div>
            </div>
        </div>
        <button type="submit" name="submit" class="btn btn-primary mt-3 btn-lg">Lưu Sản Phẩm</button>
        <a href="index.php" class="btn btn-secondary mt-3">Hủy / Quay về</a>
    </form>
</body>
</html>