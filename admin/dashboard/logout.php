<?php
/**
 * Admin Logout Page
 * Logs out the user and redirects to login page
 */
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Regenerate session ID
    session_start();
    session_regenerate_id(true);
    
    // Set flash message
    setFlashMessage('info', 'You have been successfully logged out.');
}

// Redirect to login page
redirect('../login.php');
?>