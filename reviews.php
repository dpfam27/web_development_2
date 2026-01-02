<?php
// Reviews Display - Read Only
include_once 'connect.php';
include_once 'auth.php';

if (!isset($pid)) die("Product ID required");

// Get data
$stats = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as total, COALESCE(AVG(rating),0) as avg FROM product_reviews WHERE product_id=$pid"));
$reviews = mysqli_query($link, "SELECT pr.*, u.username FROM product_reviews pr JOIN users u ON pr.user_id=u.user_id WHERE pr.product_id=$pid ORDER BY pr.created_at DESC");
?>

<div class="review-sec container">
    <h3 class="mb-4"><i class="fas fa-star text-warning"></i> Customer Reviews (<?= $stats['total'] ?>)</h3>
    
    <div class="review-stats mb-4">
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
    
    <?php if($stats['total']==0): ?>
        <div class="alert alert-secondary">No reviews yet. Be the first to review this product!</div>
    <?php else: ?>
        <?php while($r=mysqli_fetch_assoc($reviews)): ?>
            <div class="review-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stars" style="font-size:16px;">
                            <?php for($i=1;$i<=5;$i++) echo ($i<=$r['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        </div>
                        <strong><?= htmlspecialchars($r['username']) ?></strong>
                        <?php if(is_logged_in() && $_SESSION['user_id']==$r['user_id']): ?>
                            <span class="badge badge-primary ml-2">Your Review</span>
                        <?php endif; ?>
                        <small class="text-muted ml-2"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                    </div>
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
