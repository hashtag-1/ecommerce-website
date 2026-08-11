<?php
// Seed2Greens - Admin Dashboard
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Dashboard - Seed2Greens Admin';
$stats = getAdminStats();
$recent_orders = getRecentOrders(5);
$low_stock = getLowStockProducts(10);
$out_of_stock = getOutOfStockProducts();
$recent_activity = getRecentActivity(8);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>
        
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-leaf"></i> Seed2Greens</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Dashboard</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span>Welcome, <?php echo sanitize($_SESSION['admin_name']); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon products"><i class="fas fa-box"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon users"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_customers']; ?></h3>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_orders']; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending"><i class="fas fa-rupee-sign"></i></div>
                        <div class="stat-info">
                            <h3><?php echo formatAdminCurrency($stats['completed_revenue']); ?></h3>
                            <p>Completed Revenue</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fff3e0; color: #e65100;"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending_orders']; ?></h3>
                            <p>Pending Orders</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f3e5f5; color: #6a1b9a;"><i class="fas fa-spinner"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['processing_orders']; ?></h3>
                            <p>Processing</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f5e9; color: #1b5e20;"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $stats['delivered_orders']; ?></h3>
                            <p>Delivered</p>
                        </div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-card">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Recent Orders</h3>
                        <table class="data-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr><td colspan="6" style="text-align: center; padding: 30px;">No orders found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td>
                                                <?php echo sanitize($order['customer_name']); ?><br>
                                                <small style="color: var(--text-light);"><?php echo sanitize($order['customer_email']); ?></small>
                                            </td>
                                            <td><strong><?php echo formatAdminCurrency($order['total_amount']); ?></strong></td>
                                            <td><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div>
                        <div class="admin-card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Stock Alerts</h3>
                            <?php if (empty($low_stock) && empty($out_of_stock)): ?>
                                <p style="color: var(--text-light); text-align: center; padding: 20px;">All products are well stocked.</p>
                            <?php else: ?>
                                <?php if ($out_of_stock): ?>
                                    <p style="font-weight: 600; margin-bottom: 10px; color: #c62828;"><i class="fas fa-exclamation-circle"></i> Out of Stock (<?php echo count($out_of_stock); ?>)</p>
                                    <ul style="margin-bottom: 15px; padding-left: 20px;">
                                        <?php foreach (array_slice($out_of_stock, 0, 5) as $product): ?>
                                            <li style="margin-bottom: 5px;"><?php echo sanitize($product['name']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if ($low_stock): ?>
                                    <p style="font-weight: 600; margin-bottom: 10px; color: #e65100;"><i class="fas fa-exclamation-triangle"></i> Low Stock (<?php echo count($low_stock); ?>)</p>
                                    <ul style="padding-left: 20px;">
                                        <?php foreach (array_slice($low_stock, 0, 5) as $product): ?>
                                            <li style="margin-bottom: 5px;"><?php echo sanitize($product['name']); ?> (<?php echo $product['stock_quantity']; ?> <?php echo sanitize($product['unit']); ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="admin-card">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Recent Activity</h3>
                            <?php if (empty($recent_activity)): ?>
                                <p style="color: var(--text-light); text-align: center; padding: 20px;">No recent activity.</p>
                            <?php else: ?>
                                <ul class="admin-activity">
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <li>
                                            <div class="admin-activity-icon" style="background: <?php echo $activity['color']; ?>;">
                                                <i class="fas <?php echo $activity['icon']; ?>"></i>
                                            </div>
                                            <div class="admin-activity-text">
                                                <strong><?php echo sanitize($activity['text']); ?></strong>
                                                <small><?php echo timeAgo($activity['time']); ?></small>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggle && sidebar && overlay) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
