<?php
/**
 * Database Configuration File
 * Contains database credentials and site configuration
 */

// Database credentials for localhost
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        
define('DB_PASS', '');            
define('DB_NAME', 'guntur_properties');

//server
// define('DB_HOST', 'localhost');
// define('DB_USER', 'u900599714_gnt_properties');        
// define('DB_PASS', 'I]fWeOSf8+');            
// define('DB_NAME', 'u900599714_gnt_properties');


// Site configuration
define('SITE_NAME', 'Guntur Properties');
define('ADMIN_EMAIL', 'admin@gunturproperties.com');
define('ROOT_URL', 'http://localhost/gunturProperties/'); // Replace with your actual URL
define('ADMIN_URL', ROOT_URL . 'admin/');

// Session start
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone

// Define upload paths
define('PROPERTY_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/assets/images/properties/');
define('AGENT_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/assets/images/agents/');

// Max file upload size (in bytes)
define('MAX_FILE_SIZE', 5000000); // 5MB

// Allowed file types for property images
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);