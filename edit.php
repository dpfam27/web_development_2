<?php
include 'connect.php';
include 'auth.php';
require_admin();

// Lấy ID sản phẩm
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($pid == 0) { header("Location: index.php"); exit; }

$msg = "";

// 1. CẬP NHẬT THÔNG TIN CHUNG
if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $desc = mysqli_real_escape_string($link, $_POST['description']);
    $base_price = (float)$_POST['base_price'];
    $img_sql = "";

    // Upload ảnh mới nếu có
    if (!empty($_FILES['image']['name'])) {
        $target = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $img_sql = ", image_url='$target'";
    }

    $sql = "UPDATE products SET name='$name', description='$desc', base_price=$base_price $img_sql WHERE product_id=$pid";
    if(mysqli_query($link, $sql)) $msg = "<div class='alert alert-success'>Cập nhật thành công!</div>";
    else $msg = "<div class='alert alert-danger'>Lỗi: ".mysqli_error($link)."</div>";
}

// 2. THÊM BIẾN THỂ MỚI
if (isset($_POST['add_variant'])) {
    $vname = $_POST['new_vname'];
    $vprice = $_POST['new_vprice'];
    $vstock = $_POST['new_vstock'];
    mysqli_query($link, "INSERT INTO product_variants (product_id, variant_name, price, stock) VALUES ($pid, '$vname', $vprice, $vstock)");
}

// 3. CẬP NHẬT BIẾN THỂ CŨ
if (isset($_POST['save_variant'])) {
    $vid = $_POST['vid'];
    $vname = $_POST['vname'];
    $vprice = $_POST['vprice'];
    $vstock = $_POST['vstock'];
    mysqli_query($link, "UPDATE product_variants SET variant_name='$vname', price=$vprice, stock=$vstock WHERE variant_id=$vid");
}

// 4. LẤY DỮ LIỆU HIỂN THỊ
$prod = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM products WHERE product_id=$pid"));
$vars = mysqli_query($link, "SELECT * FROM product_variants WHERE product_id=$pid");
?>

<!DOCTYPE html>
<html lang="en">
<head><title>Sửa sản phẩm</title><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head>
<body class="container mt-5 mb-5">
    <div class="d-flex justify-content-between">
        <h2>Sửa: <?php echo htmlspecialchars($prod['name']); ?></h2>
        <a href="index.php" class="btn btn-secondary">Về trang chủ</a>
    </div>
    <?php echo $msg; ?>

    <div class="card p-3 mb-4 bg-light">
        <h4>1. Thông tin chung</h4>
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <label>Tên SP</label><input type="text" name="name" class="form-control" value="<?php echo $prod['name']; ?>" required>
                    <label>Giá gốc</label><input type="text" name="base_price" class="form-control" value="<?php echo $prod['base_price']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Mô tả</label><textarea name="description" class="form-control"><?php echo $prod['description']; ?></textarea>
                    <label>Ảnh mới</label><input type="file" name="image" class="form-control-file">
                </div>
            </div>
            <button type="submit" name="update_product" class="btn btn-primary mt-3">Lưu Thay Đổi</button>
        </form>
    </div>

    <div class="card p-3">
        <h4>2. Quản lý Biến thể (Vị/Size)</h4>
        <table class="table table-bordered">
            <thead><tr><th>Tên biến thể</th><th>Giá bán</th><th>Kho</th><th>Hành động</th></tr></thead>
            <tbody>
                <tr class="table-warning">
                    <form method="post">
                        <td><input type="text" name="new_vname" placeholder="Thêm mới (VD: 10Lbs)" class="form-control form-control-sm" required></td>
                        <td><input type="number" name="new_vprice" placeholder="Giá" class="form-control form-control-sm" required></td>
                        <td><input type="number" name="new_vstock" placeholder="Kho" class="form-control form-control-sm" value="100"></td>
                        <td><button type="submit" name="add_variant" class="btn btn-sm btn-success">Thêm</button></td>
                    </form>
                </tr>
                
                <?php while($v = mysqli_fetch_assoc($vars)): ?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="vid" value="<?php echo $v['variant_id']; ?>">
                        <td><input type="text" name="vname" value="<?php echo $v['variant_name']; ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="vprice" value="<?php echo $v['price']; ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="vstock" value="<?php echo $v['stock']; ?>" class="form-control form-control-sm"></td>
                        <td>
                            <button type="submit" name="save_variant" class="btn btn-primary btn-sm">Lưu</button>
                            <a href="delete.php?type=variant&id=<?php echo $v['variant_id']; ?>&pid=<?php echo $pid; ?>" 
                               class="btn btn-danger btn-sm" onclick="return confirm('Xóa biến thể này?')">Xóa</a>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>