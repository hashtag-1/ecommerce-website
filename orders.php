<?php
// Seed2Greens - Orders Page
$page_title = 'My Orders - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    setFlashMessage('Please login to view your orders', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$orders = getUserOrders($user_id);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">My Orders</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-cart">
                <i class="fas fa-box"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping now!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $order_items = getOrderItems($order['id']);
                            $items_count = 0;
                            foreach ($order_items as $oi) {
                                $items_count += $oi['quantity'];
                            }
                        ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $items_count; ?> item(s)</td>
                                <td><strong>Rs. <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                <td>
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
