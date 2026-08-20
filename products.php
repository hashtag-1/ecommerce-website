<?php
// Seed2Greens - Products Page
$page_title = 'Products - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

$products = [];
if ($search || $category_id) {
    $products = searchProducts($search, $category_id);
} else {
    $products = searchProducts('');
}

$categories = getAllCategories();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 20px;">All Products</h1>
        
        <!-- Search Bar -->
        <form method="GET" action="products.php" class="search-bar">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>" id="searchInput">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search || $category_id): ?>
                <a href="products.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
        
        <!-- Results Count -->
        <p style="margin-bottom: 20px; color: var(--text-light);">
            Showing <?php echo count($products); ?> product(s)
            <?php if ($search): ?>
                for "<?php echo sanitize($search); ?>"
            <?php endif; ?>
        </p>
        
        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="empty-cart">
                <i class="fas fa-search"></i>
                <h3>No Products Found</h3>
                <p>Try adjusting your search or filter criteria</p>
                <a href="products.php" class="btn btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <img src="img/<?php echo getProductImage($product); ?>" alt="<?php echo sanitize($product['name']); ?>">
                        </div>
                        <div class="product-card-body">
                            <div class="product-category"><?php echo sanitize($product['category_name']); ?></div>
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
