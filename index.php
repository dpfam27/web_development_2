<?php

include 'connect.php';
include 'cookie.php';
include 'auth.php';

// Bảo vệ trang
require_login();

set_my_cookie('user_theme', 'light', 86400 * 7);
$theme = get_my_cookie('user_theme');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-page">
    <div class="container mt-4">
        
        <div class="d-flex justify-content-end mb-3 align-items-center">
            <span class="mr-3">Xin chào, <b><?php echo htmlspecialchars($_SESSION["username"] ?? 'Quản trị viên'); ?></b> 
            <?php if(is_admin()): ?>
                <span class="badge badge-danger">Admin</span>
            <?php else: ?>
                <span class="badge badge-info">User</span>
            <?php endif; ?>
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Product List (Whey Products)</h1>
            <?php if(is_admin()): ?>
                <a href="add_product.php" class="btn btn-primary">Add New Product</a>
            <?php endif; ?>
        </div>

        <div class="row">
            <?php
            $sql_select = "SELECT id, name, description, price, image_url FROM products ORDER BY id DESC";
            $result = mysqli_query($link, $sql_select);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card h-100"> 
                            <img src="<?php echo (!empty($row['image_url']) && file_exists($row['image_url'])) ? $row['image_url'] : 'placeholder.png'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body d-flex flex-column"> 
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                
                                <?php if (!empty($row['description'])): ?>
                                    <p class="card-text description-text">
                                        <?php
                                        if (strlen($row['description']) > 60) {
                                            echo htmlspecialchars(substr($row['description'], 0, 60)) . '...';
                                        } else {
                                            echo htmlspecialchars($row['description']);
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>

                                <p class="card-text font-weight-bold mt-auto">$<?php echo number_format($row['price'], 2); ?></p>
                                <div>
                                    <?php if(is_admin()): ?>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<div class='col-12'><p class='text-center'>No products found.</p></div>";
            }
            ?>
        </div>
    </div>

    <?php mysqli_close($link); ?>
</body>
</html>