<?php
include 'connect.php';
include 'auth.php';
require_admin();

$msg = "";

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $cat = mysqli_real_escape_string($link, $_POST['category']);
    $desc = mysqli_real_escape_string($link, $_POST['description']);
    $raw_base_price = $_POST['base_price'];
    $var_name = mysqli_real_escape_string($link, $_POST['variant_name']);
    $raw_var_price = $_POST['variant_price'];
    $raw_stock = $_POST['stock'];

    if (!is_numeric($raw_base_price) || !is_numeric($raw_var_price) || !is_numeric($raw_stock)) {
        $msg = "<div class='alert alert-danger'>Error: Price and Stock must be numbers!</div>";
    } else {
        $base_price = (float)$raw_base_price;
        $var_price = (float)$raw_var_price;
        $stock = (int)$raw_stock;

        if ($base_price <= 0 || $var_price <= 0 || $stock < 0) {
            $msg = "<div class='alert alert-danger'>Error: Invalid number values!</div>";
        } else {
            $check_res = mysqli_query($link, "SELECT product_id FROM products WHERE name = '$name'");
            if (mysqli_num_rows($check_res) > 0) {
                echo "<script>alert('Product already exists!');</script>";
            } else {
                $img_url = "";
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "uploads/";
                    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) $img_url = $target_file;
                }

                $sql1 = "INSERT INTO products (name, description, category, base_price, image_url) VALUES ('$name', '$desc', '$cat', $base_price, '$img_url')";
                if (mysqli_query($link, $sql1)) {
                    $pid = mysqli_insert_id($link);
                    $sql2 = "INSERT INTO product_variants (product_id, variant_name, price, stock) VALUES ($pid, '$var_name', $var_price, $stock)";
                    if (mysqli_query($link, $sql2)) $msg = "<div class='alert alert-success'>Product added successfully!</div>";
                }
            }
        }
    }
}

$page_title = "Admin - Add Product";
include 'header.php';
?>

<div class="container mb-5 mt-4">
    <h2 class="mb-4">Add New Product</h2>
    <?php echo $msg; ?>
    
    <form method="post" enctype="multipart/form-data" class="card shadow-sm p-4">
        <div class="row">
            <div class="col-md-6">
                <h4 class="text-primary border-bottom pb-2">1. General Info</h4>
                <div class="form-group">
                    <label>Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <option value="Whey Protein">Whey Protein</option>
                        <option value="Pre-workout">Pre-workout</option>
                        <option value="Mass Gainer">Mass Gainer</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price ($)</label>
                    <input type="number" step="0.01" name="base_price" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" class="form-control-file">
                </div>
            </div>
            
            <div class="col-md-6">
                <h4 class="text-success border-bottom pb-2">2. First Variant</h4>
                <div class="form-group">
                    <label>Variant Name (e.g., 5Lbs Chocolate)</label>
                    <input type="text" name="variant_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Selling Price ($)</label>
                    <input type="number" step="0.01" name="variant_price" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" class="form-control" value="100" min="0">
                </div>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-end">
            <a href="index.php" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" name="submit" class="btn btn-primary btn-lg px-5">Save Product</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>