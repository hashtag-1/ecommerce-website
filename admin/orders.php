<?php
// Seed2Greens - Admin Orders Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Manage Orders - Seed2Greens Admin';
$orders = getAllOrdersForAdmin();
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
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-leaf"></i> Seed2Greens</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <h2>Orders Management</h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <div class="admin-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="7" style="text-align: center; padding: 30px;">No orders found</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td>
                                            <?php echo sanitize($order['customer_name']); ?><br>
                                            <small style="color: var(--text-light);"><?php echo sanitize($order['customer_email']); ?></small>
                                        </td>
                                        <td><?php echo sanitize($order['customer_phone']); ?></td>
                                        <td><strong>Rs. <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <form method="POST" action="order-details.php?id=<?php echo $order['id']; ?>" style="display: inline;">
                                                <select name="status" onchange="this.form.submit()" style="padding: 5px 10px; border-radius: 4px; border: 1px solid var(--border);">
                                                    <?php
                                                    $statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                                    foreach ($statuses as $status):
                                                    ?>
                                                        <option value="<?php echo $status; ?>" <?php echo ($order['status'] == $status) ? 'selected' : ''; ?>>
                                                            <?php echo $status; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
