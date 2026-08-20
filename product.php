<?php
// Seed2Greens - Single Product Page
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = (int)$_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    setFlashMessage('Product not found', 'error');
    redirect('products.php');
}

$page_title = $product['name'] . ' - Seed2Greens';

$in_wishlist = false;
if (isLoggedIn()) {
    $in_wishlist = isInWishlist($_SESSION['user_id'], $product_id);
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 20px; color: var(--text-light);">
            <a href="index.php" style="color: var(--primary);">Home</a> &gt;
            <a href="products.php" style="color: var(--primary);">Products</a> &gt;
            <a href="category.php?id=<?php echo $product['category_id']; ?>" style="color: var(--primary);"><?php echo sanitize($product['category_name']); ?></a> &gt;
            <span><?php echo sanitize($product['name']); ?></span>
        </nav>
        
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="img/<?php echo getProductImage($product); ?>" alt="<?php echo sanitize($product['name']); ?>">
            </div>
            
            <div class="product-detail-info">
                <div class="product-category"><?php echo sanitize($product['category_name']); ?></div>
                <h1><?php echo sanitize($product['name']); ?></h1>
                
                <div class="product-detail-price">
                    Rs. <?php echo number_format($product['price'], 2); ?>
                    <span class="unit">/ <?php echo sanitize($product['unit']); ?></span>
                </div>
                
                <div class="product-stock <?php echo ($product['stock_quantity'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                    <i class="fas fa-check-circle"></i>
                    <?php echo ($product['stock_quantity'] > 0) ? 'In Stock (' . $product['stock_quantity'] . ' ' . $product['unit'] . ')' : 'Out of Stock'; ?>
                </div>
                
                <div class="product-detail-description">
                    <h4 style="margin-bottom: 10px;">Description</h4>
                    <p><?php echo nl2br(sanitize($product['description'])); ?></p>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                    
                    <div class="product-actions-large">
                        <form method="POST" action="cart.php" class="add-to-cart-form" style="flex: 1;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg add-to-cart-btn" style="width: 100%;">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </form>
                        
                        <?php if (isLoggedIn()): ?>
                            <form method="POST" action="wishlist.php" style="flex: 1;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="toggle_wishlist" class="btn btn-secondary btn-lg" style="width: 100%;">
                                    <i class="fas fa-heart" style="color: <?php echo $in_wishlist ? '#dc3545' : ''; ?>;"></i>
                                    <?php echo $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary btn-lg" style="flex: 1;">
                                <i class="fas fa-heart"></i> Add to Wishlist
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- <script>
// Quantity controls for product detail
document.querySelector('.qty-minus')?.addEventListener('click', function() {
    const input = document.getElementById('qtyInput');
    const cartQty = document.getElementById('cartQty');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        cartQty.value = input.value;
    }
});

document.querySelector('.qty-plus')?.addEventListener('click', function() {
    const input = document.getElementById('qtyInput');
    const cartQty = document.getElementById('cartQty');
    const max = parseInt(input.getAttribute('max'));
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
        cartQty.value = input.value;
    }
});
</script> -->

<?php include __DIR__ . '/includes/footer.php'; ?>
