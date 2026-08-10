<?php
// Seed2Greens - Admin Add Product
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Add Product - Seed2Greens Admin';
$categories = getAllCategories();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $category_id = (int)$_POST['category_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $unit = sanitize($_POST['unit']);
    $image = sanitize($_POST['image']);
    $status = sanitize($_POST['status']);
    
    if (empty($category_id) || empty($name) || empty($price) || empty($stock_quantity)) {
        $error = 'Please fill in all required fields';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0';
    } elseif ($stock_quantity < 0) {
        $error = 'Stock quantity cannot be negative';
    } else {
        if (addProduct($category_id, $name, $description, $price, $stock_quantity, $unit, $image, $status)) {
            setFlashMessage('Product added successfully!', 'success');
            redirect('products.php');
        } else {
            $error = 'Failed to add product. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-leaf"></i> Seed2Greens</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <h2>Add New Product</h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="products.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Products</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <div class="admin-card" style="max-width: 800px;">
                    <?php if ($error): ?>
                        <div class="flash-message flash-error" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Product Name *</label>
                                <input type="text" id="name" name="name" placeholder="Enter product name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price (Rs.) *</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0" placeholder="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <input type="text" id="unit" name="unit" value="piece" placeholder="kg, piece, packet">
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Image Filename</label>
                                <input type="text" id="image" name="image" placeholder="product.jpg">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Enter product description"></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
