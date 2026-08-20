<?php
// Seed2Greens - Logout
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request. Please try again.', 'error');
        redirect('index.php');
    }
    
    if (isLoggedIn()) {
        logoutUser();
        setFlashMessage('You have been logged out successfully.', 'success');
    }
    
    redirect('index.php');
}

redirect('index.php');
