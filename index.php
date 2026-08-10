<?php
// Seed2Greens - Homepage
$page_title = 'Seed2Greens - Your Agricultural Marketplace';
require_once __DIR__ . '/includes/functions.php';
$categories = getAllCategories();
$featured_products = getFeaturedProducts(8);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Seed2Greens</h1>
    </div>
</section>

<!-- Categories Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <p class="section-subtitle">Find exactly what you need for your farm or garden</p>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="category.php?id=<?php echo $category['id']; ?>" class="category-card">
                    <div class="category-card-image">
                        <img src="img/<?php echo getCategoryImage($category); ?>" alt="<?php echo sanitize($category['name']); ?>">
                    </div>
                    <div class="category-card-body">
                        <h3><?php echo sanitize($category['name']); ?></h3>
                        <p><?php echo sanitize(substr($category['description'], 0, 80)); ?>...</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section" style="background: white;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" style="margin-bottom: 0;">Featured Products</h2>
                <p class="section-subtitle" style="margin-bottom: 0;">Handpicked quality products for you</p>
            </div>
            <a href="products.php" class="btn btn-primary">View All Products</a>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <img src="img/<?php echo getProductImage($product); ?>" alt="<?php echo sanitize($product['name']); ?>">
                    </div>
                    <div class="product-card-body">
                        <div class="product-category"><?php echo sanitize($product['category_name']); ?></div>
                        <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo sanitize($product['name']); ?></a></h3>
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
    </div>
</section>

<!-- Why Choose Us -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Why Choose Seed2Greens?</h2>
        <p class="section-subtitle">We are committed to quality and customer satisfaction</p>
        
        <div class="categories-grid">
            <div class="category-card" style="cursor: default;">
                <div class="category-card-image">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="category-card-body">
                    <h3>100% Organic</h3>
                    <p>All our fresh produce and fertilizers are certified organic and chemical-free.</p>
                </div>
            </div>
            
            <div class="category-card" style="cursor: default;">
                <div class="category-card-image">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="category-card-body">
                    <h3>Fast Delivery</h3>
                    <p>We deliver fresh products to your doorstep within 24 hours across Nepal.</p>
                </div>
            </div>
            
            <div class="category-card" style="cursor: default;">
                <div class="category-card-image">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="category-card-body">
                    <h3>Best Prices</h3>
                    <p>Direct from farmers ensures you get the best quality at fair prices.</p>
                </div>
            </div>
            
            <div class="category-card" style="cursor: default;">
                <div class="category-card-image">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="category-card-body">
                    <h3>24/7 Support</h3>
                    <p>Our customer support team is always ready to help you with any questions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="section" style="background: var(--primary); color: white; text-align: center;">
    <div class="container">
        <h2 style="font-size: 28px; margin-bottom: 10px;">Stay Updated</h2>
        <p style="margin-bottom: 25px; opacity: 0.9;">Subscribe to our newsletter for latest offers and farming tips</p>
        <form style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; max-width: 500px; margin: 0 auto;" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
            <input type="email" placeholder="Enter your email" style="flex: 1; min-width: 250px; padding: 12px 15px; border: none; border-radius: var(--radius);" required>
            <button type="submit" class="btn" style="background: white; color: var(--primary);">Subscribe</button>
        </form>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
