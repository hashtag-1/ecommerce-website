</main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-leaf"></i>
                        Seed<span>2</span>Greens
                    </div>
                    <p>Your trusted online marketplace for fresh produce, quality seeds, organic fertilizers, and agricultural tools. Empowering farmers and gardeners across Nepal.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <?php
                        $categories = getAllCategories();
                        foreach (array_slice($categories, 0, 4) as $category):
                        ?>
                            <li><a href="category.php?id=<?php echo $category['id']; ?>"><?php echo sanitize($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Kathmandu, Nepal</li>
                        <li><i class="fas fa-phone-alt"></i> +977-01-1234567</li>
                        <li><i class="fas fa-envelope"></i> info@seed2greens.com</li>
                        <li><i class="fas fa-clock"></i> Sun - Fri: 9:00 AM - 6:00 PM</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>Seed2Greens &copy; 2026. All Rights Reserved.</p>
                <p>Developed by: Sandesh Bhandari, Yubraj Bhandari, Yubesh Joshi, Sanskar Upaadhyaya</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
