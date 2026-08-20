<?php
// Seed2Greens - Admin Users Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Manage Users - Seed2Greens Admin';
$users = getAllUsers();

// Handle Delete
if (isset($_GET['delete'])) {
    if (!validateCsrfToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request. Please try again.', 'error');
        redirect('users.php');
    }
    $user_id = (int)$_GET['delete'];
    deleteUser($user_id);
    setFlashMessage('User deleted successfully', 'success');
    redirect('users.php');
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
                <li><a href="users.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><form method="POST" action="logout.php" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><button type="submit" name="logout" style="background: none; border: none; color: inherit; cursor: pointer; font-size: inherit; padding: 0; width: 100%; text-align: left;"><i class="fas fa-sign-out-alt"></i> Logout</button></form></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Registered Customers</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <form method="POST" action="logout.php" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><button type="submit" name="logout" class="btn btn-secondary btn-sm">Logout</button></form>
                </div>
            </header>
            
            <main class="admin-content">
                <div class="admin-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="6" style="text-align: center; padding: 30px;">No users found</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><strong><?php echo sanitize($user['name']); ?></strong></td>
                                        <td><?php echo sanitize($user['email']); ?></td>
                                        <td><?php echo sanitize($user['phone']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="admin-actions">
                                                <a href="customer-details.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                                <a href="users.php?delete=<?php echo $user['id']; ?>&csrf_token=<?php echo urlencode(generateCsrfToken()); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user? This will delete their cart and wishlist. Orders will be preserved for records.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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

