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
$all_statuses = getAllStatuses();

// Handle Status Update with CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request. Please try again.', 'error');
    } else {
        $new_status = sanitize($_POST['status']);
        if (in_array($new_status, $all_statuses)) {
            if (updateOrderStatus($order_id, $new_status)) {
                setFlashMessage('Order status updated successfully', 'success');
                redirect('order-details.php?id=' . $order_id);
            } else {
                setFlashMessage('Failed to update order status', 'error');
            }
        } else {
            setFlashMessage('Invalid status value', 'error');
        }
    }
}
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
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Order Details #<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="orders.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="flash-message flash-<?php echo sanitize($_SESSION['flash_type'] ?? 'success'); ?>" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                    <div>
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
                                            <td><?php echo formatAdminCurrency($item['price']); ?></td>
                                            <td><?php echo formatAdminCurrency($item['quantity'] * $item['price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="admin-card">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Payment Details</h3>
                            <div class="admin-info-row"><span class="label">Payment Method:</span><span class="value"><?php echo sanitize($order['payment_method']); ?></span></div>
                            <?php if ($order['payment_method'] === 'eSewa' || $order['payment_method'] === 'Khalti'): ?>
                                <?php if (!empty($order['receipt_type'])): ?>
                                    <div class="admin-info-row">
                                        <span class="label">Receipt:</span>
                                        <span class="value">
                                            <?php if ($order['receipt_type'] === 'pdf'): ?>
                                                <button type="button" class="btn btn-secondary btn-sm receipt-view-btn" data-type="pdf" data-url="receipt.php?id=<?php echo $order['id']; ?>" style="cursor: pointer;">
                                                    <i class="fas fa-file-pdf"></i> View PDF Receipt
                                                </button>
                                            <?php else: ?>
                                                <img src="receipt.php?id=<?php echo $order['id']; ?>" alt="Payment Receipt" class="receipt-view-btn" data-type="image" data-url="receipt.php?id=<?php echo $order['id']; ?>" style="max-width: 200px; height: auto; border-radius: var(--radius); border: 1px solid var(--border); cursor: pointer;">
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="flash-message flash-warning" style="border-radius: 8px; margin-top: 10px; padding: 12px;">
                                        <i class="fas fa-exclamation-triangle"></i> Payment Not confirmed
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="admin-card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Customer Information</h3>
                            <div class="admin-info-row"><span class="label">Name:</span><span class="value"><?php echo sanitize($order['customer_name']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Email:</span><span class="value"><?php echo sanitize($order['customer_email']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Phone:</span><span class="value"><?php echo sanitize($order['customer_phone']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Address:</span><span class="value"><?php echo sanitize($order['customer_address']); ?></span></div>
                        </div>
                        
                        <div class="admin-card" style="margin-bottom: 20px;">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Summary</h3>
                            <div class="admin-info-row"><span class="label">Order ID:</span><span class="value">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span></div>
                            <div class="admin-info-row"><span class="label">Date:</span><span class="value"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span></div>
                            <div class="admin-info-row"><span class="label">Payment:</span><span class="value"><?php echo sanitize($order['payment_method']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Subtotal:</span><span class="value"><?php echo formatAdminCurrency($order['total_amount'] - $order['delivery_fee']); ?></span></div>
                            <div class="admin-info-row"><span class="label">Delivery:</span><span class="value"><?php echo formatAdminCurrency($order['delivery_fee']); ?></span></div>
                            <div class="admin-info-row total"><span class="label">Total:</span><span class="value"><?php echo formatAdminCurrency($order['total_amount']); ?></span></div>
                        </div>
                        
                        <div class="admin-card">
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Update Status</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <div class="admin-form-group">
                                    <select name="status" style="width: 100%; padding: 10px;" required>
                                        <?php foreach ($all_statuses as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo ($order['status'] == $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
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

    <div id="receiptModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; flex-direction: column; padding: 20px;">
        <button id="receiptModalClose" style="position: absolute; top: 15px; right: 20px; color: #fff; background: transparent; border: none; font-size: 28px; cursor: pointer; line-height: 1;">&times;</button>
        <div id="receiptModalContent" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; max-width: 90vw; max-height: 90vh;">
            <img id="receiptModalImage" src="" alt="Payment Receipt" style="max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: none;">
            <iframe id="receiptModalIframe" src="" style="width: 90vw; height: 85vh; border: none; border-radius: 8px; background: #fff; display: none;"></iframe>
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

            const modal = document.getElementById('receiptModal');
            const modalImage = document.getElementById('receiptModalImage');
            const modalIframe = document.getElementById('receiptModalIframe');
            const modalClose = document.getElementById('receiptModalClose');

            document.querySelectorAll('.receipt-view-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = this.getAttribute('data-type');
                    const url = this.getAttribute('data-url');

                    modalImage.style.display = 'none';
                    modalIframe.style.display = 'none';

                    if (type === 'pdf') {
                        modalIframe.src = url;
                        modalIframe.style.display = 'block';
                    } else {
                        modalImage.src = url;
                        modalImage.style.display = 'block';
                    }

                    modal.style.display = 'flex';
                });
            });

            function closeReceiptModal() {
                modal.style.display = 'none';
                modalIframe.src = '';
                modalImage.src = '';
            }

            modalClose.addEventListener('click', closeReceiptModal);

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeReceiptModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeReceiptModal();
                }
            });
        });
    </script>
</body>
</html>
