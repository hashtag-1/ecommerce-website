<?php
// Seed2Greens - Admin Products Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Manage Products - Seed2Greens Admin';
$categories = getAllCategories();
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

global $db;
if ($search) {
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE ? OR p.description LIKE ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
    $products = $stmt->fetchAll();
} else {
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
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
                <h2>Products Management</h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="add-product.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Product</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <!-- Search -->
                <div class="admin-card" style="margin-bottom: 20px;">
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo $search; ?>" style="flex: 1; padding: 10px 15px; border: 1px solid var(--border); border-radius: var(--radius);">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?>
                            <a href="products.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Products Table -->
                <div class="admin-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>#<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div style="width: 50px; height: 50px; background: var(--bg-green); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    </td>
                                    <td><strong><?php echo sanitize($product['name']); ?></strong></td>
                                    <td><?php echo sanitize($product['category_name']); ?></td>
                                    <td>Rs. <?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock_quantity']; ?> <?php echo sanitize($product['unit']); ?></td>
                                    <td>
                                        <span class="status status-<?php echo ($product['status'] == 'active') ? 'delivered' : 'cancelled'; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
