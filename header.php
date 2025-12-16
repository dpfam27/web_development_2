<?php
if (!isset($page_title)) {
    $page_title = "WheyStore - Authentic Supplements";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS (Auto Reload) -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="custom-navbar">
        <!-- 1. Logo -->
        <a class="navbar-brand" href="index.php">Whey<span>Store</span></a>

        <!-- 2. Search -->
        <form action="products.php" method="GET" class="search-container">
            <div class="input-group">
                <input class="form-control search-input" type="search" name="search" id="search_input" placeholder="SEARCH PRODUCTS..." autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div id="search_results" class="search-results"></div>
        </form>

        <!-- 3. Menu -->
        <ul class="custom-menu">
            <li class="nav-item"><a class="nav-link" href="index.php">HOME</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="products.php" data-toggle="dropdown">SHOP</a>
                <div class="dropdown-menu shadow border-0">
                    <a class="dropdown-item" href="products.php">All Products</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="products.php?category=Whey+Protein">Whey Protein</a>
                    <a class="dropdown-item" href="products.php?category=Pre-workout">Pre-workout</a>
                    <a class="dropdown-item" href="products.php?category=Mass+Gainer">Mass Gainer</a>
                    <a class="dropdown-item" href="products.php?category=Accessories">Accessories</a>
                </div>
            </li>
            <li class="nav-item"><a class="nav-link" href="contact.php">CONTACT</a></li>
        </ul>

        <!-- 4. User/Admin Icons (Luôn nằm ngang) -->
        <div class="user-action-area">
            <?php if(is_logged_in()): ?>
                
                <!-- ADMIN ICONS (Nội dung cũ, chỉ thêm class để style) -->
                <?php if(is_admin()): ?>
                    <!-- Add Product -->
                    <a href="add_product.php" class="icon-btn" title="Add Product">
                        <i class="fas fa-plus-square"></i> <!-- Icon cũ -->
                    </a>
                    
                    <!-- Manage Orders -->
                    <a href="admin_orders.php" class="icon-btn" title="Manage Orders">
                        <i class="fas fa-clipboard-list"></i> <!-- Icon cũ -->
                    </a>

                    <!-- Admin Account (Dropdown) -->
                    <div class="dropdown">
                        <div class="icon-btn text-danger" data-toggle="dropdown"> <!-- Icon cũ -->
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="dropdown-menu dropdown-menu-right shadow border-0 mt-2">
                            <div class="dropdown-item-text font-weight-bold text-danger text-uppercase">ADMIN</div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="admin_dashboard.php">Dashboard</a>
                            <a class="dropdown-item text-danger" href="logout.php">Logout</a>
                        </div>
                    </div>

                <!-- USER ICONS -->
                <?php else: ?>
                    <a href="cart.php" class="icon-btn" title="Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <?php 
                             $uid = $_SESSION['user_id'];
                             if(isset($link)) {
                                 $c_res = @mysqli_fetch_assoc(@mysqli_query($link, "SELECT SUM(quantity) as q FROM cart_items ci JOIN carts c ON ci.cart_id=c.cart_id WHERE c.user_id=$uid"));
                                 if($c_res && $c_res['q'] > 0) echo '<span class="cart-badge">'.$c_res['q'].'</span>';
                             }
                        ?>
                    </a>
                    <div class="dropdown">
                        <div class="icon-btn" data-toggle="dropdown"><i class="fas fa-user-circle"></i></div>
                        <div class="dropdown-menu dropdown-menu-right shadow border-0 mt-2">
                            <div class="dropdown-item-text font-weight-bold text-primary">Hi, <?php echo $_SESSION['username']; ?></div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="my_orders.php">My Orders</a>
                            <a class="dropdown-item text-danger" href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
            
            <!-- GUEST -->
            <?php else: ?>
                <div class="auth-buttons ml-3">
                    <a href="login.php" class="btn btn-login auth-btn mb-1">Login</a>
                    <a href="register.php" class="btn btn-register auth-btn">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="main-content">