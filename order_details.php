<?php
include 'connect.php';
include 'auth.php';

require_login();
$current_user_id = $_SESSION['user_id'];

// Check Role
$user_query = mysqli_query($link, "SELECT role FROM users WHERE user_id = $current_user_id");
$user_data = mysqli_fetch_assoc($user_query);
$is_admin = ($user_data['role'] == 'admin');

if (!isset($_GET['order_id'])) {
    if ($is_admin) header("Location: admin_orders.php");
    else header("Location: my_orders.php");
    exit;
}

$order_id = (int)$_GET['order_id'];

// Query Order
if ($is_admin) {
    $sql_order = "SELECT o.*, u.username, u.email, u.role 
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.user_id
                  WHERE o.order_id = $order_id";
} else {
    $sql_order = "SELECT o.*, u.username, u.email, u.role 
                  FROM orders o
                  JOIN users u ON o.user_id = u.user_id
                  WHERE o.order_id = $order_id AND o.user_id = $current_user_id";
}

$res_order = mysqli_query($link, $sql_order);

if (mysqli_num_rows($res_order) == 0) {
    echo "<script>alert('Order not found or access denied!'); window.history.back();</script>";
    exit;
}

$order = mysqli_fetch_assoc($res_order);

// Query Items
$sql_items = "SELECT oi.*, v.variant_name, p.name as prod_name, p.image_url 
              FROM order_items oi
              LEFT JOIN product_variants v ON oi.variant_id = v.variant_id
              LEFT JOIN products p ON v.product_id = p.product_id
              WHERE oi.order_id = $order_id";
$res_items = mysqli_query($link, $sql_items);

// Coupon info
$coupon_code = '';
if (!empty($order['coupon_id'])) {
    $cid = $order['coupon_id'];
    $res_cp = mysqli_query($link, "SELECT * FROM coupons WHERE coupon_id = $cid");
    if ($cp = mysqli_fetch_assoc($res_cp)) {
        $coupon_code = $cp['code'];
    }
}

$page_title = "Order Details #$order_id";
include 'header.php';
?>

