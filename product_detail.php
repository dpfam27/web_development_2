<<<<<<< Updated upstream
<?php
include 'connect.php';
include 'auth.php';

$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query_prod = "SELECT * FROM products WHERE product_id = $pid";
$res_prod = mysqli_query($link, $query_prod);
$product = mysqli_fetch_assoc($res_prod);

if (!$product) { die("Sản phẩm không tồn tại"); }

// Lấy danh sách biến thể (Variant)
$query_vars = "SELECT * FROM product_variants WHERE product_id = $pid";
$res_vars = mysqli_query($link, $query_vars);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <div class="row">
        <div class="col-md-5">
            <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/400'; ?>" class="img-fluid border">
        </div>
        <div class="col-md-7">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="text-muted"><?php echo $product['category']; ?></p>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <hr>
            
            <form action="cart.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label><strong>Chọn phân loại (Variant):</strong></label>
                    <select name="variant_id" class="form-control" required>
                        <option value="">-- Chọn Vị / Size --</option>
                        <?php while($v = mysqli_fetch_assoc($res_vars)): ?>
                            <option value="<?php echo $v['variant_id']; ?>" <?php echo ($v['stock'] <= 0 ? 'disabled' : ''); ?>>
                                <?php echo $v['variant_name']; ?> - $<?php echo number_format($v['price'], 2); ?> 
                                (Kho: <?php echo $v['stock']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Số lượng:</label>
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="width: 100px;">
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Thêm vào Giỏ Hàng</button>
                <a href="index.php" class="btn btn-secondary btn-lg ml-2">Quay về</a>
            </form>
        </div>
    </div>
</body>
=======
<?php
include 'connect.php';
include 'auth.php';

$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query_prod = "SELECT * FROM products WHERE product_id = $pid";
$res_prod = mysqli_query($link, $query_prod);
$product = mysqli_fetch_assoc($res_prod);

if (!$product) { die("Sản phẩm không tồn tại"); }

// Lấy danh sách biến thể (Variant)
$query_vars = "SELECT * FROM product_variants WHERE product_id = $pid";
$res_vars = mysqli_query($link, $query_vars);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <div class="row">
        <div class="col-md-5">
            <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/400'; ?>" class="img-fluid border">
        </div>
        <div class="col-md-7">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="text-muted"><?php echo $product['category']; ?></p>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <hr>
            
            <form action="cart.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label><strong>Chọn phân loại (Variant):</strong></label>
                    <select name="variant_id" class="form-control" required>
                        <option value="">-- Chọn Vị / Size --</option>
                        <?php while($v = mysqli_fetch_assoc($res_vars)): ?>
                            <option value="<?php echo $v['variant_id']; ?>" <?php echo ($v['stock'] <= 0 ? 'disabled' : ''); ?>>
                                <?php echo $v['variant_name']; ?> - $<?php echo number_format($v['price'], 2); ?> 
                                (Kho: <?php echo $v['stock']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Số lượng:</label>
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="width: 100px;">
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Thêm vào Giỏ Hàng</button>
                <a href="index.php" class="btn btn-secondary btn-lg ml-2">Quay về</a>
            </form>
        </div>
    </div>
</body>
>>>>>>> Stashed changes
</html>