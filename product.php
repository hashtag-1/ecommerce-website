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
                    <?php echo ($product['stock_quantity'] > 0) ? 'In Stock (' . $product['stock_quantity'] . ' ' . sanitize($product['unit']) . ')' : 'Out of Stock'; ?>
                </div>
                
                <div class="product-detail-description">
                    <h4 style="margin-bottom: 10px;">Description</h4>
                    <p><?php echo nl2br(sanitize($product['description'])); ?></p>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
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
