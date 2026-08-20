<?php
// Seed2Greens - Admin Logout
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request. Please try again.', 'error');
        redirect('login.php');
    }
    
    if (isAdminLoggedIn()) {
        logoutAdmin();
        setFlashMessage('You have been logged out from admin panel.', 'success');
    }
    
    redirect('login.php');
}

if (isset($_GET['logout'])) {
    if (!validateCsrfToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request. Please try again.', 'error');
        redirect('login.php');
    }
    
    if (isAdminLoggedIn()) {
        logoutAdmin();
        setFlashMessage('You have been logged out from admin panel.', 'success');
    }
    
    redirect('login.php');
}

redirect('login.php');

