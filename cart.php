
<?php
include 'connect.php';
include 'auth.php';

require_login(); 
$user_id = $_SESSION['user_id'];

// 1. Lấy hoặc tạo Cart ID cho user
$check_cart = mysqli_query($link, "SELECT cart_id FROM carts WHERE user_id = $user_id");
if (mysqli_num_rows($check_cart) > 0) {
    $c = mysqli_fetch_assoc($check_cart);
    $cart_id = $c['cart_id'];
} else {
    mysqli_query($link, "INSERT INTO carts (user_id) VALUES ($user_id)");
    $cart_id = mysqli_insert_id($link);
}

// 2. Xử lý POST (Thêm/Xóa)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $vid = (int)$_POST['variant_id'];
        $qty = (int)$_POST['quantity'];
        // Upsert: Nếu có rồi thì tăng số lượng
        $sql = "INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES ($cart_id, $vid, $qty)
                ON DUPLICATE KEY UPDATE quantity = quantity + $qty";
        mysqli_query($link, $sql);
    }
    if ($action == 'remove') {
        $item_id = (int)$_POST['cart_item_id'];
        mysqli_query($link, "DELETE FROM cart_items WHERE cart_item_id = $item_id AND cart_id = $cart_id");
    }
    header("Location: cart.php"); exit;
}

// 3. Hiển thị
$sql_show = "SELECT ci.*, v.variant_name, v.price, p.name as prod_name, p.image_url 
             FROM cart_items ci
             JOIN product_variants v ON ci.variant_id = v.variant_id
             JOIN products p ON v.product_id = p.product_id
             WHERE ci.cart_id = $cart_id";
$res = mysqli_query($link, $sql_show);
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Giỏ hàng của bạn</h2>
    <?php if(mysqli_num_rows($res) == 0): ?>
        <div class="alert alert-warning text-center">
            <h4>Giỏ hàng trống.</h4>
            <br>
            <a href="index.php" class="btn btn-primary">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <table class="table table-bordered bg-white">
            <thead><tr><th>Sản phẩm</th><th>Loại</th><th>Giá</th><th>SL</th><th>Thành tiền</th><th>Xóa</th></tr></thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res)): 
                    $line = $row['price'] * $row['quantity'];
                    $total += $line;
                ?>
                <tr>
                    <td><?php echo $row['prod_name']; ?></td>
                    <td><?php echo $row['variant_name']; ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>$<?php echo number_format($line, 2); ?></td>
                    <td>
                        <form method="post"><input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                        <button class="btn btn-danger btn-sm">X</button></form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" class="text-right font-weight-bold">Tổng cộng:</td>
                    <td colspan="2" class="font-weight-bold text-danger">$<?php echo number_format($total, 2); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="d-flex justify-content-between mb-5">
            <a href="index.php" class="btn btn-secondary btn-lg">← Tiếp tục mua sắm</a>
            <a href="checkout.php" class="btn btn-success btn-lg">Tiến hành Thanh Toán →</a>
        </div>

    <?php endif; ?>
</body>
</html>

<?php
include 'connect.php';
include 'auth.php';

require_login(); 
$user_id = $_SESSION['user_id'];

// 1. Lấy hoặc tạo Cart ID cho user
$check_cart = mysqli_query($link, "SELECT cart_id FROM carts WHERE user_id = $user_id");
if (mysqli_num_rows($check_cart) > 0) {
    $c = mysqli_fetch_assoc($check_cart);
    $cart_id = $c['cart_id'];
} else {
    mysqli_query($link, "INSERT INTO carts (user_id) VALUES ($user_id)");
    $cart_id = mysqli_insert_id($link);
}

// 2. Xử lý POST (Thêm/Xóa)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $vid = (int)$_POST['variant_id'];
        $qty = (int)$_POST['quantity'];
        // Upsert: Nếu có rồi thì tăng số lượng
        $sql = "INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES ($cart_id, $vid, $qty)
                ON DUPLICATE KEY UPDATE quantity = quantity + $qty";
        mysqli_query($link, $sql);
    }
    if ($action == 'remove') {
        $item_id = (int)$_POST['cart_item_id'];
        mysqli_query($link, "DELETE FROM cart_items WHERE cart_item_id = $item_id AND cart_id = $cart_id");
    }
    header("Location: cart.php"); exit;
}

// 3. Hiển thị
$sql_show = "SELECT ci.*, v.variant_name, v.price, p.name as prod_name, p.image_url 
             FROM cart_items ci
             JOIN product_variants v ON ci.variant_id = v.variant_id
             JOIN products p ON v.product_id = p.product_id
             WHERE ci.cart_id = $cart_id";
$res = mysqli_query($link, $sql_show);
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Giỏ hàng của bạn</h2>
    <?php if(mysqli_num_rows($res) == 0): ?>
        <div class="alert alert-warning text-center">
            <h4>Giỏ hàng trống.</h4>
            <br>
            <a href="index.php" class="btn btn-primary">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <table class="table table-bordered bg-white">
            <thead><tr><th>Sản phẩm</th><th>Loại</th><th>Giá</th><th>SL</th><th>Thành tiền</th><th>Xóa</th></tr></thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res)): 
                    $line = $row['price'] * $row['quantity'];
                    $total += $line;
                ?>
                <tr>
                    <td><?php echo $row['prod_name']; ?></td>
                    <td><?php echo $row['variant_name']; ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>$<?php echo number_format($line, 2); ?></td>
                    <td>
                        <form method="post"><input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                        <button class="btn btn-danger btn-sm">X</button></form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" class="text-right font-weight-bold">Tổng cộng:</td>
                    <td colspan="2" class="font-weight-bold text-danger">$<?php echo number_format($total, 2); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="d-flex justify-content-between mb-5">
            <a href="index.php" class="btn btn-secondary btn-lg">← Tiếp tục mua sắm</a>
            <a href="checkout.php" class="btn btn-success btn-lg">Tiến hành Thanh Toán →</a>
        </div>

    <?php endif; ?>
</body>
</html>