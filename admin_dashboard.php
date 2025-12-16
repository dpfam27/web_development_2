<?php
include 'connect.php';
include 'auth.php';
require_admin();

// 1. REVENUE (Last 7 Days)
$sql_revenue = "SELECT DATE(created_at) as date, SUM(total_amount) as total 
                FROM orders 
                WHERE status = 'completed' 
                AND created_at >= DATE(NOW()) - INTERVAL 7 DAY
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
$res_revenue = mysqli_query($link, $sql_revenue);

$dates = [];
$revenues = [];
while ($row = mysqli_fetch_assoc($res_revenue)) {
    $dates[] = date('d/m', strtotime($row['date'])); 
    $revenues[] = (float)$row['total'];
}

// 2. TOP PRODUCTS (Top 5)
$sql_top_products = "SELECT p.name, SUM(oi.quantity) as total_sold
                     FROM order_items oi
                     JOIN product_variants v ON oi.variant_id = v.variant_id
                     JOIN products p ON v.product_id = p.product_id
                     JOIN orders o ON oi.order_id = o.order_id
                     WHERE o.status = 'completed'
                     GROUP BY p.product_id
                     ORDER BY total_sold DESC
                     LIMIT 5";
$res_top = mysqli_query($link, $sql_top_products);

$prod_names = [];
$prod_sales = [];
while ($row = mysqli_fetch_assoc($res_top)) {
    $prod_names[] = $row['name'];
    $prod_sales[] = (int)$row['total_sold'];
}

// 3. COUNTERS
$total_orders = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM orders"))['c'];
$total_products = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM products"))['c'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM orders WHERE status='pending'"))['c'];

$page_title = "Admin Dashboard";
include 'header.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .card-counter { box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin: 5px; padding: 20px; background-color: #fff; height: 120px; border-radius: 5px; transition: .3s linear all; position: relative; overflow: hidden; }
    .card-counter:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .card-counter.primary { background: linear-gradient(45deg, #007bff, #0056b3); color: #FFF; }
    .card-counter.danger { background: linear-gradient(45deg, #dc3545, #a71d2a); color: #FFF; }
    .card-counter.success { background: linear-gradient(45deg, #28a745, #1e7e34); color: #FFF; }
    .card-counter i { font-size: 4em; opacity: 0.3; position: absolute; top: 15px; left: 15px; }
    .count-numbers { position: absolute; right: 20px; top: 20px; font-size: 32px; font-weight: bold; display: block; }
    .count-name { position: absolute; right: 20px; top: 65px; font-style: italic; text-transform: uppercase; opacity: 0.8; display: block; font-size: 14px; }
</style>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Dashboard Overview</h2>
    
    <!-- Counters -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card-counter primary">
                <i class="fas fa-shopping-cart"></i>
                <span class="count-numbers"><?php echo $total_orders; ?></span>
                <span class="count-name">Total Orders</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-counter danger">
                <i class="fas fa-clock"></i>
                <span class="count-numbers"><?php echo $pending_orders; ?></span>
                <span class="count-name">Pending Orders</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-counter success">
                <i class="fas fa-box"></i>
                <span class="count-numbers"><?php echo $total_products; ?></span>
                <span class="count-name">Total Products</span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white font-weight-bold border-bottom">
                    <i class="fas fa-chart-line text-primary mr-2"></i> Revenue (Last 7 Days)
                </div>
                <div class="card-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white font-weight-bold border-bottom">
                    <i class="fas fa-trophy text-warning mr-2"></i> Best Sellers
                </div>
                <div class="card-body">
                    <canvas id="productChart"></canvas>
                </div>
            </div>
            
            <!-- Quick Menu -->
            <div class="list-group shadow-sm">
                <a href="admin_orders.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-clipboard-list mr-2 text-info"></i> Manage Orders
                </a>
                <a href="add_product.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-plus-circle mr-2 text-success"></i> Add New Product
                </a>
                <a href="index.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-home mr-2 text-primary"></i> View Homepage
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Revenue Chart
    var ctxRev = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(ctxRev, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?php echo json_encode($revenues); ?>,
                borderColor: '#48cf29',
                backgroundColor: 'rgba(72, 207, 41, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#48cf29',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, grid: { color: '#333' }, ticks: { color: '#ccc' } }, x: { grid: { color: '#333' }, ticks: { color: '#ccc' } } },
            plugins: { legend: { labels: { color: '#fff' } } }
        }
    });

    // 2. Product Chart
    var ctxProd = document.getElementById('productChart').getContext('2d');
    var productChart = new Chart(ctxProd, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($prod_names); ?>,
            datasets: [{
                data: <?php echo json_encode($prod_sales); ?>,
                backgroundColor: ['#48cf29', '#36a81b', '#28a745', '#20c997', '#17a2b8'],
                borderColor: '#111'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#fff' } } }
        }
    });
</script>

<?php include 'footer.php'; ?>