<?php
// Seed2Greens - Checkout Page
$page_title = 'Checkout - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    setFlashMessage('Please login to checkout', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);

if (empty($cart_items)) {
    setFlashMessage('Your cart is empty', 'error');
    redirect('cart.php');
}

$cart_total = getCartTotal($user_id);
$delivery_fee = 50.00;
$grand_total = $cart_total + $delivery_fee;

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $customer_name = sanitize($_POST['customer_name']);
    $customer_email = sanitize($_POST['customer_email']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $customer_address = sanitize($_POST['customer_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($customer_address)) {
        $error = 'Please fill in all fields';
    } elseif (!validateEmail($customer_email)) {
        $error = 'Please enter a valid email address';
    } elseif (!validatePhone($customer_phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } else {
        $order_id = placeOrder($user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $payment_method);
        if ($order_id) {
            setFlashMessage('Order placed successfully! Order ID: ORD' . date('Ymd') . str_pad($order_id, 4, '0', STR_PAD_LEFT), 'success');
            redirect('orders.php');
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">Checkout</h1>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="border-radius: var(--radius); margin-bottom: 20px; padding: 12px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="checkout-grid">
                <div>
                    <!-- Customer Information -->
                    <div class="checkout-form-section" style="margin-bottom: 20px;">
                        <h3 class="checkout-section-title">Customer Information</h3>
                        
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" id="customer_name" name="customer_name" value="<?php echo isset($_POST['customer_name']) ? sanitize($_POST['customer_name']) : ($_SESSION['user_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_email">Email Address *</label>
                            <input type="email" id="customer_email" name="customer_email" value="<?php echo isset($_POST['customer_email']) ? sanitize($_POST['customer_email']) : ($_SESSION['user_email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone">Phone Number *</label>
                            <input type="tel" id="customer_phone" name="customer_phone" value="<?php echo isset($_POST['customer_phone']) ? sanitize($_POST['customer_phone']) : ($_SESSION['user_phone'] ?? ''); ?>" placeholder="10-digit number" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_address">Delivery Address *</label>
                            <textarea id="customer_address" name="customer_address" placeholder="Enter your full delivery address" required><?php echo isset($_POST['customer_address']) ? sanitize($_POST['customer_address']) : ($_SESSION['user_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-form-section">
                        <h3 class="checkout-section-title">Payment Method</h3>
                        
                        <div class="form-group">
                            <label for="payment_method">Select Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="Cash on Delivery" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] == 'Cash on Delivery') ? 'selected' : ''; ?>>Cash on Delivery</option>
                                <option value="eSewa" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'eSewa') ? 'selected' : ''; ?>>eSewa (Coming Soon)</option>
                                <option value="Khalti" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Khalti') ? 'selected' : ''; ?>>Khalti (Coming Soon)</option>
                            </select>
                            <small style="color: var(--text-light);">Only Cash on Delivery is active currently</small>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div>
                    <div class="checkout-summary">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Summary</h3>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <div class="checkout-item">
                                <div class="checkout-item-image">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="checkout-item-info" style="flex: 1;">
                                    <h4><?php echo sanitize($item['name']); ?></h4>
                                    <span><?php echo $item['quantity']; ?> x Rs. <?php echo number_format($item['price'], 2); ?></span>
                                </div>
                                <div style="font-weight: 600;">
                                    Rs. <?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-summary-row" style="margin-top: 20px; border-top: 2px solid var(--primary); padding-top: 15px;">
                            <span class="label">Subtotal</span>
                            <span class="value">Rs. <?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="label">Delivery Fee</span>
                            <span class="value">Rs. <?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <div class="cart-summary-row total">
                            <span class="label">Total Amount</span>
                            <span class="value">Rs. <?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 20px;">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                        
                        <p style="text-align: center; margin-top: 15px; font-size: 13px; color: var(--text-light);">
                            <i class="fas fa-shield-alt"></i> Secure checkout
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
