<?php
// Seed2Greens - Admin: All Orders
$page_title = 'Manage Orders - Admin';
require_once __DIR__ . '/../includes/functions.php';

// FIX: was checking isLoggedIn() / $_SESSION['user_id'] (the CUSTOMER
// session), which is why admins got bounced back to the customer
// login page while viewing orders. Admin pages must check the ADMIN
// session instead.
if (!isAdminLoggedIn()) {
    setFlashMessage('Please login to access the admin panel', 'error');
    redirect('admin/login.php');
}

// FIX: getUserOrders($user_id) scopes to one customer's orders.
// The admin order list should show ALL orders, not the orders of
// whatever $_SESSION['user_id'] happened to be lying around.
$orders = getAllOrders(); // implement in functions.php if it doesn't exist yet -- see note below
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">Manage Orders</h1>

        <?php if (empty($orders)): ?>
            <div class="empty-cart">
                <i class="fas fa-box"></i>
                <h3>No Orders Yet</h3>
            </div>
        <?php else: ?>
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
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
                                <td><?php echo sanitize($order['customer_name'] ?? ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $items_count; ?> item(s)</td>
                                <td><strong>Rs. <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo sanitize($order['status']); ?></span></td>
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