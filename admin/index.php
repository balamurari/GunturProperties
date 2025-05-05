<?php
/**
 * Admin Index Page
 * Redirects to dashboard if logged in, otherwise to login page
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard
    redirect('dashboard/dashboard.php');
} else {
    // Redirect to login page
    redirect('login.php');
}
?>