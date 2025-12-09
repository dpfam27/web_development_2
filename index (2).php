<?php
include 'connect.php';
include 'auth.php'; // Chứa session_start() và các hàm check login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Whey Shop - Trang chủ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .product-img { 
            height: 200px; 
            object-fit: contain; 
            padding: 10px; 
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h3 class="text-primary"><a href="index.php" style="text-decoration:none;">WheyStore</a></h3>
            <div>
                <?php if(is_logged_in()): ?>
                    <span class="mr-2">Chào, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
                    
                    <?php if(is_admin()): ?>
                        <a href="admin_orders.php" class="btn btn-info btn-sm">Quản lý Đơn</a>
                        <a href="add_product.php" class="btn btn-success btn-sm">Thêm Sản Phẩm</a>
                    <?php else: ?>
                        <a href="my_orders.php" class="btn btn-info btn-sm">Đơn hàng của tôi</a>
                        <a href="cart.php" class="btn btn-primary btn-sm">
                            Giỏ hàng 
                            </a>
                    <?php endif; ?>

                    <a href="logout.php" class="btn btn-danger btn-sm ml-2">Đăng xuất</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm">Đăng nhập</a>
                    <a href="register.php" class="btn btn-outline-primary btn-sm">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <?php
            // Lấy danh sách sản phẩm mới nhất lên đầu
            $sql = "SELECT * FROM products ORDER BY created_at DESC";
            $result = mysqli_query($link, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/300?text=No+Image'; ?>" class="card-img-top product-img" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title" style="font-size: 1.1rem;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </h5>
                            
                            <p class="text-muted small mb-2">
                                <?php echo htmlspecialchars($row['category'] ?? 'General'); ?>
                            </p>
                            
                            <p class="text-danger font-weight-bold">
                                Giá tham khảo: $<?php echo number_format($row['base_price'], 2); ?>
                            </p>
                            
                            <div class="mt-auto">
                                <?php if(is_admin()): ?>
                                    <div class="d-flex">
                                        <a href="edit.php?id=<?php echo $row['product_id']; ?>" class="btn btn-warning btn-sm flex-fill mr-1">Sửa</a>
                                        
                                        <a href="delete.php?id=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm flex-fill" onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này? (Các biến thể cũng sẽ bị xóa)');">Xóa</a>
                                    </div>
                                <?php else: ?>
                                    <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-outline-primary btn-sm btn-block">
                                        Xem chi tiết & Mua
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center p-5'><h4 class='text-muted'>Chưa có sản phẩm nào. Admin hãy thêm sản phẩm nhé!</h4></div>";
            }
            ?>
        </div>
    </div>
</body>
</html>