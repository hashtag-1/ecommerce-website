<?php
// Seed2Greens - Admin Dashboard
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Dashboard - Seed2Greens Admin';
$total_products = getTotalProducts();
$total_users = getTotalUsers();
$total_orders = getTotalOrders();
$pending_orders = getOrderCountByStatus('Pending');
$total_sales = getTotalSales();
$recent_orders = getAllOrdersForAdmin();
$recent_orders = array_slice($recent_orders, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-leaf"></i> Seed2Greens</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-main">
            <header class="admin-header">
                <h2>Dashboard</h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span>Welcome, <?php echo sanitize($_SESSION['admin_name']); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon products"><i class="fas fa-box"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $total_products; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon users"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $total_users; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $total_orders; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $pending_orders; ?></h3>
                            <p>Pending Orders</p>
                        </div>
                    </div>
                </div>
                
                <!-- Sales & Recent Orders -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <div class="admin-card">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Total Sales</h3>
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: 36px; font-weight: 700; color: var(--primary-dark);">
                                Rs. <?php echo number_format($total_sales, 2); ?>
                            </div>
                            <p style="color: var(--text-light); margin-top: 10px;">Total Revenue Generated</p>
                        </div>
                    </div>
                    
                    <div class="admin-card">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Recent Orders</h3>
                        <table class="data-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo sanitize($order['customer_name']); ?></td>
                                        <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                        <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
