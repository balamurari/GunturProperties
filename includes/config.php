<?php
/**
 * Database Configuration File
 * Contains database credentials and site configuration
 * OPTIMIZED for perfect agent image handling
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
define('ADMIN_URL', ROOT_URL . 'admin/');

// Session configuration for security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// File upload configuration
define('MAX_FILE_SIZE', 10000000); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Agent-specific upload limits
define('AGENT_MAX_FILE_SIZE', 5000000); // 5MB for agent photos
define('AGENT_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Ensure upload directories exist and are writable
$directories = [
    'Properties' => PROPERTY_IMG_PATH,
    'Agents' => AGENT_IMG_PATH
];

foreach ($directories as $type => $path) {
    if (!file_exists($path)) {
        // Try to create the directory if it doesn't exist
        if (mkdir($path, 0755, true)) {
            error_log("✅ Created $type directory: " . $path);
        } else {
            error_log("❌ Failed to create $type directory: " . $path);
        }
    }
    
    if (!is_writable($path)) {
        error_log("⚠️ Warning: $type directory not writable: " . $path);
        // Store error in session for admin notification
        $_SESSION['upload_error'] = "$type directory not writable: " . $path;
    } else {
        error_log("✅ $type directory is writable: " . $path);
    }
}

// Fallback ROOT_URL definition
if (!defined('ROOT_URL')) {
    define('ROOT_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/');
}

// Create placeholder images if they don't exist
$placeholder_images = [
    'assets/images/no-image.jpg' => [640, 480, 'No Image Available'],
    'assets/images/agent-placeholder.jpg' => [300, 300, 'Agent Photo'],
    'assets/images/property-placeholder.jpg' => [640, 480, 'Property Image']
];

foreach ($placeholder_images as $relative_path => $config) {
    list($width, $height, $text) = $config;
    
    $full_path = $_SERVER['DOCUMENT_ROOT'];
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $full_path .= '/gunturProperties/';
    } else {
        $full_path .= '/';
    }
    $full_path .= $relative_path;
    
    if (!file_exists($full_path)) {
        $dir = dirname($full_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Create simple placeholder if GD is available
        if (extension_loaded('gd')) {
            $image = imagecreate($width, $height);
            $bg_color = imagecolorallocate($image, 240, 240, 240);
            $text_color = imagecolorallocate($image, 100, 100, 100);
            
            imagefill($image, 0, 0, $bg_color);
            
            $font_size = 5;
            $text_width = imagefontwidth($font_size) * strlen($text);
            $text_height = imagefontheight($font_size);
            $x = ($width - $text_width) / 2;
            $y = ($height - $text_height) / 2;
            
            imagestring($image, $font_size, $x, $y, $text, $text_color);
            
            imagejpeg($image, $full_path, 80);
            imagedestroy($image);
            
            error_log("✅ Created placeholder: " . $relative_path);
        }
    }
}

// Development/Production mode detection
define('IS_DEVELOPMENT', $_SERVER['HTTP_HOST'] == 'localhost');
define('IS_PRODUCTION', !IS_DEVELOPMENT);

// Logging configuration
if (IS_PRODUCTION) {
    // In production, log errors but don't display them
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/error_log');
}

// Success message for configuration
if (IS_DEVELOPMENT) {
    error_log("🚀 Guntur Properties Config Loaded Successfully!");
    error_log("📁 Environment: " . ($_SERVER['HTTP_HOST'] == 'localhost' ? 'LOCALHOST' : 'PRODUCTION'));
    error_log("🌐 ROOT_URL: " . ROOT_URL);
    error_log("📸 AGENT_IMG_PATH: " . AGENT_IMG_PATH);
    error_log("🏠 PROPERTY_IMG_PATH: " . PROPERTY_IMG_PATH);
}
?>