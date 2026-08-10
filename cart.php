<?php
// Seed2Greens - Shopping Cart
$page_title = 'Shopping Cart - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    setFlashMessage('Please login to view your cart', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    $product = getProductById($product_id);
    if ($product && $product['stock_quantity'] >= $quantity) {
        addToCart($user_id, $product_id, $quantity);
        setFlashMessage('Product added to cart!', 'success');
    } else {
        setFlashMessage('Insufficient stock available', 'error');
    }
    redirect('cart.php');
}

// Handle Update Quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    updateCartQuantity($user_id, $product_id, $quantity);
    setFlashMessage('Cart updated', 'success');
    redirect('cart.php');
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    removeFromCart($user_id, $product_id);
    setFlashMessage('Product removed from cart', 'success');
    redirect('cart.php');
}

$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);
$delivery_fee = $cart_total > 0 ? 50.00 : 0.00;
$grand_total = $cart_total + $delivery_fee;
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your Cart is Empty</h3>
                <p>Looks like you haven't added any products yet</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <div>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="cart-product">
                                            <div class="cart-product-image">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo sanitize($item['name']); ?></strong>
                                                <br><small style="color: var(--text-light);"><?php echo sanitize($item['unit']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" action="" class="cart-qty-form">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <div class="quantity-control">
                                                <button type="button" class="qty-minus-btn" data-action="decrease">-</button>
                                                <input type="number" name="quantity" class="cart-quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" style="width: 50px; text-align: center; border: 1px solid var(--border); border-radius: 0; height: 36px;">
                                                <button type="button" class="qty-plus-btn" data-action="increase">+</button>
                                            </div>
                                            <input type="hidden" name="update_cart" value="1">
                                        </form>
                                    </td>
                                    <td><strong>Rs. <?php echo number_format($item['subtotal'], 2); ?></strong></td>
                                    <td>
                                        <a href="cart.php?remove=<?php echo $item['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item from cart?')" style="padding: 6px 12px;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div>
                    <div class="cart-summary">
                        <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Order Summary</h3>
                        
                        <div class="cart-summary-row">
                            <span class="label">Subtotal</span>
                            <span class="value">Rs. <?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="label">Delivery Fee</span>
                            <span class="value">Rs. <?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <div class="cart-summary-row total">
                            <span class="label">Total</span>
                            <span class="value">Rs. <?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 20px; display: block; text-align: center;">
                            Proceed to Checkout
                        </a>
                        <a href="products.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px; display: block; text-align: center;">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