<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <?php echo $is_admin ? "<span class='badge badge-danger mr-2'>ADMIN</span>" : ""; ?>
            Order #<?php echo $order_id; ?>
        </h3>
        <a href="<?php echo $is_admin ? 'admin_orders.php' : 'my_orders.php'; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <!-- Order Info Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-info-circle mr-1"></i> Order Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Order Date:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Status:</strong> 
                        <?php 
                            $status_map = [
                                'pending' => 'Pending', 'paid' => 'Paid',
                                'processing' => 'Processing', 'shipped' => 'Shipped',
                                'completed' => 'Completed', 'cancelled' => 'Cancelled'
                            ];
                            $st = $order['status'];
                            $badge_color = ($st == 'completed') ? 'success' : (($st == 'cancelled') ? 'danger' : 'warning');
                            echo "<span class='badge badge-$badge_color'>" . ucfirst($st) . "</span>";
                        ?>
                    </p>
                    <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?> (<?php echo $order['email']; ?>)</p>
                    <p><strong>Shipping Address:</strong><br> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-box-open mr-1"></i> Order Items
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Total</th>
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
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo !empty($item['image_url']) ? $item['image_url'] : 'https://via.placeholder.com/40'; ?>" width="40" class="mr-2 rounded">
                                <?php echo $item['prod_name'] ?? '<em class="text-muted">Deleted Product</em>'; ?>
                            </div>
                        </td>
                        <td><?php echo $item['variant_name']; ?></td>
                        <td class="text-center">$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right">$<?php echo number_format($line_total, 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <tr>
                        <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <?php if ($subtotal > $order['total_amount']): ?>
                    <tr>
                        <td colspan="4" class="text-right text-success">
                            <strong>Discount <?php echo $coupon_code ? "($coupon_code)" : ""; ?>:</strong>
                        </td>
                        <td class="text-right text-success">-$<?php echo number_format($subtotal - $order['total_amount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="4" class="text-right text-danger"><h4>Total:</h4></td>
                        <td class="text-right text-danger"><h4>$<?php echo number_format($order['total_amount'], 2); ?></h4></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- REVIEWS SECTION - Chỉ hiện khi order completed -->
    <?php if ($order['status'] == 'completed' && !$is_admin): ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-dark text-white">
            <i class="fas fa-star"></i> PRODUCT REVIEWS
        </div>
        <div class="card-body">
            <div class="card-body">
                <?php
                // Lấy danh sách sản phẩm trong đơn hàng
                mysqli_data_seek($res_items, 0); // Reset pointer về đầu
                while ($item = mysqli_fetch_assoc($res_items)):
                    // Lấy product_id từ variant
                    $variant_query = mysqli_query($link, "SELECT product_id FROM product_variants WHERE variant_id = {$item['variant_id']}");
                    $variant_data = mysqli_fetch_assoc($variant_query);
                    $product_id = $variant_data['product_id'];
                    
                    // Check đã review chưa
                    $check_review = mysqli_query($link, "SELECT * FROM product_reviews WHERE user_id = $current_user_id AND product_id = $product_id");
                    $existing_review = mysqli_fetch_assoc($check_review);
                    
                    // Handle review submission for this product
                    $review_msg = "";
                    if (isset($_POST['submit_review_' . $product_id])) {
                        $rating = (int)$_POST['rating_' . $product_id];
                        $text = mysqli_real_escape_string($link, $_POST['review_text_' . $product_id]);
                        
                        // Upload images
                        $image_paths = [];
                        if (isset($_FILES['review_images_' . $product_id]['tmp_name'])) {
                            $upload_dir = "uploads/reviews/";
                            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                            
                            foreach ($_FILES['review_images_' . $product_id]['tmp_name'] as $key => $tmp_name) {
                                if ($_FILES['review_images_' . $product_id]['error'][$key] == 0 && count($image_paths) < 5) {
                                    $filename = time() . "_" . $key . "_" . basename($_FILES['review_images_' . $product_id]['name'][$key]);
                                    if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                                        $image_paths[] = $upload_dir . $filename;
                                    }
                                }
                            }
                        }
                        $images_str = implode(',', $image_paths);
                        
                        if ($existing_review) {
                            mysqli_query($link, "UPDATE product_reviews SET rating=$rating, review_text='$text', review_images='$images_str' WHERE review_id={$existing_review['review_id']}");
                        } else {
                            mysqli_query($link, "INSERT INTO product_reviews (product_id,user_id,rating,review_text,review_images) VALUES ($product_id,$current_user_id,$rating,'$text','$images_str')");
                        }
                        $review_msg = "<div class='alert alert-success'>Review submitted successfully!</div>";
                        
                        // Refresh to show updated review
                        echo "<script>setTimeout(function(){ location.reload(); }, 1000);</script>";
                    }
                ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['prod_name']; ?>" style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px; border-radius: 5px;">
                            <div>
                                <strong><?php echo $item['prod_name']; ?></strong><br>
                                <small class="text-muted"><?php echo $item['variant_name']; ?></small>
                            </div>
                        </div>
                        
                        <?php echo $review_msg; ?>
                        
                        <?php if ($existing_review): ?>
                            <!-- Đã review - Hiện review -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <strong>Your Review:</strong>
                                <div class="mt-2">
                                    <div class="stars" style="color: #ffc107; font-size: 18px;">
                                        <?php for($i=1;$i<=5;$i++) echo ($i<=$existing_review['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                                    </div>
                                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($existing_review['review_text'])); ?></p>
                                    <?php if(!empty($existing_review['review_images'])): ?>
                                        <div class="mt-2">
                                            <?php foreach(explode(',', $existing_review['review_images']) as $img): ?>
                                                <img src="<?php echo $img; ?>" style="width:60px;height:60px;object-fit:cover;margin-right:5px;border-radius:5px;">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <a href="product_detail.php?id=<?php echo $product_id; ?>" class="btn btn-sm btn-outline-primary mt-2">View All Reviews</a>
                            </div>
                        <?php else: ?>
                            <!-- Chưa review - Hiện form -->
                            <form method="POST" enctype="multipart/form-data" class="review-form-inline">
                                <input type="hidden" name="rating_<?php echo $product_id; ?>" id="rating-<?php echo $product_id; ?>" required>
                                
                                <label><strong>Rating *</strong></label>
                                <div class="star-input-clickable mb-3" data-product="<?php echo $product_id; ?>" style="font-size: 24px; cursor: pointer;">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <i class="star-icon far fa-star" data-rating="<?php echo $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label><strong>Your Review *</strong></label>
                                    <textarea name="review_text_<?php echo $product_id; ?>" class="form-control" rows="3" required placeholder="Share your experience with this product..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label><strong>Product Images</strong></label>
                                    <input type="file" name="review_images_<?php echo $product_id; ?>[]" class="form-control-file" accept="image/*" multiple>
                                    <small class="text-muted">Select up to 5 images</small>
                                </div>
                                
                                <button type="submit" name="submit_review_<?php echo $product_id; ?>" class="btn btn-warning">
                                    <i class="fas fa-paper-plane"></i> Submit Review
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-right mt-4">
        <?php if ($is_admin): ?>
            <button class="btn btn-info" onclick="window.print()"><i class="fas fa-print mr-1"></i> Print Invoice</button>
        <?php else: ?>
            <?php if ($order['status'] == 'pending'): ?>
                <button class="btn btn-danger" onclick="alert('Please contact hotline to cancel this order!')">Request Cancellation</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div>

<script>
// Star rating for multiple products
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.star-input-clickable').forEach(starContainer => {
        const productId = starContainer.dataset.product;
        const stars = starContainer.querySelectorAll('.star-icon');
        const ratingInput = document.getElementById('rating-' + productId);
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                ratingInput.value = rating;
                stars.forEach((s, idx) => {
                    s.classList.toggle('fas', idx < rating);
                    s.classList.toggle('far', idx >= rating);
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                stars.forEach((s, idx) => s.style.color = (idx < rating) ? '#ffc107' : '#ddd');
            });
        });
        
        starContainer.addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value) || 0;
            stars.forEach((s, idx) => s.style.color = (idx < currentRating) ? '#ffc107' : '#ddd');
        });
    });
});
</script>

<?php include 'footer.php'; ?>