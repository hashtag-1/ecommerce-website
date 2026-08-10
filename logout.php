<?php
// Seed2Greens - Logout
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    logoutUser();
    setFlashMessage('You have been logged out successfully.', 'success');
}

redirect('index.php');
