<?php
include 'connect.php';
include 'auth.php';
require_admin();

$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($pid == 0) { header("Location: index.php"); exit; }

$msg = "";

// 1. UPDATE PRODUCT INFO
if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $desc = mysqli_real_escape_string($link, $_POST['description']);
    $raw_base_price = $_POST['base_price'];
    
    if (!is_numeric($raw_base_price) || (float)$raw_base_price <= 0) {
        $msg = "<div class='alert alert-danger'>Error: Base price must be a positive number!</div>";
    } else {
        $base_price = (float)$raw_base_price;
        $img_sql = "";
        if (!empty($_FILES['image']['name'])) {
            $target = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $img_sql = ", image_url='$target'";
        }
        $sql = "UPDATE products SET name='$name', description='$desc', base_price=$base_price $img_sql WHERE product_id=$pid";
        if(mysqli_query($link, $sql)) $msg = "<div class='alert alert-success'>Product updated successfully!</div>";
        else $msg = "<div class='alert alert-danger'>Error: ".mysqli_error($link)."</div>";
    }
}

// 2. ADD NEW VARIANT
if (isset($_POST['add_variant'])) {
    $vname = $_POST['new_vname'];
    $raw_vprice = $_POST['new_vprice'];
    $raw_vstock = $_POST['new_vstock'];
    
    if (!is_numeric($raw_vprice) || !is_numeric($raw_vstock) || (float)$raw_vprice <= 0 || (int)$raw_vstock < 0) {
        $msg = "<div class='alert alert-danger'>Error: Price must be positive and Stock cannot be negative!</div>";
    } else {
        $vprice = (float)$raw_vprice;
        $vstock = (int)$raw_vstock;
        if(mysqli_query($link, "INSERT INTO product_variants (product_id, variant_name, price, stock) VALUES ($pid, '$vname', $vprice, $vstock)")) {
            $msg = "<div class='alert alert-success'>New variant added!</div>";
        }
    }
}

// 3. UPDATE EXISTING VARIANT
if (isset($_POST['save_variant'])) {
    $vid = $_POST['vid'];
    $vname = $_POST['vname'];
    $raw_vprice = $_POST['vprice'];
    $raw_vstock = $_POST['vstock'];
    
    if (!is_numeric($raw_vprice) || !is_numeric($raw_vstock) || (float)$raw_vprice <= 0 || (int)$raw_vstock < 0) {
        $msg = "<div class='alert alert-danger'>Error: Price must be positive and Stock cannot be negative!</div>";
    } else {
        $vprice = (float)$raw_vprice;
        $vstock = (int)$raw_vstock;
        mysqli_query($link, "UPDATE product_variants SET variant_name='$vname', price=$vprice, stock=$vstock WHERE variant_id=$vid");
        $msg = "<div class='alert alert-success'>Variant updated!</div>";
    }
}

$prod = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM products WHERE product_id=$pid"));
$vars = mysqli_query($link, "SELECT * FROM product_variants WHERE product_id=$pid");

$page_title = "Edit Product - Admin";
include 'header.php';
?>

<div class="container pb-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Edit Product: <span class="text-primary"><?php echo htmlspecialchars($prod['name']); ?></span></h3>
        <a href="delete.php?id=<?php echo $pid; ?>" class="btn btn-danger" onclick="return confirmDelete()">
            <i class="fas fa-trash-alt"></i> Delete Product
        </a>
    </div>

    <?php echo $msg; ?>

    <div class="row">
        <!-- Left Column: General Info -->
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white font-weight-bold">
                    1. General Information
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo !empty($prod['image_url']) ? $prod['image_url'] : 'https://via.placeholder.com/150'; ?>" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $prod['name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Base Price ($)</label>
                            <input type="number" step="0.01" name="base_price" class="form-control" value="<?php echo $prod['base_price']; ?>" required min="0">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo $prod['description']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Change Image</label>
                            <input type="file" name="image" class="form-control-file">
                        </div>
                        <button type="submit" name="update_product" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Variants -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white font-weight-bold">
                    2. Manage Variants (Flavor / Size)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Add New Variant Form -->
                                <tr class="table-warning">
                                    <form method="post">
                                        <td><input type="text" name="new_vname" placeholder="New variant..." class="form-control form-control-sm" required></td>
                                        <td><input type="number" step="0.01" name="new_vprice" placeholder="Price" class="form-control form-control-sm" required min="0"></td>
                                        <td><input type="number" name="new_vstock" placeholder="Qty" class="form-control form-control-sm" value="100" min="0"></td>
                                        <td>
                                            <button type="submit" name="add_variant" class="btn btn-sm btn-success btn-block" title="Add">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                
                                <!-- Existing Variants List -->
                                <?php while($v = mysqli_fetch_assoc($vars)): ?>
                                <tr>
                                    <form method="post">
                                        <input type="hidden" name="vid" value="<?php echo $v['variant_id']; ?>">
                                        <td><input type="text" name="vname" value="<?php echo $v['variant_name']; ?>" class="form-control form-control-sm"></td>
                                        <td><input type="number" step="0.01" name="vprice" value="<?php echo $v['price']; ?>" class="form-control form-control-sm" min="0"></td>
                                        <td><input type="number" name="vstock" value="<?php echo $v['stock']; ?>" class="form-control form-control-sm" min="0"></td>
                                        <td class="d-flex">
                                            <button type="submit" name="save_variant" class="btn btn-primary btn-sm mr-1" title="Save"><i class="fas fa-save"></i></button>
                                            <a href="delete.php?type=variant&id=<?php echo $v['variant_id']; ?>&pid=<?php echo $pid; ?>" 
                                               class="btn btn-danger btn-sm" onclick="return confirm('Delete this variant?')" title="Delete"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </form>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete() {
        if (confirm("WARNING: Are you sure you want to delete this product?")) {
            return confirm("This action will permanently delete the product and ALL its variants!\n\nDo you really want to proceed?");
        }
        return false;
    }
</script>

<?php include 'footer.php'; ?>