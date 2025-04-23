<?php
/**
 * Database Configuration File
 * Contains database credentials and site configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Replace with your actual database username
define('DB_PASS', '');            // Replace with your actual database password
define('DB_NAME', 'guntur_properties');

// Site configuration
define('SITE_NAME', 'Guntur Properties');
define('ADMIN_EMAIL', 'admin@gunturproperties.com');
define('ROOT_URL', 'http://localhost/guntur-properties/'); // Replace with your actual URL
define('ADMIN_URL', ROOT_URL . 'admin/');

// Session start
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone

// Define upload paths
define('PROPERTY_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/assets/images/properties/');
define('AGENT_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/assets/images/agents/');

// Max file upload size (in bytes)
define('MAX_FILE_SIZE', 5000000); // 5MB

// Allowed file types for property images
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);