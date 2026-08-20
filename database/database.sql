-- ============================================
-- Seed2Greens E-Commerce Database
-- Project: Seed2Greens
-- Technology: PHP + MySQL
-- ============================================

-- Drop database if exists (for clean setup)
DROP DATABASE IF EXISTS seed2greens;

-- Create database
CREATE DATABASE seed2greens
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE seed2greens;

-- ============================================
-- Users Table (Customers)
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- ============================================
-- Categories Table
-- ============================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);

-- ============================================
-- Products Table
-- ============================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'piece',
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_status (status)
);

-- ============================================
-- Cart Table
-- ============================================
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_cart (user_id, product_id),
    INDEX idx_user (user_id)
);

-- ============================================
-- Wishlist Table
-- ============================================
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_user (user_id)
);

-- ============================================
-- Orders Table
-- ============================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    receipt_data MEDIUMTEXT DEFAULT NULL,
    receipt_mime VARCHAR(100) DEFAULT NULL,
    receipt_type ENUM('image', 'pdf') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_date (order_date)
);

-- ============================================
-- Order Items Table
-- ============================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);

-- ============================================
-- Admin Table
-- ============================================
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    totp_secret VARCHAR(255) DEFAULT NULL,
    totp_enabled TINYINT(1) DEFAULT 0,
    backup_codes JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Insert Sample Categories
-- ============================================
INSERT INTO categories (name, description, image, status) VALUES
('Fresh Produce', 'Fresh fruits and vegetables sourced directly from local farms. 100% organic and pesticide-free.', 'fresh-produce.jpg', 'active'),
('Seeds', 'High-quality crop seeds for better yield. Premium quality seeds for home gardens and commercial farming.', 'seeds.jpg', 'active'),
('Organic Fertilizers', 'Natural and organic fertilizers to improve soil health and boost plant growth sustainably.', 'fertilizers.jpg', 'active'),
('Agriculture Tools', 'Essential farming and gardening tools for all your agricultural needs. Durable and affordable.', 'tools.jpg', 'active');

-- ============================================
-- Insert Sample Products
-- ============================================

-- Fresh Produce (category_id = 1)
INSERT INTO products (category_id, name, description, price, stock_quantity, unit, image, status) VALUES
(1, 'Fresh Tomato', 'Juicy red tomatoes, freshly harvested from organic farms. Perfect for salads, curries, and sauces.', 80.00, 100, 'kg', 'tomato.jpg', 'active'),
(1, 'Potato', 'Premium quality potatoes, ideal for all cooking needs. Fresh and clean.', 60.00, 150, 'kg', 'potato.jpg', 'active'),
(1, 'Red Apple', 'Crisp and sweet red apples. Rich in fiber and vitamins. Perfect for healthy snacking.', 180.00, 80, 'kg', 'apple.jpg', 'active'),
(1, 'Fresh Cauliflower', 'Organic cauliflower heads. Great for curries, stir-fries, and healthy meals.', 90.00, 60, 'piece', 'cauliflower.jpg', 'active'),
(1, 'Spinach (Palak)', 'Fresh green spinach leaves. Rich in iron and essential nutrients. Handpicked daily.', 40.00, 70, 'bunch', 'spinach.jpg', 'active'),
(1, 'Carrot', 'Sweet and crunchy orange carrots. Rich in beta-carotene and perfect for salads and juices.', 70.00, 90, 'kg', 'carrot.jpg', 'active'),
(1, 'Cabbage', 'Fresh green cabbage heads. Great for salads, stir-fries, and traditional Nepali dishes.', 50.00, 80, 'piece', 'cabbage.jpg', 'active');

