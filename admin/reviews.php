<?php
// Seed2Greens - Admin Reviews Management
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$currentMode = getFeaturedMode();
$allReviews = getAllReviews();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_review'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid security token. Please try again.', 'error');
    } else {
        $del_id = (int)$_POST['delete'];
        $review = getReviewById($del_id);
        if ($review) {
            deleteReview($del_id);
            setFlashMessage('Review deleted successfully', 'success');
        } else {
            setFlashMessage('Review not found.', 'error');
        }
    }
    redirect('reviews.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_feature'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid security token. Please try again.', 'error');
    } else {
        $mode = $_POST['feature_mode'] ?? 'top_rated';
        if (!in_array($mode, ['top_rated', 'latest', 'manual'], true)) {
            $mode = 'top_rated';
        }

        if ($mode === 'manual') {
            $selectedIds = $_POST['featured_reviews'] ?? [];
            setManualFeaturedReviews($selectedIds);
        }

        setFeaturedMode($mode);
        setFlashMessage('Feature settings saved successfully', 'success');
    }
    redirect('reviews.php');
}

$page_title = 'Manage Reviews - Seed2Greens Admin';
$activeTab = $_GET['tab'] ?? 'delete';
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
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="reviews.php" class="active"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><form method="POST" action="logout.php" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><button type="submit" name="logout" style="background: none; border: none; color: inherit; cursor: pointer; font-size: inherit; padding: 0; width: 100%; text-align: left;"><i class="fas fa-sign-out-alt"></i> Logout</button></form></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Review Management</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <form method="POST" action="logout.php" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><button type="submit" name="logout" class="btn btn-secondary btn-sm">Logout</button></form>
                </div>
            </header>
            
            <main class="admin-content">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="flash-message flash-<?php echo sanitize($_SESSION['flash_type'] ?? 'success'); ?>" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    </div>
                <?php endif; ?>

                <div class="admin-card" style="margin-bottom: 20px;">
                    <div style="display: flex; gap: 0; border-bottom: 2px solid #e0e0e0;">
                        <button class="admin-tab-btn <?php echo $activeTab === 'delete' ? 'active' : ''; ?>" data-tab="delete" onclick="switchTab('delete')" style="padding: 12px 24px; border: none; background: none; cursor: pointer; font-size: 15px; font-weight: 600; color: <?php echo $activeTab === 'delete' ? '#2e7d32' : '#666'; ?>; border-bottom: 3px solid <?php echo $activeTab === 'delete' ? '#2e7d32' : 'transparent'; ?>; margin-bottom: -2px;">Delete Reviews</button>
                        <button class="admin-tab-btn <?php echo $activeTab === 'feature' ? 'active' : ''; ?>" data-tab="feature" onclick="switchTab('feature')" style="padding: 12px 24px; border: none; background: none; cursor: pointer; font-size: 15px; font-weight: 600; color: <?php echo $activeTab === 'feature' ? '#2e7d32' : '#666'; ?>; border-bottom: 3px solid <?php echo $activeTab === 'feature' ? '#2e7d32' : 'transparent'; ?>; margin-bottom: -2px;">Feature Reviews</button>
                    </div>
                </div>

                <div id="tab-delete" class="admin-tab-content" style="display: <?php echo $activeTab === 'delete' ? 'block' : 'none'; ?>;">
                    <div class="admin-card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Author</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allReviews)): ?>
                                    <tr><td colspan="6" style="text-align: center; padding: 30px;">No reviews found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($allReviews as $review): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($review['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                            <td><strong><?php echo sanitize($review['name']); ?></strong></td>
                                            <td><?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></td>
                                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo sanitize($review['review']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteReview(<?php echo $review['id']; ?>)">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="tab-feature" class="admin-tab-content" style="display: <?php echo $activeTab === 'feature' ? 'block' : 'none'; ?>;">
                    <div class="admin-card">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="save_feature" value="1">

                            <div class="admin-form-group">
                                <label style="font-weight: 600; margin-bottom: 10px; display: block;">Featured Reviews Mode</label>
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; transition: all 0.2s;">
                                        <input type="radio" name="feature_mode" value="top_rated" <?php echo $currentMode === 'top_rated' ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #2e7d32;">
                                        <div>
                                            <strong>Top Rated 10 Reviews</strong>
                                            <p style="margin: 4px 0 0; font-size: 13px; color: #666;">Automatically feature the 10 highest-rated reviews on the Home Page.</p>
                                        </div>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; transition: all 0.2s;">
                                        <input type="radio" name="feature_mode" value="latest" <?php echo $currentMode === 'latest' ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #2e7d32;">
                                        <div>
                                            <strong>Latest 10 Reviews</strong>
                                            <p style="margin: 4px 0 0; font-size: 13px; color: #666;">Automatically feature the 10 most recently added reviews on the Home Page.</p>
                                        </div>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; transition: all 0.2s;">
                                        <input type="radio" name="feature_mode" value="manual" <?php echo $currentMode === 'manual' ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #2e7d32;">
                                        <div>
                                            <strong>Select Reviews Manually</strong>
                                            <p style="margin: 4px 0 0; font-size: 13px; color: #666;">Choose specific reviews to feature on the Home Page.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="manual-selection" style="display: <?php echo $currentMode === 'manual' ? 'block' : 'none'; ?>; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                <label style="font-weight: 600; margin-bottom: 10px; display: block;">Select Reviews to Feature</label>
                                <div style="display: flex; flex-direction: column; gap: 8px; max-height: 400px; overflow-y: auto; padding: 10px; border: 1px solid #e0e0e0; border-radius: 8px;">
                                    <?php if (empty($allReviews)): ?>
                                        <p style="color: #666; text-align: center; padding: 20px;">No reviews available.</p>
                                    <?php else: ?>
                                        <?php foreach ($allReviews as $review): ?>
                                            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 10px; border-radius: 6px; transition: background 0.2s;">
                                                <input type="checkbox" name="featured_reviews[]" value="<?php echo $review['id']; ?>" <?php echo $review['is_featured'] ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #2e7d32; margin-top: 2px; flex-shrink: 0;">
                                                <div style="flex: 1;">
                                                    <strong><?php echo sanitize($review['name']); ?></strong>
                                                    <span style="color: #f9a825; margin-left: 8px;"><?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></span>
                                                    <p style="margin: 4px 0 0; font-size: 13px; color: #666; line-height: 1.4;"><?php echo sanitize($review['review']); ?></p>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">Save Feature Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="deleteReviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: #fff; padding: 24px; border-radius: 8px; max-width: 380px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <p id="deleteReviewModalText" style="margin: 0 0 20px; font-size: 15px; color: #333;"></p>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeleteReviewModal()">Cancel</button>
                <button type="button" id="deleteReviewConfirmBtn" class="btn btn-danger btn-sm">Continue</button>
            </div>
        </div>
    </div>

    <script>
        const deleteReviewCsrfToken = '<?php echo generateCsrfToken(); ?>';
        let deleteReviewTargetId = null;
        const deleteReviewModal = document.getElementById('deleteReviewModal');
        const deleteReviewModalText = document.getElementById('deleteReviewModalText');
        const deleteReviewConfirmBtn = document.getElementById('deleteReviewConfirmBtn');

        function confirmDeleteReview(id) {
            deleteReviewTargetId = id;
            deleteReviewModalText.textContent = 'Are you sure to delete this review?';
            deleteReviewConfirmBtn.textContent = 'Continue';
            deleteReviewConfirmBtn.onclick = showDeleteReviewWarning;
            openDeleteReviewModal();
        }

        function showDeleteReviewWarning() {
            deleteReviewModalText.textContent = "You won't be able to recover this review once you delete.";
            deleteReviewConfirmBtn.textContent = 'Confirm Delete';
            deleteReviewConfirmBtn.onclick = doDeleteReview;
            openDeleteReviewModal();
        }

        function doDeleteReview() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'reviews.php';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = deleteReviewCsrfToken;
            form.appendChild(csrfInput);
            
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete';
            deleteInput.value = deleteReviewTargetId;
            form.appendChild(deleteInput);
            
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'delete_review';
            submitInput.value = '1';
            form.appendChild(submitInput);
            
            const tabInput = document.createElement('input');
            tabInput.type = 'hidden';
            tabInput.name = 'tab';
            tabInput.value = 'delete';
            form.appendChild(tabInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function openDeleteReviewModal() {
            deleteReviewModal.style.display = 'flex';
        }

        function closeDeleteReviewModal() {
            deleteReviewModal.style.display = 'none';
            deleteReviewTargetId = null;
        }

        deleteReviewModal.addEventListener('click', function(e) {
            if (e.target === deleteReviewModal) {
                closeDeleteReviewModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteReviewModal.style.display === 'flex') {
                closeDeleteReviewModal();
            }
        });

        function switchTab(tab) {
            document.querySelectorAll('.admin-tab-content').forEach(function(el) {
                el.style.display = 'none';
            });
            document.querySelectorAll('.admin-tab-btn').forEach(function(el) {
                el.style.color = '#666';
                el.style.borderBottom = '3px solid transparent';
            });

            document.getElementById('tab-' + tab).style.display = 'block';
            const activeBtn = document.querySelector('.admin-tab-btn[data-tab="' + tab + '"]');
            if (activeBtn) {
                activeBtn.style.color = '#2e7d32';
                activeBtn.style.borderBottom = '3px solid #2e7d32';
            }

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        }

        document.querySelectorAll('input[name="feature_mode"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('manual-selection').style.display = this.value === 'manual' ? 'block' : 'none';
            });
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
