</main>

<!-- Footer -->
<footer class="site-footer">
    <!-- Compact CTA Row -->
    <div class="footer-cta-row">
        <div class="container">
            <div class="cta-grid">
                <div class="cta-col">
                    <h4>Stay Updated</h4>
                    <p>Get the latest updates, products, and growing tips.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault();">
                        <div class="newsletter-input-wrap">
                            <input type="email" placeholder="Enter your email" required>
                            <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
                        </div>
                    </form>
                </div>
                <div class="cta-col cta-col-right">
                    <h4>Grow Better. Live Greener.</h4>
                    <p>Everything you need to grow with confidence.</p>
                    <a href="products.php" class="btn btn-primary btn-sm">Explore Products</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Footer Content -->
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-col footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-leaf"></i>
                        Seed<span>2</span>Greens
                    </div>
                    <p class="footer-tagline">Growing a greener future, one seed at a time.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="category.php">Categories</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Shop Categories -->
                <div class="footer-col">
                    <h4>Shop Categories</h4>
                    <ul class="footer-links">
                        <li><a href="category.php?id=4"><i class="fas fa-tools"></i> Agriculture Tools</a></li>
                        <li><a href="category.php?id=1"><i class="fas fa-leaf"></i> Fresh Produce</a></li>
                        <li><a href="category.php?id=3"><i class="fas fa-seedling"></i> Organic Fertilizers</a></li>
                        <li><a href="category.php?id=2"><i class="fas fa-spa"></i> Seeds</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-col">
                    <h4>Get in Touch</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Nepal</li>
                        <li><a href="tel:+977-01-1234567"><i class="fas fa-phone-alt"></i> +977-01-1234567</a></li>
                        <li><a href="mailto:info@seed2greens.com"><i class="fas fa-envelope"></i> info@seed2greens.com</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Developers Section -->
    <div class="footer-developers">
        <div class="container">
            <div class="developers-header">
                <h4>Meet the Developers</h4>
            </div>
            <div class="developers-grid">
                <!-- Developer 1 -->
                <div class="developer-card">
                    <div class="dev-avatar">
                        <img src="img/sanskar-sharma.jpeg" alt="Sanskar Sharma">
                    </div>
                    <h5>Sanskar Sharma</h5>
                    <a href="https://www.facebook.com/sanskar.sharma.639786" target="_blank" rel="noopener noreferrer" class="btn btn-facebook btn-xs" aria-label="Connect with Sanskar Sharma on Facebook">
                        <i class="fab fa-facebook-f"></i> Connect
                    </a>
                </div>

                <!-- Developer 2 -->
                <div class="developer-card">
                    <div class="dev-avatar">
                        <img src="img/yubesh-joshi.jpeg" alt="Yubesh Joshi">
                    </div>
                    <h5>Yubesh Joshi</h5>
                    <a href="https://www.facebook.com/yubesh.joshi" target="_blank" rel="noopener noreferrer" class="btn btn-facebook btn-xs" aria-label="Connect with Yubesh Joshi on Facebook">
                        <i class="fab fa-facebook-f"></i> Connect
                    </a>
                </div>

                <!-- Developer 3 - Sandesh Bhandari -->
                <div class="developer-card" id="sandeshCard">
                    <div class="dev-avatar">
                        <img src="img/sandesh-bhandari.jpeg" alt="Sandesh Bhandari">
                    </div>
                    <h5>Sandesh Bhandari</h5>
                    <button class="btn btn-surprise btn-xs" id="surpriseBtn" aria-label="Contact Sandesh Bhandari">SURPRISE</button>
                </div>

                <!-- Developer 4 -->
                <div class="developer-card">
                    <div class="dev-avatar">
                        <img src="img/yubraj-bhandari.jpeg" alt="Yubraj Bhandari">
                    </div>
                    <h5>Yubraj Bhandari</h5>
                    <a href="https://www.facebook.com/yubraj.bhandari.39794" target="_blank" rel="noopener noreferrer" class="btn btn-facebook btn-xs" aria-label="Connect with Yubraj Bhandari on Facebook">
                        <i class="fab fa-facebook-f"></i> Connect
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stay Updated -->
    <div class="footer-stay-updated">
        <div class="container">
            <form class="stay-updated-row" onsubmit="event.preventDefault();">
                <span class="stay-text">Stay Updated</span>
                <input type="email" placeholder="Enter your email" required class="stay-input">
                <button type="submit" class="btn btn-primary btn-xs">Subscribe</button>
            </form>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <p>&copy; 2026 Seed 2 Greens. All rights reserved.</p>
                <p class="footer-made">Made with 🌱 for a greener future.</p>
            </div>
        </div>
    </div>

    <!-- SURPRISE Light Overlay -->
    <div class="surprise-overlay" id="surpriseOverlay" aria-hidden="true">
        <div class="surprise-glow"></div>
        <div class="surprise-particles" id="surpriseParticles"></div>
        <div class="surprise-streaks">
            <span class="streak"></span>
            <span class="streak"></span>
            <span class="streak"></span>
            <span class="streak"></span>
            <span class="streak"></span>
            <span class="streak"></span>
            <span class="streak"></span>
            <span class="streak"></span>
        </div>
        <div class="surprise-bursts">
            <span class="burst"></span>
            <span class="burst"></span>
            <span class="burst"></span>
            <span class="burst"></span>
            <span class="burst"></span>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>