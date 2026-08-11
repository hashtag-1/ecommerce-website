<?php
// Seed2Greens - Helper Functions
session_start();
require_once __DIR__ . '/../config/database.php';

// Get database instance
$db = Database::getInstance()->getConnection();

// ============================================
// Authentication Helper Functions
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateOrderId() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

// ============================================
// Flash Message Functions
// ============================================

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// ============================================
// Cart Helper Functions
// ============================================

function getCartCount($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getCartTotal($user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT SUM(c.quantity * p.price) as total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

// ============================================
// Product Helper Functions
// ============================================

function getAllCategories() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCategoryById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductsByCategory($category_id, $limit = null) {
    global $db;
    $sql = "SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$category_id, (int)$limit]);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute([$category_id]);
    }
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function searchProducts($search_term, $category_id = null, $limit = null) {
    global $db;
    $sql = "SELECT p.*, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            AND (p.name LIKE ? OR p.description LIKE ?)";
    $params = ["%$search_term%", "%$search_term%"];
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = (int)$limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getFeaturedProducts($limit = 8) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

// ============================================
// Wishlist Helper Functions
// ============================================

function getWishlistItems($user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT w.*, p.name, p.price, p.image, p.stock_quantity, p.unit
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ? AND p.status = 'active'
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function isInWishlist($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch() ? true : false;
}

function addToWishlist($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    return $stmt->execute([$user_id, $product_id]);
}

function removeFromWishlist($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

// ============================================
// Order Helper Functions
// ============================================

function getOrderById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getOrderItems($order_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT oi.*, p.name as product_name, p.image as product_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

function getUserOrders($user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getOrderCountByStatus($status) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = ?");
    $stmt->execute([$status]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getProductImage($product) {
    $name = $product['name'] ?? $product['product_name'] ?? '';
    $map = [
        'Fresh Tomato' => 'fresh-tomato.jpg',
        'Potato' => 'potato.jpg',
        'Red Apple' => 'red-apple.jpg',
        'Fresh Cauliflower' => 'fresh-cauliflower.jpg',
        'Spinach (Palak)' => 'spinach.jpg',
        'Carrot' => 'carrot.jpg',
        'Cabbage' => 'cabbage.jpg',
        'Tomato Seeds' => 'tomato seed.webp',
        'Radish Seeds' => 'radish-seed.jpg',
        'Cucumber Seeds' => 'cucumber-seed.jpg',
        'Spinach Seeds' => 'spinach-seed.jpg',
        'Chili Seeds' => 'chilli-seed.jpg',
        'Brinjal Seeds' => 'brinjal-seed.jpg',
        'Vermicompost' => 'vermicompost.jpg',
        'Organic Compost' => 'organic-compost.jpg',
        'Cow Manure Fertilizer' => 'cow-manure.webp',
        'Neem Cake Fertilizer' => 'neem-cake-fertilizer.jpg',
        'Bone Meal Fertilizer' => 'bone-meal-fertilizer.jpg',
        'Hand Trowel' => 'hand-trowel.jpg',
        'Garden Hoe' => 'garden-hoe.jpg',
        'Pruning Shears' => 'pruning-shear.jpg',
        'Watering Can' => 'watering-can.jpg',
        'Garden Fork' => 'garden-fork.jpg',
        'Gardening Gloves' => 'gardening-gloves.jpg',
    ];

    return $map[$name] ?? ($product['image'] ?? $product['product_image'] ?? '');
}

function getCategoryImage($category) {
    $map = [
        'Fresh Produce' => 'Fresh-produce.jpeg',
        'Seeds' => 'seeds.jpeg',
        'Organic Fertilizers' => 'Organic-fertilizers.jpeg',
        'Agriculture Tools' => 'Agriculture-tools.jpeg',
    ];

    return $map[$category['name']] ?? '';
}

// ============================================
// Admin Helper Functions
// ============================================

function getTotalProducts() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getTotalUsers() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getTotalOrders() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function getTotalSales() {
    global $db;
    $stmt = $db->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getAllUsers() {
    global $db;
    $stmt = $db->prepare("SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function deleteUser($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

function getAllOrdersForAdmin() {
    global $db;
    $stmt = $db->prepare("
        SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function updateOrderStatus($order_id, $status) {
    global $db;
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $order_id]);
}

// ============================================
// CSRF Protection
// ============================================

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// Admin Dashboard Helpers
// ============================================

function getAdminStats() {
    global $db;
    $stats = [];

    $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch()['count'];

    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $stats['total_customers'] = $stmt->fetch()['count'];

    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $stats['total_products'] = $stmt->fetch()['count'];

    $stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'");
    $stats['completed_revenue'] = $stmt->fetch()['total'] ?? 0;

    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
    $stats['pending_orders'] = $stmt->fetch()['count'];

    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Processing'");
    $stats['processing_orders'] = $stmt->fetch()['count'];

    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Delivered'");
    $stats['delivered_orders'] = $stmt->fetch()['count'];

    return $stats;
}

function getRecentOrders($limit = 5) {
    global $db;
    $stmt = $db->prepare("
        SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

function getOrders($search = '', $status = '', $page = 1, $limit = 10) {
    global $db;
    $offset = ($page - 1) * $limit;
    $params = [];
    $where = [];

    if ($search) {
        $where[] = "(o.id LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($status) {
        $where[] = "o.status = ?";
        $params[] = $status;
    }

    $sql = "
        SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
    ";

    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getOrdersCount($search = '', $status = '') {
    global $db;
    $params = [];
    $where = [];

    if ($search) {
        $where[] = "(o.id LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($status) {
        $where[] = "o.status = ?";
        $params[] = $status;
    }

    $sql = "SELECT COUNT(*) as count FROM orders o";
    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch()['count'];
}

function getCustomerById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCustomerOrderStats($user_id) {
    global $db;
    $stats = [];

    $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['total_orders'] = $stmt->fetch()['count'];

    $stmt = $db->prepare("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND status != 'Cancelled'");
    $stmt->execute([$user_id]);
    $stats['total_spent'] = $stmt->fetch()['total'] ?? 0;

    return $stats;
}

function getCustomerOrderHistory($user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getLowStockProducts($threshold = 10) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.stock_quantity <= ? AND p.status = 'active'
        ORDER BY p.stock_quantity ASC
    ");
    $stmt->execute([(int)$threshold]);
    return $stmt->fetchAll();
}

function getOutOfStockProducts() {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.stock_quantity = 0 AND p.status = 'active'
        ORDER BY p.name ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getRecentActivity($limit = 10) {
    global $db;
    $activities = [];

    $stmt = $db->prepare("
        SELECT id, customer_name, created_at, 'New order' as activity
        FROM orders ORDER BY created_at DESC LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    $orders = $stmt->fetchAll();

    foreach ($orders as $order) {
        $activities[] = [
            'text' => 'New order #' . str_pad($order['id'], 4, '0', STR_PAD_LEFT) . ' placed by ' . $order['customer_name'],
            'time' => $order['created_at'],
            'icon' => 'fa-shopping-bag',
            'color' => '#2e7d32'
        ];
    }

    $stmt = $db->prepare("
        SELECT id, name, created_at FROM users ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();

    foreach ($users as $user) {
        $activities[] = [
            'text' => 'New customer registered: ' . $user['name'],
            'time' => $user['created_at'],
            'icon' => 'fa-user-plus',
            'color' => '#1976d2'
        ];
    }

    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    return array_slice($activities, 0, $limit);
}

function getAllStatuses() {
    return ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
}

function formatAdminCurrency($amount) {
    return 'Rs. ' . number_format($amount, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time / 60) . ' min ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    return date('M d, Y', strtotime($datetime));
}
