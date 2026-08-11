<?php
// Seed2Greens - Admin Customer Details Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('customers.php');
}

$customer_id = (int)$_GET['id'];
$customer = getCustomerById($customer_id);

if (!$customer) {
    setFlashMessage('Customer not found', 'error');
    redirect('customers.php');
}

$page_title = 'Customer Details - Seed2Greens Admin';
$stats = getCustomerOrderStats($customer_id);
$order_history = getCustomerOrderHistory($customer_id);
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
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
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
                    <h2>Customer Details</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="customers.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Customers</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <div>
                        <div class="admin-card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Customer Information</h3>
                            <div class="admin-info-row"><span class="label">Name:</span><span class="value"><?php echo sanitize($customer['name']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Email:</span><span class="value"><?php echo sanitize($customer['email']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Phone:</span><span class="value"><?php echo sanitize($customer['phone']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Address:</span><span class="value"><?php echo sanitize($customer['address'] ?? 'N/A'); ?></span></div>
                            <div class="admin-info-row"><span class="label">Joined:</span><span class="value"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></span></div>
                        </div>
                        
                        <div class="admin-card">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Statistics</h3>
                            <div class="admin-info-row"><span class="label">Total Orders:</span><span class="value"><?php echo $stats['total_orders']; ?></span></div>
                            <div class="admin-info-row total"><span class="label">Total Spent:</span><span class="value"><?php echo formatAdminCurrency($stats['total_spent']); ?></span></div>
                        </div>
                    </div>
                    
                    <div class="admin-card">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order History</h3>
                        <table class="data-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($order_history)): ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 30px;">No orders yet</td></tr>
                                <?php else: ?>
                                    <?php foreach ($order_history as $order): ?>
                                        <tr>
                                            <td><strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatAdminCurrency($order['total_amount']); ?></td>
                                            <td><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                            <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
