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
$delivery_fee = 50.00;

if (empty($cart_items)) {
    setFlashMessage('Your cart is empty', 'error');
    redirect('cart.php');
}

$selected_items = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_items'])) {
    $selected_items = array_map('intval', $_POST['selected_items']);
    $selected_items = array_unique(array_filter($selected_items, function($id) { return $id > 0; }));
    
    if (empty($selected_items)) {
        setFlashMessage('Please select at least one item to checkout.', 'error');
        redirect('cart.php');
    }
    
    $cart_items = array_values(array_filter($cart_items, function($item) use ($selected_items) {
        return in_array($item['product_id'], $selected_items);
    }));
    
    if (empty($cart_items)) {
        setFlashMessage('No valid items selected for checkout.', 'error');
        redirect('cart.php');
    }
}

$cart_total = 0;
foreach ($cart_items as $item) {
    $cart_total += $item['subtotal'];
}
$grand_total = $cart_total + $delivery_fee;

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
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
        $receipt_data = null;
        $receipt_mime = null;
        $receipt_type = null;
        
        if ($payment_method === 'eSewa' || $payment_method === 'Khalti') {
            if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['receipt_file'];
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                $max_size = 5 * 1024 * 1024;
                
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->file($file['tmp_name']);
                
                if (!in_array($mime_type, $allowed_types)) {
                    $error = 'Invalid file type. Only JPG, PNG, and PDF are allowed.';
                } elseif ($file['size'] > $max_size) {
                    $error = 'File size exceeds 5MB limit.';
                } else {
                    $content = file_get_contents($file['tmp_name']);
                    if ($content === false) {
                        $error = 'Failed to read uploaded file.';
                    } else {
                        $receipt_mime = $mime_type;
                        $receipt_type = ($mime_type === 'application/pdf') ? 'pdf' : 'image';
                        $receipt_data = base64_encode($content);
                    }
                }
            }
        }
        
        if (empty($error)) {
            $order_id = placeOrder($user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $payment_method, $receipt_data, $receipt_mime, $receipt_type, !empty($selected_items) ? $selected_items : null);
            if ($order_id) {
                setFlashMessage('Order placed successfully! Order ID: ORD' . date('Ymd') . str_pad($order_id, 4, '0', STR_PAD_LEFT), 'success');
                redirect('orders.php');
            } else {
                $error = 'Failed to place order. Please try again.';
            }
        }
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
                <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['order_debug_error'])): ?>
            <div class="flash-message flash-error" style="border-radius: var(--radius); margin-bottom: 20px; padding: 12px;">
                <strong>Debug:</strong> <?php echo sanitize($_SESSION['order_debug_error']); unset($_SESSION['order_debug_error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <?php if (!empty($selected_items)): ?>
                <?php foreach ($selected_items as $pid): ?>
                    <input type="hidden" name="selected_items[]" value="<?php echo (int)$pid; ?>">
                <?php endforeach; ?>
            <?php endif; ?>
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
                                <option value="eSewa" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'eSewa') ? 'selected' : ''; ?>>eSewa</option>
                                <option value="Khalti" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Khalti') ? 'selected' : ''; ?>>Khalti</option>
                            </select>
                            
                            <div id="payment-qr-container" style="margin-top: 15px; display: none;">
                                <img id="esewa-qr" src="img/esewa.png" alt="eSewa QR" style="display: none; max-width: 200px; height: auto; border-radius: var(--radius); border: 1px solid var(--border); cursor: pointer;">
                                <img id="khalti-qr" src="img/khalti.png" alt="Khalti QR" style="display: none; max-width: 200px; height: auto; border-radius: var(--radius); border: 1px solid var(--border); cursor: pointer;">
                                
                                <div id="receipt-upload-container" style="margin-top: 15px; display: none;">
                                    <label for="receipt_file" style="display: block; margin-bottom: 5px; font-weight: 500;">Upload Receipt (JPG, PNG, PDF)</label>
                                    <input type="file" id="receipt_file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" style="padding: 8px; border: 1px solid var(--border); border-radius: var(--radius); width: 100%;">
                                    <small style="color: var(--text-light);">Max size: 5MB. Accepted: JPG, JPEG, PNG, PDF</small>
                                </div>
                            </div>
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
                                    <img src="img/<?php echo getProductImage($item); ?>" alt="<?php echo sanitize($item['name']); ?>">
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
        
        <div id="qr-lightbox" style="display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.92); align-items: center; justify-content: center; flex-direction: column; padding: 20px;">
            <img id="qr-lightbox-img" src="" alt="Payment QR" style="max-width: 90vw; max-height: 70vh; width: auto; height: auto; object-fit: contain; border-radius: 8px;">
            <div style="margin-top: 20px; display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                <button type="button" id="qr-lightbox-download" style="padding: 12px 24px; border: none; border-radius: 8px; background: var(--primary); color: #fff; font-size: 16px; font-weight: 600; cursor: pointer;">Download QR</button>
                <button type="button" id="qr-lightbox-close" style="padding: 12px 24px; border: none; border-radius: 8px; background: rgba(255,255,255,0.15); color: #fff; font-size: 16px; font-weight: 600; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
