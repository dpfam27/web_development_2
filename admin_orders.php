<?php
include 'connect.php';
include 'auth.php';
require_admin();

if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $st = $_POST['status'];
    mysqli_query($link, "UPDATE orders SET status='$st' WHERE order_id=$oid");
}

$sql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY created_at DESC";
$res = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Admin Orders</title><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head>
<body class="container mt-5">
    <h2>Quản lý Đơn Hàng</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ</a>
    <table class="table table-bordered">
        <thead><tr><th>Mã</th><th>User</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th><th>Hành động</th></tr></thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td>#<?php echo $row['order_id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <select name="status" class="form-control form-control-sm mr-2">
                            <option value="pending" <?php if($row['status']=='pending') echo 'selected'; ?>>Pending</option>
                            <option value="processing" <?php if($row['status']=='processing') echo 'selected'; ?>>Processing</option>
                            <option value="completed" <?php if($row['status']=='completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if($row['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Lưu</button>
                    </form>
                </td>
                <td><?php echo $row['created_at']; ?></td>
                <td><a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-info btn-sm">Chi tiết</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>