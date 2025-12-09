<?php
include 'connect.php';
include 'auth.php';

require_login();
$current_user_id = $_SESSION['user_id'];

// Kiểm tra role từ session (giả sử bạn lưu role lúc login, nếu chưa thì mặc định là user)
// Nếu session chưa lưu role, ta có thể truy vấn lại DB để chắc chắn
$user_query = mysqli_query($link, "SELECT role FROM users WHERE user_id = $current_user_id");
$user_data = mysqli_fetch_assoc($user_query);
$is_admin = ($user_data['role'] == 'admin');

if (!isset($_GET['order_id'])) {
    // Nếu là admin thì về trang quản lý, user thì về đơn hàng của tôi
    if ($is_admin) header("Location: admin_orders.php");
    else header("Location: my_orders.php");
    exit;
}

$order_id = (int)$_GET['order_id'];

// 1. Xây dựng câu truy vấn dựa trên quyền hạn
if ($is_admin) {
    // ADMIN: Xem được mọi đơn hàng
    $sql_order = "SELECT o.*, u.username, u.email, u.role 
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.user_id
                  WHERE o.order_id = $order_id";
} else {
    // USER: Chỉ xem được đơn của chính mình
    $sql_order = "SELECT o.*, u.username, u.email, u.role 
                  FROM orders o
                  JOIN users u ON o.user_id = u.user_id
                  WHERE o.order_id = $order_id AND o.user_id = $current_user_id";
}

$res_order = mysqli_query($link, $sql_order);

if (mysqli_num_rows($res_order) == 0) {
    echo "<script>alert('Không tìm thấy đơn hàng hoặc bạn không có quyền xem!'); window.history.back();</script>";
    exit;
}

$order = mysqli_fetch_assoc($res_order);

// 2. Lấy chi tiết sản phẩm
$sql_items = "SELECT oi.*, v.variant_name, p.name as prod_name, p.image_url 
              FROM order_items oi
              LEFT JOIN product_variants v ON oi.variant_id = v.variant_id
              LEFT JOIN products p ON v.product_id = p.product_id
              WHERE oi.order_id = $order_id";
$res_items = mysqli_query($link, $sql_items);

// 3. Coupon info
$coupon_code = '';
if (!empty($order['coupon_id'])) {
    $cid = $order['coupon_id'];
    $res_cp = mysqli_query($link, "SELECT * FROM coupons WHERE coupon_id = $cid");
    if ($cp = mysqli_fetch_assoc($res_cp)) {
        $coupon_code = $cp['code'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .order-header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .status-badge { font-size: 0.9em; padding: 5px 10px; border-radius: 4px; color: white; }
        .status-pending { background-color: orange; }
        .status-paid { background-color: blue; }
        .status-processing { background-color: #17a2b8; }
        .status-shipped { background-color: purple; }
        .status-completed { background-color: green; }
        .status-cancelled { background-color: red; }
    </style>
</head>
<body class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>
            <?php echo $is_admin ? "[Admin] " : ""; ?>
            Chi tiết đơn hàng #<?php echo $order_id; ?>
        </h3>
        <a href="<?php echo $is_admin ? 'admin_orders.php' : 'my_orders.php'; ?>" class="btn btn-secondary">
            ← Quay lại danh sách
        </a>
    </div>

    <div class="order-header border">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Ngày đặt hàng:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Trạng thái:</strong> 
                    <?php 
                        $status_map = [
                            'pending' => 'Chờ xử lý', 'paid' => 'Đã thanh toán',
                            'processing' => 'Đang xử lý', 'shipped' => 'Đang giao',
                            'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'
                        ];
                        $st = $order['status'];
                        echo "<span class='status-badge status-$st'>" . ($status_map[$st] ?? $st) . "</span>";
                    ?>
                </p>
                <p><strong>Phương thức TT:</strong> <?php echo $order['payment_method']; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['username']); ?> (<?php echo $order['email']; ?>)</p>
                <p><strong>Địa chỉ giao hàng:</strong><br> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead class="thead-light">
            <tr>
                <th>Sản phẩm</th>
                <th>Phân loại</th>
                <th class="text-center">Đơn giá</th>
                <th class="text-center">SL</th>
                <th class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            while ($item = mysqli_fetch_assoc($res_items)): 
                $line_total = $item['unit_price'] * $item['quantity'];
                $subtotal += $line_total;
            ?>
            <tr>
                <td><?php echo $item['prod_name'] ?? '<em>Sản phẩm đã xóa</em>'; ?></td>
                <td><?php echo $item['variant_name']; ?></td>
                <td class="text-center">$<?php echo number_format($item['unit_price'], 2); ?></td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-right">$<?php echo number_format($line_total, 2); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <tr>
                <td colspan="4" class="text-right"><strong>Tạm tính:</strong></td>
                <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php if ($subtotal > $order['total_amount']): ?>
            <tr>
                <td colspan="4" class="text-right text-success">
                    <strong>Giảm giá <?php echo $coupon_code ? "($coupon_code)" : ""; ?>:</strong>
                </td>
                <td class="text-right text-success">-$<?php echo number_format($subtotal - $order['total_amount'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="4" class="text-right text-danger"><h4>Tổng cộng:</h4></td>
                <td class="text-right text-danger"><h4>$<?php echo number_format($order['total_amount'], 2); ?></h4></td>
            </tr>
        </tbody>
    </table>

    <div class="text-right mt-3">
        <?php if ($is_admin): ?>
            <button class="btn btn-info" onclick="window.print()">In hóa đơn</button>
        <?php else: ?>
            <?php if ($order['status'] == 'pending'): ?>
                <button class="btn btn-danger" onclick="alert('Vui lòng liên hệ hotline để hủy đơn!')">Yêu cầu hủy đơn</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</body>
</html>