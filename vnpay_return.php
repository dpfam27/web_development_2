<?php
include_once 'connect.php';
include_once 'vnpay_config.php';

if (isset($_GET['vnp_ResponseCode'])) {
    $inputData = array();
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    $vnp_SecureHash = $_GET['vnp_SecureHash'];
    unset($inputData['vnp_SecureHash']);

    ksort($inputData);
    $i = 0;
    $hashData = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);
    $order_id = $_GET['vnp_TxnRef'];
    $responseCode = $_GET['vnp_ResponseCode'];

    if ($secureHash == $vnp_SecureHash) {
        if ($responseCode == '00') {
            // Thanh toán thành công
            // Cập nhật order status
            mysqli_query($link, "UPDATE orders SET status = 'paid' WHERE order_id = $order_id");
            
            // Cập nhật payment status
            mysqli_query($link, "UPDATE payments SET status = 'paid' WHERE order_id = $order_id");
            
            // Cập nhật stock và xóa cart
            $sql_items = "SELECT oi.*, c.cart_id FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.order_id
                         JOIN carts c ON o.user_id = c.user_id
                         WHERE oi.order_id = $order_id";
            $res_items = mysqli_query($link, $sql_items);
            
            while($item = mysqli_fetch_assoc($res_items)) {
                $vid = $item['variant_id'];
                $qty = $item['quantity'];
                mysqli_query($link, "UPDATE product_variants SET stock = stock - $qty WHERE variant_id = $vid");
            }
            
            // Xóa cart items
            $cart_result = mysqli_query($link, "SELECT cart_id FROM orders o JOIN carts c ON o.user_id = c.user_id WHERE order_id = $order_id");
            if ($cart_row = mysqli_fetch_assoc($cart_result)) {
                mysqli_query($link, "DELETE FROM cart_items WHERE cart_id = " . $cart_row['cart_id']);
            }
            
            // Xóa coupon session
            unset($_SESSION['coupon']);
            
            echo "<script>alert('Payment successful! Order ID: #$order_id'); window.location='index.php';</script>";
        } else {
            // Thanh toán thất bại
            mysqli_query($link, "UPDATE orders SET status = 'cancelled' WHERE order_id = $order_id");
            mysqli_query($link, "UPDATE payments SET status = 'failed' WHERE order_id = $order_id");
            echo "<script>alert('Payment failed! Please try again.'); window.location='checkout.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid signature!'); window.location='checkout.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request!'); window.location='checkout.php';</script>";
}
?>
