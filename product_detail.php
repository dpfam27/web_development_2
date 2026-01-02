<?php
include 'connect.php';
include 'auth.php';

$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query_prod = "SELECT * FROM products WHERE product_id = $pid";
$res_prod = mysqli_query($link, $query_prod);
$product = mysqli_fetch_assoc($res_prod);

// Nếu không tìm thấy sản phẩm, chuyển hướng về trang shop
if (!$product) { 
    header("Location: products.php"); 
    exit; 
}

// Lấy danh sách biến thể (Variant)
$query_vars = "SELECT * FROM product_variants WHERE product_id = $pid";
$res_vars = mysqli_query($link, $query_vars);

$page_title = $product['name'] . " - WheyStore";
include 'header.php';
?>

<div class="container mt-5 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Shop</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Left Column: Image -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/600'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="object-fit: contain; max-height: 500px;">
            </div>
        </div>

        <!-- Right Column: Details -->
        <div class="col-md-6">
            <h2 class="font-weight-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h2>
            
            <div class="mb-3">
                <span class="badge badge-info p-2" style="font-size: 0.9rem;"><?php echo htmlspecialchars($product['category']); ?></span>
            </div>

            <div class="mb-4">
                <h3 class="text-danger font-weight-bold" id="display-price">
                    $<?php echo number_format($product['base_price'], 2); ?>
                </h3>
            </div>

            <p class="lead" style="font-size: 1rem; line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>
            
            <hr class="my-4">
            
            <form action="cart.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="font-weight-bold">Select Option (Flavor / Size):</label>
                    <select name="variant_id" class="form-control form-control-lg" required id="variant-select">
                        <option value="" data-price="<?php echo $product['base_price']; ?>">-- Choose an option --</option>
                        <?php 
                        $has_stock = false;
                        while($v = mysqli_fetch_assoc($res_vars)): 
                            if ($v['stock'] > 0) $has_stock = true;
                        ?>
                            <option value="<?php echo $v['variant_id']; ?>" 
                                    data-price="<?php echo $v['price']; ?>"
                                    <?php echo ($v['stock'] <= 0 ? 'disabled' : ''); ?>>
                                <?php echo $v['variant_name']; ?> - $<?php echo number_format($v['price'], 2); ?> 
                                <?php echo ($v['stock'] <= 0 ? '(Out of Stock)' : '(In Stock: '.$v['stock'].')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="font-weight-bold">Quantity:</label>
                    <div class="input-group" style="width: 150px;">
                        <input type="number" name="quantity" value="1" min="1" class="form-control form-control-lg text-center">
                    </div>
                </div>

                <div class="mt-4">
                    <?php if ($has_stock): ?>
                        <button type="submit" class="btn btn-primary btn-lg btn-block" style="border-radius: 50px; padding: 15px;">
                            <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-lg btn-block" disabled style="border-radius: 50px; padding: 15px;">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                    
                    <a href="products.php" class="btn btn-outline-secondary btn-block mt-3" style="border-radius: 50px;">
                        Continue Shopping
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script to update price when variant changes -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const variantSelect = document.getElementById('variant-select');
        const priceDisplay = document.getElementById('display-price');

        if (variantSelect) {
            variantSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                if (price) {
                    priceDisplay.textContent = '$' + parseFloat(price).toFixed(2);
                }
            });
        }
    });
</script>

<?php include 'footer.php'; ?>