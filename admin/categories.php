<?php
// Seed2Greens - Admin Categories Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Manage Categories - Seed2Greens Admin';
$categories = getAllCategories();

$error = '';
$success = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $image = sanitize($_POST['image']);
    $status = sanitize($_POST['status']);
    
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        if (addCategory($name, $description, $image, $status)) {
            setFlashMessage('Category added successfully!', 'success');
            redirect('categories.php');
        } else {
            $error = 'Failed to add category. Name may already exist.';
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    if (deleteCategory($category_id)) {
        setFlashMessage('Category deleted successfully', 'success');
        redirect('categories.php');
    } else {
        setFlashMessage('Failed to delete category', 'error');
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
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php" class="active"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <h2>Categories Management</h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <?php if ($error): ?>
                    <div class="flash-message flash-error" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Category Form -->
                <div class="admin-card" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Add New Category</h3>
                    <form method="POST" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Category Name *</label>
                            <input type="text" name="name" placeholder="Category name" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius);" required>
                        </div>
                        <div style="flex: 2; min-width: 250px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Description</label>
                            <input type="text" name="description" placeholder="Brief description" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius);">
                        </div>
                        <div style="flex: 1; min-width: 150px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
                            <select name="status" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius);">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                    </form>
                </div>
                
                <!-- Categories Table -->
                <div class="admin-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>#<?php echo str_pad($category['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><strong><?php echo sanitize($category['name']); ?></strong></td>
                                    <td><?php echo sanitize(substr($category['description'], 0, 60)); ?>...</td>
                                    <td>
                                        <span class="status status-<?php echo ($category['status'] == 'active') ? 'delivered' : 'cancelled'; ?>">
                                            <?php echo ucfirst($category['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                    <td>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? This will delete all products in this category.')">
                                            <i class="fas fa-trash"></i> Delete
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
