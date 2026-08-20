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
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
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
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request. Please try again.', 'error');
        redirect('categories.php');
    }
    $category_id = (int)$_POST['delete'];
    if (deleteCategory($category_id)) {
        setFlashMessage('Category deleted successfully', 'success');
        redirect('categories.php');
    } else {
        setFlashMessage('Failed to delete category', 'error');
        redirect('categories.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>
        
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-leaf"></i> Seed2Greens</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php" class="active"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><form method="POST" action="logout.php" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><button type="submit" name="logout" style="background: none; border: none; color: inherit; cursor: pointer; font-size: inherit; padding: 0; width: 100%; text-align: left;"><i class="fas fa-sign-out-alt"></i> Logout</button></form></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Categories Management</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <form method="POST" action="logout.php" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><button type="submit" name="logout" class="btn btn-secondary btn-sm">Logout</button></form>
                </div>
            </header>
            
            <main class="admin-content">
                <?php if ($error): ?>
                    <div class="flash-message flash-error" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="admin-card" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Add New Category</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="admin-grid-2">
                            <div class="admin-form-group">
                                <label for="name">Category Name *</label>
                                <input type="text" id="name" name="name" placeholder="Category name" required>
                            </div>
                            <div class="admin-form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="admin-form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Brief description"></textarea>
                        </div>
                        <div class="admin-form-group">
                            <label for="image">Image Filename</label>
                            <input type="text" id="image" name="image" placeholder="category.jpg">
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</button>
                    </form>
                </div>
                
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
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="6" style="text-align: center; padding: 30px;">No categories found</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($category['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><strong><?php echo sanitize($category['name']); ?></strong></td>
                                        <td><?php echo sanitize(substr($category['description'], 0, 60)); ?>...</td>
                                        <td>
                                            <span class="status status-<?php echo ($category['status'] == 'active') ? 'delivered' : 'cancelled'; ?>">
                                                <?php echo sanitize(ucfirst($category['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure? This will delete all products in this category.')">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="delete" value="<?php echo $category['id']; ?>">
                                                <button type="submit" name="delete_category" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggle && sidebar && overlay) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>

