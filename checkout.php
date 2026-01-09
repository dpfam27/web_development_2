<?php
include_once 'connect.php';
include_once 'auth.php';
include_once 'vnpay_config.php';
require_login();
$user_id = $_SESSION['user_id'];

// Check Cart
$check_cart = mysqli_query($link, "SELECT cart_id FROM carts WHERE user_id = $user_id");
if (mysqli_num_rows($check_cart) == 0) { header("Location: index.php"); exit; }
$c = mysqli_fetch_assoc($check_cart);
$cart_id = $c['cart_id'];

$sql_items = "SELECT ci.*, v.variant_name, v.price, p.name as prod_name 
              FROM cart_items ci
              JOIN product_variants v ON ci.variant_id = v.variant_id
              JOIN products p ON v.product_id = p.product_id
              WHERE ci.cart_id = $cart_id";
$res_items = mysqli_query($link, $sql_items);
if(mysqli_num_rows($res_items) == 0) { header("Location: cart.php"); exit; }

$items = [];
$subtotal = 0;
while($row = mysqli_fetch_assoc($res_items)) {
    $items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

// Handle Coupon
$discount = 0;
$coupon_id = "NULL";
$msg_coupon = "";

if (isset($_POST['apply_coupon'])) {
    $code = mysqli_real_escape_string($link, $_POST['code']);
    $today = date('Y-m-d');
    $sql_cp = "SELECT * FROM coupons WHERE code='$code' AND is_active=1 AND (start_date <= '$today') AND (end_date >= '$today')";
    $res_cp = mysqli_query($link, $sql_cp);
    
    if (mysqli_num_rows($res_cp) > 0) {
        $cp = mysqli_fetch_assoc($res_cp);
        if ($subtotal >= $cp['minimum_order_amount']) {
            $_SESSION['coupon'] = $cp; 
            $msg_coupon = "<div class='alert alert-success mt-2'>Coupon Applied!</div>";
        } else {
            $msg_coupon = "<div class='alert alert-warning mt-2'>Minimum order amount is $".$cp['minimum_order_amount']."</div>";
        }
    } else {
        $msg_coupon = "<div class='alert alert-danger mt-2'>Invalid or expired coupon!</div>";
    }
}

if (isset($_SESSION['coupon'])) {
    $cp = $_SESSION['coupon'];
    $coupon_id = $cp['coupon_id'];
    if ($cp['discount_type'] == 'fixed') {
        $discount = $cp['value'];
    } else {
        $discount = $subtotal * ($cp['value'] / 100);
    }
}
$final_total = $subtotal - $discount;
if ($final_total < 0) $final_total = 0;

// Handle Checkout
if (isset($_POST['checkout'])) {
    $address = mysqli_real_escape_string($link, $_POST['address']);
    $payment = mysqli_real_escape_string($link, $_POST['payment_method']);

    $sql_ord = "INSERT INTO orders (user_id, coupon_id, total_amount, shipping_address, payment_method, status) 
                VALUES ($user_id, $coupon_id, $final_total, '$address', '$payment', 'pending')";
    
    if (mysqli_query($link, $sql_ord)) {
        $order_id = mysqli_insert_id($link);

        foreach ($items as $item) {
            $vid = $item['variant_id'];
            $qty = $item['quantity'];
            $price = $item['price'];
            
            mysqli_query($link, "INSERT INTO order_items (order_id, variant_id, quantity, unit_price) VALUES ($order_id, $vid, $qty, $price)");
        }

        if ($payment == 'VNPay') {
            // Tạo payment record
            mysqli_query($link, "INSERT INTO payments (order_id, amount, payment_method, status) VALUES ($order_id, $final_total, 'VNPay', 'pending')");
            
            // Tạo URL VNPay và redirect
            $order_info = "Thanh toan don hang #$order_id";
            $vnpay_url = createVNPayUrl($order_id, $final_total, $order_info);
            header("Location: $vnpay_url");
            exit;
        } else {
            // COD - cập nhật stock và xóa cart
            foreach ($items as $item) {
                $vid = $item['variant_id'];
                $qty = $item['quantity'];
                mysqli_query($link, "UPDATE product_variants SET stock = stock - $qty WHERE variant_id = $vid");
            }
            mysqli_query($link, "DELETE FROM cart_items WHERE cart_id = $cart_id");
            unset($_SESSION['coupon']);
            echo "<script>alert('Order placed successfully! Order ID: #$order_id'); window.location='index.php';</script>";
        }
    } else {
        echo "Error: " . mysqli_error($link);
    }
}

$page_title = "Checkout - WheyStore";
include 'header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Checkout</h2>
    <div class="row">
        <div class="col-md-6">
            <h4 class="mb-3">Shipping Information</h4>
            <form method="post" class="card p-4 shadow-sm">
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="address" class="form-control" rows="3" required placeholder="House number, street, ward, district..."></textarea>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="COD">Cash On Delivery (COD)</option>
                        <option value="VNPay">VNPay (Online Payment)</option>
                    </select>
                </div>
                <button type="submit" name="checkout" class="btn btn-success btn-lg btn-block mt-3">PLACE ORDER</button>
            </form>
        </div>
        <div class="col-md-6">
            <div class="card bg-light p-3 shadow-sm">
                <h5 class="card-title">Order Summary</h5>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach($items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <span><?php echo $item['prod_name'] . " (" . $item['variant_name'] . ")"; ?> <small>x<?php echo $item['quantity']; ?></small></span>
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex justify-content-between"><span>Subtotal:</span> <strong>$<?php echo number_format($subtotal, 2); ?></strong></div>
                <?php if($discount > 0): ?>
                    <div class="d-flex justify-content-between text-success"><span>Discount:</span> <strong>-$<?php echo number_format($discount, 2); ?></strong></div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between h4"><span>Total:</span> <strong class="text-danger">$<?php echo number_format($final_total, 2); ?></strong></div>

                <form method="post" class="mt-3 input-group">
                    <input type="text" name="code" class="form-control" placeholder="Enter Coupon Code">
                    <div class="input-group-append">
                        <button type="submit" name="apply_coupon" class="btn btn-secondary">Apply</button>
                    </div>
                </form>
                <?php echo $msg_coupon; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
