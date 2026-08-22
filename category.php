<?php
// Seed2Greens - Category Page
$page_title = 'Category - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$category_id = (int)$_GET['id'];
$category = getCategoryById($category_id);

if (!$category) {
    setFlashMessage('Category not found', 'error');
    redirect('products.php');
}

$page_title = $category['name'] . ' - Seed2Greens';
$products = getProductsByCategory($category_id);
$categories = getAllCategories();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 20px; color: var(--text-light);">
            <a href="index.php" style="color: var(--primary);">Home</a> &gt;
            <a href="products.php" style="color: var(--primary);">Products</a> &gt;
            <span><?php echo sanitize($category['name']); ?></span>
        </nav>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 class="section-title" style="text-align: left; margin-bottom: 5px;"><?php echo sanitize($category['name']); ?></h1>
                <p style="color: var(--text-light);"><?php echo sanitize($category['description']); ?></p>
            </div>
            <a href="products.php" class="btn btn-secondary">All Categories</a>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-cart">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>This category doesn't have any products yet</p>
                <a href="products.php" class="btn btn-primary">Browse Other Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <?php if (isLoggedIn()): ?>
                                <?php $in_wishlist = isInWishlist($_SESSION['user_id'], $product['id']); ?>
                                <form method="POST" action="wishlist.php" class="wishlist-toggle-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="redirect_to" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                                    <button type="submit" name="toggle_wishlist" class="wishlist-heart-btn" title="<?php echo $in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                        <?php echo $in_wishlist ? '❤️' : '♡'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="wishlist-heart-btn" title="Login to add to wishlist" style="text-decoration: none;">♡</a>
                            <?php endif; ?>
                            <img src="img/<?php echo getProductImage($product); ?>" alt="<?php echo sanitize($product['name']); ?>">
                        </div>
                        <div class="product-card-body">
                            <div class="product-category"><?php echo sanitize($category['name']); ?></div>
                            <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo sanitize($product['name']); ?></a></h3>
                            <p style="font-size: 14px; color: var(--text-light); margin-bottom: 10px;">
                                <?php echo sanitize(substr($product['description'], 0, 80)); ?>...
                            </p>
                            <div class="product-price">
                                Rs. <?php echo number_format($product['price'], 2); ?>
                                <span class="unit">/ <?php echo sanitize($product['unit']); ?></span>
                            </div>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                                <?php if (isLoggedIn()): ?>
                                    <form method="POST" action="cart.php" class="add-to-cart-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm add-to-cart-btn">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary btn-sm">Add to Cart</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