-- Seeds (category_id = 2)
INSERT INTO products (category_id, name, description, price, stock_quantity, unit, image, status) VALUES
(2, 'Tomato Seeds', 'High-yield hybrid tomato seeds. Disease resistant and suitable for all seasons. Pack of 50 seeds.', 120.00, 200, 'packet', 'tomato-seeds.jpg', 'active'),
(2, 'Radish Seeds', 'Fast-growing radish seeds. Produces crisp, round radishes in just 25-30 days.', 60.00, 150, 'packet', 'radish-seeds.jpg', 'active'),
(2, 'Cucumber Seeds', 'Premium cucumber seeds for high yield. Long, crisp, and seedless varieties available.', 100.00, 180, 'packet', 'cucumber-seeds.jpg', 'active'),
(2, 'Spinach Seeds', 'Organic spinach seeds. High germination rate and suitable for home gardens.', 80.00, 120, 'packet', 'spinach-seeds.jpg', 'active'),
(2, 'Chili Seeds', 'Hot chili pepper seeds. Perfect for Nepali cuisine. High yield with spicy flavor.', 90.00, 140, 'packet', 'chili-seeds.jpg', 'active'),
(2, 'Brinjal Seeds', 'Hybrid brinjal (eggplant) seeds. Produces large, purple eggplants with excellent taste.', 110.00, 130, 'packet', 'brinjal-seeds.jpg', 'active');

-- Organic Fertilizers (category_id = 3)
INSERT INTO products (category_id, name, description, price, stock_quantity, unit, image, status) VALUES
(3, 'Vermicompost', 'Nutrient-rich organic vermicompost made from earthworms. Improves soil structure and plant growth. 10kg pack.', 250.00, 50, 'kg', 'vermicompost.jpg', 'active'),
(3, 'Organic Compost', '100% organic compost made from plant waste. Excellent soil conditioner for all types of plants. 15kg pack.', 300.00, 40, 'kg', 'compost.jpg', 'active'),
(3, 'Cow Manure Fertilizer', 'Pure cow manure fertilizer. Rich in organic matter and beneficial microorganisms. 20kg pack.', 200.00, 60, 'kg', 'cow-manure.jpg', 'active'),
(3, 'Neem Cake Fertilizer', 'Natural neem cake powder. Acts as both fertilizer and pesticide. 5kg pack.', 180.00, 45, 'kg', 'neem-cake.jpg', 'active'),
(3, 'Bone Meal Fertilizer', 'Organic bone meal rich in phosphorus. Promotes strong root development and flowering. 2kg pack.', 220.00, 35, 'kg', 'bone-meal.jpg', 'active');

-- Agriculture Tools (category_id = 4)
INSERT INTO products (category_id, name, description, price, stock_quantity, unit, image, status) VALUES
(4, 'Hand Trowel', 'Stainless steel hand trowel with ergonomic wooden handle. Essential for gardening and small farming tasks.', 350.00, 40, 'piece', 'hand-trowel.jpg', 'active'),
(4, 'Garden Hoe', 'Heavy-duty garden hoe with strong steel blade. Perfect for weeding and soil preparation.', 650.00, 25, 'piece', 'garden-hoe.jpg', 'active'),
(4, 'Pruning Shears', 'Sharp pruning shears for trimming plants and branches. Durable construction with safety lock.', 450.00, 30, 'piece', 'pruning-shears.jpg', 'active'),
(4, 'Watering Can', '10-liter plastic watering can with long spout. Ideal for garden and potted plants.', 550.00, 20, 'piece', 'watering-can.jpg', 'active'),
(4, 'Garden Fork', 'Strong garden fork for turning compost and loosening soil. Made from rust-proof steel.', 750.00, 15, 'piece', 'garden-fork.jpg', 'active'),
(4, 'Gardening Gloves', 'Breathable cotton gardening gloves with rubber grip. Protects hands while working in the garden.', 180.00, 50, 'pair', 'gardening-gloves.jpg', 'active');

