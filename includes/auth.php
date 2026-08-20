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
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_address'] = $user['address'];
        return true;
    }
    return false;
}

function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_phone']);
    unset($_SESSION['user_address']);
}

// ============================================
// Admin Authentication
// ============================================

function loginAdmin($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && verifyPassword($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }
    return false;
}

function logoutAdmin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_username']);
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

function clearCart($user_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// ============================================
// Order Functions
// ============================================

function placeOrder($user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $payment_method = 'Cash on Delivery', $receipt_data = null, $receipt_mime = null, $receipt_type = null) {
    global $db;
    
    $db->beginTransaction();
    
    try {
        $cart_items = getCartItems($user_id);
        if (empty($cart_items)) {
            throw new Exception('Cart is empty');
        }
        
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['subtotal'];
        }
        
        $delivery_fee = 50.00;
        $total_amount = $subtotal + $delivery_fee;
        
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount, delivery_fee, payment_method, receipt_data, receipt_mime, receipt_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $total_amount, $delivery_fee, $payment_method, $receipt_data, $receipt_mime, $receipt_type]);
        $order_id = $db->lastInsertId();
        
        foreach ($cart_items as $item) {
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            $stmt = $db->prepare("
                UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        clearCart($user_id);
        
        $db->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $db->rollBack();
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

