<?php
include 'connect.php';
include 'auth.php';
require_admin();

if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $st = $_POST['status'];
    mysqli_query($link, "UPDATE orders SET status='$st' WHERE order_id=$oid");
}

$sql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY created_at DESC";
$res = mysqli_query($link, $sql);

$page_title = "Manage Orders - Admin";
include 'header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Manage Orders</h2>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-list mr-1"></i> Order List
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td>#<?php echo $row['order_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                        <td class="text-danger font-weight-bold">$<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <select name="status" class="form-control form-control-sm mr-2" style="width: 120px;">
                                    <option value="pending" <?php if($row['status']=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="processing" <?php if($row['status']=='processing') echo 'selected'; ?>>Processing</option>
                                    <option value="completed" <?php if($row['status']=='completed') echo 'selected'; ?>>Completed</option>
                                    <option value="cancelled" <?php if($row['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary btn-sm" title="Save Status">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td><?php echo date('d/m/y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>