-- ============================================
-- Insert Sample Admin User
-- Default credentials: admin / admin123
-- ============================================
INSERT INTO admin (username, password, name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin@seed2greens.com');

-- ============================================
-- Insert Sample Customer Users
-- Password for all: password
-- ============================================
INSERT INTO users (name, email, phone, password, address) VALUES
('Ramesh Sharma', 'ramesh@example.com', '9841234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kathmandu, Nepal'),
('Sita Thapa', 'sita@example.com', '9857654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pokhara, Nepal'),
('Gopal Rai', 'gopal@example.com', '9861239876', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lalitpur, Nepal');

-- ============================================
-- Insert Sample Orders
-- ============================================
INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount, delivery_fee, status, payment_method) VALUES
(1, 'Ramesh Sharma', 'ramesh@example.com', '9841234567', 'Kathmandu, Nepal', 620.00, 50.00, 'Pending', 'Cash on Delivery'),
(1, 'Ramesh Sharma', 'ramesh@example.com', '9841234567', 'Kathmandu, Nepal', 850.00, 50.00, 'Confirmed', 'Cash on Delivery'),
(2, 'Sita Thapa', 'sita@example.com', '9857654321', 'Pokhara, Nepal', 450.00, 50.00, 'Processing', 'Cash on Delivery');

-- ============================================
-- Insert Sample Order Items
-- ============================================
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 2, 80.00),
(1, 3, 1, 180.00),
(1, 8, 3, 100.00),
(2, 13, 1, 250.00),
(2, 15, 2, 200.00),
(2, 19, 1, 650.00),
(3, 5, 3, 40.00),
(3, 9, 2, 120.00);

-- ============================================
-- Insert Sample Cart Items
-- ============================================
INSERT INTO cart (user_id, product_id, quantity) VALUES
(1, 2, 3),
(1, 11, 1);

-- ============================================
-- Insert Sample Wishlist Items
-- ============================================
INSERT INTO wishlist (user_id, product_id) VALUES
(1, 4),
(1, 18),
(2, 1),
(2, 14);

-- ============================================
-- Reviews Table
-- ============================================
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rating INT NOT NULL DEFAULT 5,
    review TEXT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
);

-- ============================================
-- Site Settings Table
-- ============================================
CREATE TABLE site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('reviews_feature_mode', 'top_rated');

-- ============================================
-- Insert Sample Reviews
-- ============================================
INSERT INTO reviews (name, rating, review, status, is_featured, created_at) VALUES
('Samir Bhandari', 5, 'I ordered a few vegetable seeds for my home garden and was really impressed with the quality. The seeds arrived well packed and the growing results have been great.', 'active', 1, '2024-01-15 10:00:00'),
('Sambhab Karna', 5, 'Fresh produce at my doorstep in Kathmandu — what more could I ask for? The tomatoes and spinach were honestly the best I have had in a while.', 'active', 1, '2024-01-20 14:30:00'),
('Shital Adhikari', 4, 'Good variety of organic fertilizers and tools. Ordering was simple and delivery was on time. Would love to see more seed options in the future.', 'active', 1, '2024-02-05 09:15:00'),
('Sabita Maharjan', 5, 'The vermicompost I bought transformed my balcony garden. My plants are healthier and producing more than ever. Highly recommended for home gardeners.', 'active', 1, '2024-02-18 16:45:00'),
('Amit Rai', 5, 'Seed quality is consistently good. I have ordered multiple times and every packet has had high germination rates. Packaging is also neat and secure.', 'active', 1, '2024-03-02 11:20:00'),
('Sumila Shakya', 4, 'I appreciate the focus on organic products. The fertilizers work well and customer support was helpful when I had questions about application.', 'active', 1, '2024-03-15 13:10:00'),
('Karan Timalsina', 5, 'The gardening gloves and hand trowel I ordered are sturdy and comfortable. Great build quality for the price. Will definitely order more tools from here.', 'active', 1, '2024-04-01 08:50:00'),
('Sadish Thapa', 5, 'Fast delivery and genuine organic products. The fresh cauliflower and carrots were crisp and lasted much longer than supermarket produce.', 'active', 1, '2024-04-12 15:25:00'),
('Shovit Shrestha', 4, 'Simple ordering process and good product range. The cucumber seeds gave a nice yield. Minor suggestion: add more seasonal items during festivals.', 'active', 1, '2024-05-08 10:40:00'),
('Shobhindra Budhathoki', 5, 'As a commercial grower, I need reliable supplies. Seed 2 Greens has become my go-to for bulk seeds and fertilizers. Consistent quality every time.', 'active', 1, '2024-05-22 12:00:00');
