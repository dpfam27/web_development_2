<?php
include 'connect.php';
include 'auth.php'; // Sử dụng auth.php để đồng bộ logic kiểm tra đăng nhập

// 1. Kiểm tra đăng nhập bằng hàm chuẩn của bạn
// Hàm này sẽ tự động chuyển hướng về login.php nếu chưa đăng nhập
require_login(); 

// 2. Lấy user_id chuẩn từ Session (SỬA LỖI TẠI ĐÂY: dùng 'user_id' thay vì 'id')
$user_id = $_SESSION['user_id'];

// 3. Lấy danh sách đơn hàng của user này
// Sắp xếp đơn mới nhất lên đầu (ORDER BY created_at DESC)
$sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .status-pending { color: orange; font-weight: bold; }
        .status-paid { color: blue; font-weight: bold; }
        .status-processing { color: #17a2b8; font-weight: bold; }
        .status-shipped { color: purple; font-weight: bold; }
        .status-completed { color: green; font-weight: bold; }
        .status-cancelled { color: red; font-weight: bold; }
    </style>
</head>
<body class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Đơn hàng của tôi</h2>
        <a href="index.php" class="btn btn-outline-secondary">← Quay lại trang chủ</a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Mã đơn (#ID)</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                            
                            <td>
                                <span class="status-<?php echo $row['status']; ?>">
                                    <?php 
                                        // Map trạng thái sang tiếng Việt
                                        $status_map = [
                                            'pending' => 'Chờ xử lý',
                                            'paid' => 'Đã thanh toán',
                                            'processing' => 'Đang xử lý',
                                            'shipped' => 'Đang giao',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        echo $status_map[$row['status']] ?? ucfirst($row['status']);
                                    ?>
                                </span>
                            </td>
                            
                            <td>
                                <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-info btn-sm">Xem chi tiết</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            <h4>Bạn chưa có đơn hàng nào.</h4>
            <p class="mt-3"><a href="index.php" class="btn btn-primary">Mua sắm ngay</a></p>
        </div>
    <?php endif; ?>
    
</body>
</html>