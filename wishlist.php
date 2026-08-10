<?php
// Seed2Greens - Wishlist Page
$page_title = 'My Wishlist - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    setFlashMessage('Please login to view your wishlist', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle Toggle Wishlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_wishlist'])) {
    $product_id = (int)$_POST['product_id'];
    
    if (isInWishlist($user_id, $product_id)) {
        removeFromWishlist($user_id, $product_id);
        setFlashMessage('Removed from wishlist', 'success');
    } else {
        addToWishlist($user_id, $product_id);
        setFlashMessage('Added to wishlist', 'success');
    }
    redirect('wishlist.php');
}

// Handle Remove from Wishlist
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    removeFromWishlist($user_id, $product_id);
    setFlashMessage('Removed from wishlist', 'success');
    redirect('wishlist.php');
}

$wishlist_items = getWishlistItems($user_id);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">My Wishlist</h1>
        
        <?php if (empty($wishlist_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-heart"></i>
                <h3>Your Wishlist is Empty</h3>
                <p>Save your favorite products here for later</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <img src="img/<?php echo getProductImage($item); ?>" alt="<?php echo sanitize($item['name']); ?>">
                        </div>
                        <div class="product-card-body">
                            <div class="product-category"><?php echo sanitize($item['category_name'] ?? 'Product'); ?></div>
                            <h3><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo sanitize($item['name']); ?></a></h3>
                            <div class="product-price">
                                Rs. <?php echo number_format($item['price'], 2); ?>
                                <span class="unit">/ <?php echo sanitize($item['unit']); ?></span>
                            </div>
                            <div class="product-actions">
                                <form method="POST" action="cart.php" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm add-to-cart-btn">Add to Cart</button>
                                </form>
                                <a href="wishlist.php?remove=<?php echo $item['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove from wishlist?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
