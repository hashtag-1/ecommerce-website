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

        <!-- FAQ Section -->
        <div class="faq-section" style="max-width: 800px; margin: 60px auto 0;">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-subtitle">Everything you need to know about Seed 2 Greens</p>

            <div class="faq-list">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>What is Seed 2 Greens?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Seed 2 Greens is an online platform dedicated to making gardening and growing easier by providing quality seeds, plants, and gardening products for people who want to create a greener lifestyle.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>What products can I find on Seed 2 Greens?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>You can explore a variety of gardening and growing products, including seeds, plants, and other products designed to support your gardening journey.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>How can I place an order?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Browse the products you like, add them to your cart, review your selected items, and proceed to checkout to complete your order.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>How do I choose the right seeds for my garden?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Consider factors such as your location, season, available space, sunlight, soil conditions, and the growing requirements of the plant. You can also check the information provided with each product.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>How should I store seeds after purchasing them?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Store seeds in a cool, dry place away from direct sunlight and moisture. Keeping them properly sealed can help maintain their quality until planting.</p>
                    </div>
                </div>

                <div class="faq-item faq-hidden">
                    <button class="faq-question" aria-expanded="false">
                        <span>How can I check my order?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>You can view your order information through the relevant order or account section of the website.</p>
                    </div>
                </div>

                <div class="faq-item faq-hidden">
                    <button class="faq-question" aria-expanded="false">
                        <span>Can I save products for later?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Yes. You can use the <strong>Wishlist</strong> feature to save products that you may want to purchase later.</p>
                    </div>
                </div>

                <div class="faq-item faq-hidden">
                    <button class="faq-question" aria-expanded="false">
                        <span>Can I grow seeds in pots or containers?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Many plants can be grown successfully in pots or containers, provided they have suitable soil, enough space, proper drainage, sunlight, and appropriate care.</p>
                    </div>
                </div>

                <div class="faq-item faq-hidden">
                    <button class="faq-question" aria-expanded="false">
                        <span>What should I do if I have a problem with my order?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>If you experience an issue with an order, contact the Seed 2 Greens support team with your order details so the issue can be reviewed and resolved.</p>
                    </div>
                </div>

                <div class="faq-item faq-hidden">
                    <button class="faq-question" aria-expanded="false">
                        <span>How can I contact Seed 2 Greens?</span>
                        <i class="fas fa-plus faq-icon"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>You can use the contact information provided on the website to reach the Seed 2 Greens team for questions, assistance, or order-related support.</p>
                    </div>
                </div>
            </div>

            <div class="faq-toggle-wrap">
                <button class="btn btn-primary btn-sm faq-toggle-btn" id="faqToggleBtn">More FAQs</button>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
