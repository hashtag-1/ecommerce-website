<?php
// Seed2Greens - About Page
$page_title = 'About Us - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <h1 class="section-title">About Seed2Greens</h1>
        <p class="section-subtitle">Your trusted agricultural marketplace</p>
        
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <div class="hero" style="padding: 60px 0; margin-bottom: 40px; border-radius: var(--radius-lg);">
                <h2 style="font-size: 36px; margin-bottom: 15px;">Our Mission</h2>
                <p style="font-size: 16px; opacity: 0.95;">To bridge the gap between local farmers and consumers by providing a reliable online marketplace for agricultural products. We believe in supporting local agriculture while delivering fresh, quality products directly to your doorstep.</p>
            </div>
            
            <div class="categories-grid" style="text-align: left;">
                <div class="category-card" style="cursor: default;">
                    <div class="category-card-image">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <div class="category-card-body">
                        <h3>Our Vision</h3>
                        <p>To become Nepal's leading agricultural e-commerce platform, empowering farmers and providing consumers with access to fresh, organic, and quality agricultural products.</p>
                    </div>
                </div>
                
                <div class="category-card" style="cursor: default;">
                    <div class="category-card-image">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="category-card-body">
                        <h3>Our Values</h3>
                        <p>Quality, transparency, sustainability, and customer satisfaction are at the core of everything we do. We source directly from trusted farmers and suppliers.</p>
                    </div>
                </div>
                
                <div class="category-card" style="cursor: default;">
                    <div class="category-card-image">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="category-card-body">
                        <h3>Our Team</h3>
                        <p>Developed by passionate students from BSc CSIT who believe in using technology to solve real-world problems in the agricultural sector.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
