<?php
include 'connect.php';
include 'auth.php';

$search = "";
$title_text = "All Products";

// Logic SQL (Same as before)
$sql_base = "SELECT p.*, COALESCE(SUM(pv.stock), 0) as total_stock 
             FROM products p 
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id ";

$where = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($link, $_GET['search']);
    $where[] = "p.name LIKE '%$search%'";
    $title_text = "Search results for: '" . htmlspecialchars($_GET['search']) . "'";
} 
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $cat = mysqli_real_escape_string($link, $_GET['category']);
    $where[] = "p.category = '$cat'";
    $title_text = "Category: " . htmlspecialchars($_GET['category']);
}

if (!empty($where)) {
    $sql_base .= " WHERE " . implode(" AND ", $where);
}

$sql_base .= " GROUP BY p.product_id ORDER BY (total_stock > 0) DESC, p.created_at DESC";
$result = mysqli_query($link, $sql_base);

$page_title = "Shop - WheyStore";
include 'header.php';
?>
<div class="container mt-4 mb-5">
    <h4 class="mb-4 text-muted border-bottom pb-2"><?php echo $title_text; ?></h4>
    <div class="row">
        <?php
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $is_sold_out = ($row['total_stock'] <= 0);
        ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm product-card <?php echo $is_sold_out ? 'sold-out-card' : ''; ?>" style="position: relative;">
                    
                    <?php if($is_sold_out): ?>
                        <div class="sold-out-overlay"><span class="sold-out-text">SOLD OUT</span></div>
                    <?php endif; ?>

                    <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/300?text=No+Image'; ?>" class="card-img-top product-img" style="height: 200px; object-fit: contain; padding: 10px;">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title" style="font-size: 1.1rem;"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></p>
                        <p class="text-danger font-weight-bold">$<?php echo number_format($row['base_price'], 2); ?></p>
                        
                        <div class="mt-auto">
                            <?php if(is_admin()): ?>
                                <a href="edit.php?id=<?php echo $row['product_id']; ?>" class="btn btn-warning btn-sm btn-block font-weight-bold" style="position: relative; z-index: 11;"><i class="fas fa-edit"></i> Edit & View</a>
                            <?php else: ?>
                                <?php if($is_sold_out): ?>
                                    <button class="btn btn-secondary btn-sm btn-block" disabled>Sold Out</button>
                                <?php else: ?>
                                    <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-outline-primary btn-sm btn-block">View & Buy</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<div class='col-12 text-center p-5'><h4 class='text-muted'>No products found!</h4><a href='products.php' class='btn btn-secondary mt-3'>View All Products</a></div>";
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>