<?php
// Seed2Greens - Authentication Functions

require_once __DIR__ . '/functions.php';

// ============================================
// Customer Authentication
// ============================================

function registerUser($name, $email, $phone, $password, $address = '') {
    global $db;
    
    $hashed_password = hashPassword($password);
    
    $stmt = $db->prepare("
        INSERT INTO users (name, email, phone, password, address)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([$name, $email, $phone, $hashed_password, $address]);
}

function loginUser($email, $password) {
    global $db;
    
    if (!checkLoginRateLimit('user')) {
        return 'rate_limited';
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_address'] = $user['address'];
        clearLoginAttempts('user');
        return true;
    }
    
    recordLoginAttempt('user');
    return false;
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly'], $params['samesite'] ?? '');
    }
    session_destroy();
}

// ============================================
// Admin Authentication
// ============================================

function loginAdmin($username, $password) {
    global $db;
    
    if (!checkLoginRateLimit('admin')) {
        return 'rate_limited';
    }
    
    $stmt = $db->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && verifyPassword($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_username'] = $admin['username'];
        clearLoginAttempts('admin');
        return true;
    }
    
    recordLoginAttempt('admin');
    return false;
}

function logoutAdmin() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly'], $params['samesite'] ?? '');
    }
    session_destroy();
}

// ============================================
// Cart Functions
// ============================================

function addToCart($user_id, $product_id, $quantity = 1) {
    global $db;
    
    // Check if product already in cart
    $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $new_quantity = $existing['quantity'] + $quantity;
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        return $stmt->execute([$new_quantity, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $product_id, $quantity]);
    }
}

function updateCartQuantity($user_id, $product_id, $quantity) {
    global $db;
    
    if ($quantity <= 0) {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$user_id, $product_id]);
    }
    
    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$quantity, $user_id, $product_id]);
}

function removeFromCart($user_id, $product_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

function getCartItems($user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT c.*, p.name, p.price, p.image, p.stock_quantity, p.unit,
               (c.quantity * p.price) as subtotal
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND p.status = 'active'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function removeCartItemsByProductIds($user_id, $product_ids) {
    global $db;
    if (empty($product_ids)) {
        return true;
    }
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
    return $stmt->execute(array_merge([$user_id], $product_ids));
}

function clearCart($user_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// ============================================
// Order Functions
// ============================================

function placeOrder($user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $payment_method = 'Cash on Delivery', $receipt_data = null, $receipt_mime = null, $receipt_type = null, $selected_product_ids = null) {
    global $db;
    
    $db->beginTransaction();
    
    try {
        $cart_items = getCartItems($user_id);
        if (empty($cart_items)) {
            throw new Exception('Cart is empty');
        }
        
        $valid_product_ids = null;
        if ($selected_product_ids !== null && is_array($selected_product_ids)) {
            $selected_product_ids = array_map('intval', $selected_product_ids);
            $selected_product_ids = array_unique(array_filter($selected_product_ids, function($id) { return $id > 0; }));
            
            if (empty($selected_product_ids)) {
                throw new Exception('No valid items selected for checkout');
            }
            
            $placeholders = implode(',', array_fill(0, count($selected_product_ids), '?'));
            $stmt = $db->prepare("SELECT product_id FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
            $stmt->execute(array_merge([$user_id], $selected_product_ids));
            $valid_product_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($valid_product_ids)) {
                throw new Exception('No valid cart items selected');
            }
            
            $cart_items = array_values(array_filter($cart_items, function($item) use ($valid_product_ids) {
                return in_array($item['product_id'], $valid_product_ids);
            }));
            
            if (empty($cart_items)) {
                throw new Exception('No valid cart items selected');
            }
        }
        
        $subtotal = 0;
        foreach ($cart_items as $item) {
            if ($item['quantity'] <= 0) {
                throw new Exception('Invalid cart item quantity');
            }
            $subtotal += $item['subtotal'];
        }
        
        $delivery_fee = 50.00;
        $total_amount = $subtotal + $delivery_fee;
        
        $stmt = $db->query("SHOW COLUMNS FROM orders LIKE 'receipt_data'");
        $hasReceiptColumns = (bool)$stmt->fetch();
        
        if (!$hasReceiptColumns) {
            $db->exec("ALTER TABLE orders ADD COLUMN receipt_data MEDIUMTEXT DEFAULT NULL, ADD COLUMN receipt_mime VARCHAR(100) DEFAULT NULL, ADD COLUMN receipt_type ENUM('image', 'pdf') DEFAULT NULL");
        }
        
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount, delivery_fee, payment_method, receipt_data, receipt_mime, receipt_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $total_amount, $delivery_fee, $payment_method, $receipt_data, $receipt_mime, $receipt_type]);
        
        $order_id = $db->lastInsertId();
        
        foreach ($cart_items as $item) {
            $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $stock = $stmt->fetchColumn();
            if ($stock === false || $item['quantity'] > $stock) {
                throw new Exception('Insufficient stock for: ' . $item['name']);
            }
            
            $stmt = $db->prepare("
                UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            if ($stmt->rowCount() == 0) {
                throw new Exception('Insufficient stock for: ' . $item['name']);
            }
            
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }
        
        if ($valid_product_ids !== null && !empty($valid_product_ids)) {
            removeCartItemsByProductIds($user_id, $valid_product_ids);
        } else {
            clearCart($user_id);
        }
        
        $db->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['order_debug_error'] = $e->getMessage();
        return false;
    }
}

// ============================================
// Admin Product Functions
// ============================================

function addProduct($category_id, $name, $description, $price, $stock_quantity, $unit, $image, $status = 'active') {
    global $db;
    $stmt = $db->prepare("
        INSERT INTO products (category_id, name, description, price, stock_quantity, unit, image, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$category_id, $name, $description, $price, $stock_quantity, $unit, $image, $status]);
}

function updateProduct($id, $category_id, $name, $description, $price, $stock_quantity, $unit, $image, $status) {
    global $db;
    $stmt = $db->prepare("
        UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock_quantity = ?, unit = ?, image = ?, status = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([$category_id, $name, $description, $price, $stock_quantity, $unit, $image, $status, $id]);
}

function deleteProduct($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============================================
// Admin Category Functions
// ============================================

function addCategory($name, $description, $image, $status = 'active') {
    global $db;
    $stmt = $db->prepare("
        INSERT INTO categories (name, description, image, status)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$name, $description, $image, $status]);
}

function updateCategory($id, $name, $description, $image, $status) {
    global $db;
    $stmt = $db->prepare("
        UPDATE categories SET name = ?, description = ?, image = ?, status = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([$name, $description, $image, $status, $id]);
}

function deleteCategory($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$id]);
}

