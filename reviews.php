<?php
// Reviews System - Giống Shopee
include_once 'connect.php';
include_once 'auth.php';

if (!isset($pid)) die("Product ID required");

$msg = "";

// Submit/Update Review
if (isset($_POST['submit_review']) && is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $text = mysqli_real_escape_string($link, $_POST['review_text']);
    
    // Upload images
    $image_paths = [];
    if (isset($_FILES['review_images'])) {
        $upload_dir = "uploads/reviews/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        foreach ($_FILES['review_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['review_images']['error'][$key] == 0) {
                $filename = time() . "_" . $key . "_" . basename($_FILES['review_images']['name'][$key]);
                $target = $upload_dir . $filename;
                if (move_uploaded_file($tmp_name, $target)) {
                    $image_paths[] = $target;
                }
            }
        }
    }
    $images_str = implode(',', $image_paths);
    
    // Check purchase - chỉ cho review khi đã GIAO HÀNG (giống Shopee)
    $check = mysqli_query($link, "SELECT COUNT(*) as c FROM orders o 
        JOIN order_items oi ON o.order_id=oi.order_id 
        JOIN product_variants pv ON oi.variant_id=pv.variant_id 
        WHERE o.user_id=$uid AND pv.product_id=$pid AND o.status='completed'");
    
    if (mysqli_fetch_assoc($check)['c'] > 0) {
        $exist = mysqli_query($link, "SELECT review_id FROM product_reviews WHERE user_id=$uid AND product_id=$pid");
        if (mysqli_num_rows($exist) > 0) {
            $rid = mysqli_fetch_assoc($exist)['review_id'];
            mysqli_query($link, "UPDATE product_reviews SET rating=$rating, review_text='$text', review_images='$images_str' WHERE review_id=$rid");
        } else {
            mysqli_query($link, "INSERT INTO product_reviews (product_id,user_id,rating,review_text,review_images) VALUES ($pid,$uid,$rating,'$text','$images_str')");
        }
        $msg = "<div class='alert alert-success'>Đánh giá đã được gửi!</div>";
    } else {
        $msg = "<div class='alert alert-warning'>Chỉ có thể đánh giá sau khi đã nhận hàng.</div>";
    }
}

// Delete Review
if (isset($_GET['del_review']) && is_logged_in()) {
    mysqli_query($link, "DELETE FROM product_reviews WHERE review_id=".(int)$_GET['del_review']." AND user_id=".$_SESSION['user_id']);
    header("Location: product_detail.php?id=$pid"); exit;
}

// Get Stats
$stats = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as total, COALESCE(AVG(rating),0) as avg FROM product_reviews WHERE product_id=$pid"));
$total = $stats['total'];
$avg = round($stats['avg'], 1);

// Get Reviews
$reviews = mysqli_query($link, "SELECT pr.*, u.username 
    FROM product_reviews pr JOIN users u ON pr.user_id=u.user_id 
    WHERE pr.product_id=$pid ORDER BY pr.created_at DESC");

// Check if user can review
$can_review = false;
$my_review = null;
if (is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $chk = mysqli_query($link, "SELECT COUNT(*) as c FROM orders o 
        JOIN order_items oi ON o.order_id=oi.order_id 
        JOIN product_variants pv ON oi.variant_id=pv.variant_id 
        WHERE o.user_id=$uid AND pv.product_id=$pid AND o.status='completed'");
    $can_review = mysqli_fetch_assoc($chk)['c'] > 0;
    
    $mr = mysqli_query($link, "SELECT * FROM product_reviews WHERE user_id=$uid AND product_id=$pid");
    if (mysqli_num_rows($mr) > 0) $my_review = mysqli_fetch_assoc($mr);
}
?>

<div class="review-sec container">
    <h3 class="mb-4"><i class="fas fa-star text-warning"></i> Reviews (<?php echo $total; ?>)</h3>
    <?php echo $msg; ?>
    
    <div class="review-stats">
        <div class="row">
            <div class="col-md-4 avg-box">
                <div class="avg-num"><?php echo $avg; ?></div>
                <div class="stars">
                    <?php for($i=1;$i<=5;$i++) echo ($i<=$avg) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                </div>
                <small class="text-muted"><?php echo $total; ?> reviews</small>
            </div>
            <div class="col-md-8">
                <p class="text-muted">Average rating based on customer feedback</p>
            </div>
        </div>
    </div>
    
    <?php if(is_logged_in()): ?>
        <?php if($can_review): ?>
            <div class="review-form">
                <h5><?php echo $my_review ? 'Chỉnh sửa đánh giá' : 'Viết đánh giá'; ?></h5>
                <?php echo $msg; ?>
                <form method="POST" enctype="multipart/form-data">
                    <label><strong>Đánh giá *</strong></label>
                    <div class="star-input">
                        <?php for($i=5;$i>=1;$i--): ?>
                            <label><input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo ($my_review && $my_review['rating']==$i)?'checked':''; ?> required><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                    <div class="form-group mt-3">
                        <label><strong>Nhận xét *</strong></label>
                        <textarea name="review_text" class="form-control" rows="4" required><?php echo $my_review ? htmlspecialchars($my_review['review_text']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><strong>Hình ảnh sản phẩm</strong></label>
                        <input type="file" name="review_images[]" class="form-control-file" accept="image/*" multiple>
                        <small class="form-text text-muted">Chọn tối đa 5 ảnh</small>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Mua hàng để đánh giá sản phẩm này.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">Vui lòng <a href="login.php">đăng nhập</a> để đánh giá.</div>
    <?php endif; ?>
    
    <h5 class="mt-4 mb-3">Đánh giá từ khách hàng</h5>
    <?php if($total==0): ?>
        <div class="alert alert-secondary">Chưa có đánh giá nào.</div>
    <?php else: ?>
        <?php while($r=mysqli_fetch_assoc($reviews)): ?>
            <div class="review-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stars" style="font-size:16px;">
                            <?php for($i=1;$i<=5;$i++) echo ($i<=$r['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        </div>
                        <strong><?php echo htmlspecialchars($r['username']); ?></strong>
                        <small class="text-muted ml-2"><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></small>
                    </div>
                    <?php if(is_logged_in() && $_SESSION['user_id']==$r['user_id']): ?>
                        <a href="?id=<?php echo $pid; ?>&del_review=<?php echo $r['review_id']; ?>" 
                           class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa đánh giá?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <p class="mt-2 mb-2"><?php echo nl2br(htmlspecialchars($r['review_text'])); ?></p>
                
                <?php if(!empty($r['review_images'])): ?>
                    <div class="review-images mb-2">
                        <?php foreach(explode(',', $r['review_images']) as $img): ?>
                            <a href="<?php echo $img; ?>" target="_blank">
                                <img src="<?php echo $img; ?>" alt="Review Image" style="width:80px;height:80px;object-fit:cover;margin-right:5px;border-radius:5px;">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-input label');
    stars.forEach((s, i) => {
        s.addEventListener('mouseenter', () => stars.forEach((l, j) => l.style.color = (j >= i) ? '#ffc107' : '#ddd'));
    });
    document.querySelector('.star-input')?.addEventListener('mouseleave', function() {
        const checked = this.querySelector('input:checked');
        if(checked) {
            const v = parseInt(checked.value);
            stars.forEach((l, i) => l.style.color = (5-i <= v) ? '#ffc107' : '#ddd');
        }
    });
});
</script>
