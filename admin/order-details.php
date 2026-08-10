<?php
// Seed2Greens - Admin Order Details Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['id'];
$order = getOrderById($order_id);

if (!$order) {
    setFlashMessage('Order not found', 'error');
    redirect('orders.php');
}

$page_title = 'Order #' . str_pad($order_id, 4, '0', STR_PAD_LEFT) . ' - Seed2Greens Admin';
$order_items = getOrderItems($order_id);

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);
    if (updateOrderStatus($order_id, $new_status)) {
        setFlashMessage('Order status updated successfully', 'success');
        redirect('order-details.php?id=' . $order_id);
    } else {
        setFlashMessage('Failed to update order status', 'error');
    }
}
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
                <h2>Order Details #<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="orders.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                    <!-- Order Items -->
                    <div class="admin-card">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Items</h3>
                        <table class="data-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?php echo sanitize($item['product_name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                        <td>Rs. <?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Order Info -->
                    <div>
                        <div class="admin-card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Customer Information</h3>
                            <div class="cart-summary-row"><span class="label">Name:</span><span class="value"><?php echo sanitize($order['customer_name']); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Email:</span><span class="value"><?php echo sanitize($order['customer_email']); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Phone:</span><span class="value"><?php echo sanitize($order['customer_phone']); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Address:</span><span class="value"><?php echo sanitize($order['customer_address']); ?></span></div>
                        </div>
                        
                        <div class="admin-card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Summary</h3>
                            <div class="cart-summary-row"><span class="label">Order ID:</span><span class="value">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Date:</span><span class="value"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Payment:</span><span class="value"><?php echo sanitize($order['payment_method']); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Subtotal:</span><span class="value">Rs. <?php echo number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span></div>
                            <div class="cart-summary-row"><span class="label">Delivery:</span><span class="value">Rs. <?php echo number_format($order['delivery_fee'], 2); ?></span></div>
                            <div class="cart-summary-row total"><span class="label">Total:</span><span class="value">Rs. <?php echo number_format($order['total_amount'], 2); ?></span></div>
                        </div>
                        
                        <div class="admin-card">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Update Status</h3>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <select name="status" style="width: 100%; padding: 10px;">
                                        <?php
                                        $statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                        foreach ($statuses as $status):
                                        ?>
                                            <option value="<?php echo $status; ?>" <?php echo ($order['status'] == $status) ? 'selected' : ''; ?>>
                                                <?php echo $status; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
