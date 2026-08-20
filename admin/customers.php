<?php
// Seed2Greens - Admin Customers Page
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

if (isset($_GET['delete'])) {
    if (!validateCsrfToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('Invalid security token. Please try again.', 'error');
    } else {
        $del_id = (int)$_GET['delete'];
        deleteUser($del_id);
        setFlashMessage('User deleted successfully', 'success');
    }
    redirect('customers.php');
}

$page_title = 'Manage Customers - Seed2Greens Admin';
$users = getAllUsers();
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
                <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Registered Customers</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="flash-message flash-<?php echo $_SESSION['flash_type'] ?? 'success'; ?>" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    </div>
                <?php endif; ?>

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
                                <tr><td colspan="6" style="text-align: center; padding: 30px;">No customers found</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><strong><?php echo sanitize($user['name']); ?></strong></td>
                                        <td><?php echo sanitize($user['email']); ?></td>
                                        <td><?php echo sanitize($user['phone']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <a href="customer-details.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteUser(<?php echo $user['id']; ?>)">Delete</button>
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

    <div id="deleteUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: #fff; padding: 24px; border-radius: 8px; max-width: 380px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <p id="deleteUserModalText" style="margin: 0 0 20px; font-size: 15px; color: #333;"></p>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeleteUserModal()">Cancel</button>
                <button type="button" id="deleteUserConfirmBtn" class="btn btn-danger btn-sm">Continue</button>
            </div>
        </div>
    </div>

    <script>
        const deleteUserCsrfToken = '<?php echo generateCsrfToken(); ?>';
        let deleteUserTargetId = null;
        const deleteUserModal = document.getElementById('deleteUserModal');
        const deleteUserModalText = document.getElementById('deleteUserModalText');
        const deleteUserConfirmBtn = document.getElementById('deleteUserConfirmBtn');

        function confirmDeleteUser(id) {
            deleteUserTargetId = id;
            deleteUserModalText.textContent = 'Are you sure to delete this user?';
            deleteUserConfirmBtn.textContent = 'Continue';
            deleteUserConfirmBtn.onclick = showDeleteUserWarning;
            openDeleteUserModal();
        }

        function showDeleteUserWarning() {
            deleteUserModalText.textContent = "You won't be able to recover this data once you delete.";
            deleteUserConfirmBtn.textContent = 'Confirm Delete';
            deleteUserConfirmBtn.onclick = doDeleteUser;
            openDeleteUserModal();
        }

        function doDeleteUser() {
            window.location.href = 'customers.php?delete=' + encodeURIComponent(deleteUserTargetId) + '&csrf_token=' + encodeURIComponent(deleteUserCsrfToken);
        }

        function openDeleteUserModal() {
            deleteUserModal.style.display = 'flex';
        }

        function closeDeleteUserModal() {
            deleteUserModal.style.display = 'none';
            deleteUserTargetId = null;
        }

        deleteUserModal.addEventListener('click', function(e) {
            if (e.target === deleteUserModal) {
                closeDeleteUserModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteUserModal.style.display === 'flex') {
                closeDeleteUserModal();
            }
        });
    </script>

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
