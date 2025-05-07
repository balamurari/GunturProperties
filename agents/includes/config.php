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
    
    // Add image URL paths for localhost
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
    
    // Add image URL paths for server
    define('IMAGES_URL', ROOT_URL . 'assets/images/');
}

// Define image URL paths for both environments
define('PROPERTY_IMAGES_URL', IMAGES_URL . 'properties/');
define('AGENT_IMAGES_URL', IMAGES_URL . 'agents/');
define('DEFAULT_IMAGE_URL', IMAGES_URL . 'no-image.jpg');

// Site configuration
define('SITE_NAME', 'Guntur Properties');
// define('ADMIN_EMAIL', 'admin@gunturproperties.com');
define('ADMIN_URL', ROOT_URL . 'admin/');

// Session start
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone

// Max file upload size (in bytes)
define('MAX_FILE_SIZE', 5000000); // 5MB

// Allowed file types for property images
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Ensure upload directories exist and are writable
foreach ([PROPERTY_IMG_PATH, AGENT_IMG_PATH] as $path) {
    if (!file_exists($path)) {
        // Try to create the directory if it doesn't exist
        if (!mkdir($path, 0755, true)) {
            error_log("Failed to create directory: " . $path);
        }
    }
    
    if (!is_writable($path)) {
        error_log("Warning: Directory not writable: " . $path);
        // Store error in session for admin notification
        $_SESSION['upload_error'] = "Upload directory not writable: " . $path;
    }
}

// Add a function to help with debugging upload issues
function debug_upload_error($file_error) {
    switch($file_error) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension";
        default:
            return "Unknown upload error";
    }
}