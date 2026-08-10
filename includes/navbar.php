<?php
// Seed2Greens - Navigation Bar (Standalone)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="navbar" id="navbar">
    <div class="container">
        <a href="index.php" class="logo">
            <i class="fas fa-leaf"></i>
            Seed<span>2</span>Greens
        </a>

        <ul class="nav-menu" id="navMenu">
            <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="products.php" class="<?php echo ($current_page == 'products.php') ? 'active' : ''; ?>">Products</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Categories <i class="fas fa-chevron-down"></i></a>
                <ul class="dropdown-menu">
                    <?php
                    $categories = getAllCategories();
                    foreach ($categories as $category):
                    ?>
                        <li><a href="category.php?id=<?php echo $category['id']; ?>"><?php echo sanitize($category['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li><a href="about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
        </ul>

        <div class="nav-actions">
            <a href="wishlist.php" class="nav-icon">
                <i class="fas fa-heart"></i>
                <span class="badge"><?php echo (isLoggedIn()) ? count(getWishlistItems($_SESSION['user_id'])) : 0; ?></span>
            </a>
            <a href="cart.php" class="nav-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="badge"><?php echo (isLoggedIn()) ? getCartCount($_SESSION['user_id']) : 0; ?></span>
            </a>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</div>
