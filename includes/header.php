<?php
// Seed2Greens - Header
$page_title = isset($page_title) ? $page_title : 'Seed2Greens - Your Agricultural Marketplace';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Seed2Greens - Your online marketplace for fresh produce, seeds, organic fertilizers, and agricultural tools.">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Weather & Time Bar -->
    <div class="weather-bar" id="weatherBar" aria-label="Current weather and time in Kathmandu, Nepal" role="status" aria-live="polite">
        <div class="container">
            <div class="weather-content">
                <span class="weather-location"><i class="fas fa-leaf"></i> Kathmandu</span>
                <span class="weather-divider" aria-hidden="true">|</span>
                <span class="weather-condition" id="weatherCondition">
                    <i class="fas fa-spinner fa-spin" id="weatherIcon"></i>
                    <span class="weather-condition-text desktop-only" id="weatherConditionText">Loading...</span>
                </span>
                <span class="weather-temp" id="weatherTemp">--°C</span>
                <span class="weather-divider desktop-only" aria-hidden="true">|</span>
                <span class="weather-humidity"><i class="fas fa-droplet"></i> <span id="weatherHumidity">--%</span></span>
                <span class="weather-wind desktop-only"><i class="fas fa-wind"></i> <span id="weatherWind">-- km/h</span></span>
                <span class="weather-divider" aria-hidden="true">|</span>
                <span class="weather-time"><i class="fas fa-clock"></i> <span id="kathmanduTime">--:--:-- --</span></span>
            </div>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-left">
                <span><i class="fas fa-phone-alt"></i> +977-01-1234567</span>
                <span><i class="fas fa-envelope"></i> info@seed2greens.com</span>
            </div>
            <div class="top-bar-right">
                <?php if (isLoggedIn()): ?>
                    <span>Welcome, <?php echo sanitize($_SESSION['user_name']); ?></span>
                    <a href="profile.php">Profile</a>
                    <a href="orders.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar" id="navbar">
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
    </nav>

    <!-- Flash Messages -->
    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>">
            <div class="container">
                <?php echo $flash['message']; ?>
                <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main id="main-content">
