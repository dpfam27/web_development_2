<?php
// Reviews System - Shopee Style
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
    if (isset($_FILES['review_images']['tmp_name'])) {
        $upload_dir = "uploads/reviews/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        foreach ($_FILES['review_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['review_images']['error'][$key] == 0 && count($image_paths) < 5) {
                $filename = time() . "_" . $key . "_" . basename($_FILES['review_images']['name'][$key]);
                if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                    $image_paths[] = $upload_dir . $filename;
                }
            }
        }
    }
    $images_str = implode(',', $image_paths);
    
    // Check purchase
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
        $msg = "<div class='alert alert-success'>Review submitted successfully!</div>";
    } else {
        $msg = "<div class='alert alert-warning'>You can only review after receiving the product.</div>";
    }
}

// Delete Review
if (isset($_GET['del_review']) && is_logged_in()) {
    mysqli_query($link, "DELETE FROM product_reviews WHERE review_id=".(int)$_GET['del_review']." AND user_id=".$_SESSION['user_id']);
    header("Location: product_detail.php?id=$pid"); exit;
}

// Get data
$stats = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as total, COALESCE(AVG(rating),0) as avg FROM product_reviews WHERE product_id=$pid"));
$reviews = mysqli_query($link, "SELECT pr.*, u.username FROM product_reviews pr JOIN users u ON pr.user_id=u.user_id WHERE pr.product_id=$pid ORDER BY pr.created_at DESC");

// Check if user can review
$can_review = false;
$my_review = null;
if (is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $can_review = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM orders o 
        JOIN order_items oi ON o.order_id=oi.order_id JOIN product_variants pv ON oi.variant_id=pv.variant_id 
        WHERE o.user_id=$uid AND pv.product_id=$pid AND o.status='completed'"))['c'] > 0;
    
    $mr = mysqli_query($link, "SELECT * FROM product_reviews WHERE user_id=$uid AND product_id=$pid");
    if (mysqli_num_rows($mr) > 0) $my_review = mysqli_fetch_assoc($mr);
}
?>

<div class="review-sec container">
    <h3 class="mb-4"><i class="fas fa-star text-warning"></i> Reviews (<?= $stats['total'] ?>)</h3>
    <?= $msg ?>
    
    <div class="review-stats">
        <div class="row">
            <div class="col-md-4 avg-box">
                <div class="avg-num"><?= round($stats['avg'], 1) ?></div>
                <div class="stars">
                    <?php for($i=1;$i<=5;$i++) echo ($i<=round($stats['avg'])) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                </div>
                <small class="text-muted"><?= $stats['total'] ?> reviews</small>
            </div>
            <div class="col-md-8"><p class="text-muted">Average rating based on customer feedback</p></div>
        </div>
    </div>
    
    <?php if(is_logged_in()): ?>
        <?php if($can_review): ?>
            <div class="review-form" id="review-form">
                <h5><?= $my_review ? 'Edit Your Review' : 'Write a Review' ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="rating" id="rating-value" value="<?= $my_review['rating'] ?? '' ?>" required>
                    <label><strong>Rating *</strong></label>
                    <div class="star-input-clickable" style="font-size:28px;cursor:pointer;">
                        <?php for($i=1;$i<=5;$i++): ?>
                            <i class="star-icon <?= ($my_review && $i <= $my_review['rating']) ? 'fas' : 'far' ?> fa-star" data-rating="<?= $i ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="form-group mt-3">
                        <label><strong>Your Review *</strong></label>
                        <textarea name="review_text" class="form-control" rows="4" required><?= $my_review ? htmlspecialchars($my_review['review_text']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><strong>Product Images</strong></label>
                        <input type="file" name="review_images[]" class="form-control-file" accept="image/*" multiple>
                        <small class="form-text text-muted">Select up to 5 images</small>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Review</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Purchase this product to review.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">Please <a href="login.php">login</a> to review.</div>
    <?php endif; ?>
    
    <h5 class="mt-4 mb-3">Customer Reviews</h5>
    <?php if($stats['total']==0): ?>
        <div class="alert alert-secondary">No reviews yet.</div>
    <?php else: ?>
        <?php while($r=mysqli_fetch_assoc($reviews)): ?>
            <div class="review-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stars" style="font-size:16px;">
                            <?php for($i=1;$i<=5;$i++) echo ($i<=$r['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        </div>
                        <strong><?= htmlspecialchars($r['username']) ?></strong>
                        <small class="text-muted ml-2"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                    </div>
                    <?php if(is_logged_in() && $_SESSION['user_id']==$r['user_id']): ?>
                        <a href="?id=<?= $pid ?>&del_review=<?= $r['review_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this review?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <p class="mt-2 mb-2"><?= nl2br(htmlspecialchars($r['review_text'])) ?></p>
                <?php if(!empty($r['review_images'])): ?>
                    <div class="review-images mb-2">
                        <?php foreach(explode(',', $r['review_images']) as $img): ?>
                            <a href="<?= $img ?>" target="_blank">
                                <img src="<?= $img ?>" alt="Review" style="width:80px;height:80px;object-fit:cover;margin-right:5px;border-radius:5px;">
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
    const starContainer = document.querySelector('.star-input-clickable');
    if (!starContainer) return;
    
    const stars = starContainer.querySelectorAll('.star-icon');
    const ratingInput = document.getElementById('rating-value');
    
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
</script>
