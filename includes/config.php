<?php
/**
 * Database Configuration File
 * Contains database credentials and site configuration
 */

// Check if we're on localhost or server
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // Database credentials for localhost
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');        
    define('DB_PASS', '');            
    define('DB_NAME', 'guntur_properties');
    
    // Localhost paths
    define('ROOT_URL', 'http://localhost/gunturProperties/');
    define('PROPERTY_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/assets/images/properties/');
    define('AGENT_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/assets/images/agents/');
    
    // Image URL paths for localhost
    define('IMAGES_URL', ROOT_URL . 'assets/images/');
} else {
    // Database credentials for server
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u900599714_gnt_properties');        
    define('DB_PASS', 'I]fWeOSf8+');            
    define('DB_NAME', 'u900599714_gnt_properties');
    
    // Server paths
    define('ROOT_URL', 'https://gunturproperties.com/');
    define('PROPERTY_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/properties/');
    define('AGENT_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/agents/');
    
    // Image URL paths for server
    define('IMAGES_URL', ROOT_URL . 'assets/images/');
}

// Define image URL paths for display in browser
define('PROPERTY_IMAGES_URL', IMAGES_URL . 'properties/');
define('AGENT_IMAGES_URL', IMAGES_URL . 'agents/');
define('DEFAULT_IMAGE_URL', IMAGES_URL . 'no-image.jpg');

// Site configuration
define('SITE_NAME', 'Guntur Properties');
define('ADMIN_EMAIL', 'admin@gunturproperties.com');
define('ADMIN_URL', ROOT_URL . 'admin/');

// Session start
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone

// Max file upload size (in bytes)
define('MAX_FILE_SIZE', 10000000); // 5MB

// Allowed file types for property images
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Add error handling for directory permissions
foreach ([PROPERTY_IMG_PATH, AGENT_IMG_PATH] as $path) {
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
    
    if (!is_writable($path)) {
        error_log("Warning: Directory not writable: " . $path);
        // You can also set a session variable to display an admin warning
        $_SESSION['upload_error'] = "Upload directory not writable. Please contact your web administrator.";
    }
}