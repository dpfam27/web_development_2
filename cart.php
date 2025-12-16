<?php
include_once 'connect.php';
include_once 'auth.php';

require_login(); 
$user_id = $_SESSION['user_id'];

// Get or Create Cart ID
$check_cart = mysqli_query($link, "SELECT cart_id FROM carts WHERE user_id = $user_id");
if (mysqli_num_rows($check_cart) > 0) {
    $c = mysqli_fetch_assoc($check_cart);
    $cart_id = $c['cart_id'];
} else {
    mysqli_query($link, "INSERT INTO carts (user_id) VALUES ($user_id)");
    $cart_id = mysqli_insert_id($link);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $vid = (int)$_POST['variant_id'];
        $qty = (int)$_POST['quantity'];
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

// Display
$sql_show = "SELECT ci.*, v.variant_name, v.price, p.name as prod_name, p.image_url 
             FROM cart_items ci
             JOIN product_variants v ON ci.variant_id = v.variant_id
             JOIN products p ON v.product_id = p.product_id
             WHERE ci.cart_id = $cart_id";
$res = mysqli_query($link, $sql_show);
$total = 0;

$page_title = "Your Cart - WheyStore";
include 'header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Your Shopping Cart</h2>
    <?php if(mysqli_num_rows($res) == 0): ?>
        <div class="alert alert-warning text-center p-5">
            <h4><i class="fas fa-shopping-basket mb-3" style="font-size: 3rem;"></i><br>Your cart is empty.</h4>
            <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light"><tr><th>Product</th><th>Variant</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($res)): 
                                    $line = $row['price'] * $row['quantity'];
                                    $total += $line;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/50'; ?>" width="50" class="mr-3 rounded">
                                            <span><?php echo $row['prod_name']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $row['variant_name']; ?></td>
                                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td class="font-weight-bold">$<?php echo number_format($line, 2); ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                                            <button class="btn btn-outline-danger btn-sm rounded-circle"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Cart Summary</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <strong>$<?php echo number_format($total, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="h4">Total:</span>
                            <span class="h4 text-danger">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-success btn-block btn-lg">CHECKOUT</a>
                        <a href="products.php" class="btn btn-outline-secondary btn-block mt-2">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
