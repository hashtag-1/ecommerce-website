<?php
// Seed2Greens - Admin: Order Details
$page_title = 'Order Details - Admin';
define('PROJECT_ROOT', __DIR__);
$functionsPath = PROJECT_ROOT . '/includes/functions.php';
if (!file_exists($functionsPath)) {
    die('Error: Application configuration file missing. Please contact support. Error code: CFG001');
}
require_once $functionsPath;

// FIX: was checking isLoggedIn() (customer session) — same bug as orders.php.
if (!isAdminLoggedIn()) {
    setFlashMessage('Please login to access the admin panel', 'error');
    redirect('admin/login.php');
}

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['id'];
$order = getOrderById($order_id);

// FIX: was comparing $order['user_id'] != $_SESSION['user_id'] -- that check
// makes sense for a CUSTOMER viewing their own order, not for an admin who
// needs to view any customer's order. Admin just needs the order to exist.
if (!$order) {
    setFlashMessage('Order not found', 'error');
    redirect('orders.php');
}

$page_title = 'Order #' . str_pad($order_id, 4, '0', STR_PAD_LEFT) . ' - Admin';
$order_items = getOrderItems($order_id);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
            <h1 class="section-title" style="margin-bottom: 0; text-align: left;">Order Details</h1>
            <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div>
                <div class="cart-summary" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Items</h3>

                    <?php foreach ($order_items as $item): ?>
                        <div class="checkout-item">
                            <div class="checkout-item-image">
                                <img src="img/<?php echo getProductImage($item); ?>" alt="<?php echo sanitize($item['product_name']); ?>">
                            </div>
                            <div class="checkout-item-info" style="flex: 1;">
                                <h4><?php echo sanitize($item['product_name']); ?></h4>
                                <span><?php echo $item['quantity']; ?> x Rs. <?php echo number_format($item['price'], 2); ?></span>
                            </div>
                            <div style="font-weight: 600;">
                                Rs. <?php echo number_format($item['quantity'] * $item['price'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="cart-summary" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Information</h3>

                    <div class="cart-summary-row">
                        <span class="label">Order ID</span>
                        <span class="value">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Date</span>
                        <span class="value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Status</span>
                        <span class="value"><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo sanitize($order['status']); ?></span></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Payment</span>
                        <span class="value"><?php echo sanitize($order['payment_method']); ?></span>
                    </div>
                </div>

                <div class="cart-summary" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Shipping Address</h3>

                    <div class="cart-summary-row">
                        <span class="label">Name</span>
                        <span class="value"><?php echo sanitize($order['customer_name']); ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo sanitize($order['customer_phone']); ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Address</span>
                        <span class="value"><?php echo sanitize($order['customer_address']); ?></span>
                    </div>
                </div>

                <div class="cart-summary">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Payment Summary</h3>

                    <div class="cart-summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value">Rs. <?php echo number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Delivery Fee</span>
                        <span class="value">Rs. <?php echo number_format($order['delivery_fee'], 2); ?></span>
                    </div>
                    <div class="cart-summary-row total">
                        <span class="label">Total</span>
                        <span class="value">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>