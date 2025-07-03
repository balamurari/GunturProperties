<?php
/**
 * Helper Functions
 * Contains various utility functions used throughout the admin panel
 */

// Include database and config
require_once 'config.php';
require_once 'database.php';

// Initialize database connection
$db = new Database();

/**
 * Sanitize user input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a specific URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set a flash message to be displayed on the next page load
 * 
 * @param string $type Message type (success, error, info)
 * @param string $message Message content
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display flash message if available and clear it
 * 
 * @return void
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        
        echo "<div class='alert alert-{$flash['type']}'>";
        echo $flash['message'];
        echo "</div>";
        
        // Clear the flash message
        unset($_SESSION['flash']);
    }
}

/**
 * Check if user is logged in, if not redirect to login page
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require user to be logged in, redirect to login if not
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'You must be logged in to access this page');
        redirect(ADMIN_URL . 'login.php');
    }
}

/**
 * Check if logged in user is an admin
 * 
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require user to be an admin, redirect to dashboard if not
 * 
 * @return void
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        setFlashMessage('error', 'You do not have permission to access this page');
        redirect(ADMIN_URL . 'dashboard.php');
    }
}

/**
 * Get user details by ID
 * 
 * @param int $id User ID
 * @return array|bool User data or false if not found
 */
function getUserById($id) {
    global $db;
    
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $id);
    
    return $db->single();
}

/**
 * Get all properties with optional filtering
 * 
 * @param array $filters Associative array of filters (status, featured, agent_id, etc)
 * @param int $limit Number of properties to return (0 for all)
 * @param int $offset Offset for pagination
 * @return array Properties data
 */
function getProperties($filters = [], $limit = 0, $offset = 0) {
    global $db;
    
    $sql = "SELECT p.*, pt.name AS type_name, u.name AS agent_name 
            FROM properties p
            LEFT JOIN property_types pt ON p.type_id = pt.id
            LEFT JOIN users u ON p.agent_id = u.id
            WHERE 1=1";
    
    // Add filters
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $sql .= " AND p.$key = :$key";
            }
        }
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    // Add limit and offset
    if ($limit > 0) {
        $sql .= " LIMIT :limit";
        if ($offset > 0) {
            $sql .= " OFFSET :offset";
        }
    }
    
    $db->query($sql);
    
    // Bind filter values
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $db->bind(":$key", $value);
            }
        }
    }
    
    // Bind limit and offset
    if ($limit > 0) {
        $db->bind(':limit', $limit, PDO::PARAM_INT);
        if ($offset > 0) {
            $db->bind(':offset', $offset, PDO::PARAM_INT);
        }
    }
    
    return $db->resultSet();
}

/**
 * Get a property by ID with all related data
 * 
 * @param int $id Property ID
 * @return array|bool Property data or false if not found
 */
function getPropertyById($id) {
    global $db;
    
    // Get property details
    $db->query("SELECT p.*, pt.name AS type_name, u.name AS agent_name, u.email AS agent_email, u.phone AS agent_phone
                FROM properties p
                LEFT JOIN property_types pt ON p.type_id = pt.id
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.id = :id");
    $db->bind(':id', $id);
    
    $property = $db->single();
    
    if (!$property) {
        return false;
    }
    
    // Get property images
    $db->query("SELECT * FROM property_images WHERE property_id = :id ORDER BY is_primary DESC, sort_order ASC");
    $db->bind(':id', $id);
    
    $property['images'] = $db->resultSet();
    
    // Get property features
    $db->query("SELECT pf.*, pfm.value 
                FROM property_feature_mapping pfm
                JOIN property_features pf ON pfm.feature_id = pf.id
                WHERE pfm.property_id = :id");
    $db->bind(':id', $id);
    
    $property['features'] = $db->resultSet();
    
    return $property;
}

/**
 * Get all agents (users with agent role)
 * 
 * @return array Agents data
 */
function getAgents() {
    $db = new Database();
    
    // Make sure to select agent.id, not user.id
    $db->query("SELECT a.id, a.user_id, u.name, u.email, u.phone, u.profile_pic, 
               a.position, a.experience, a.rating, a.properties_sold, a.featured
               FROM agents a
               JOIN users u ON a.user_id = u.id
               WHERE u.status = 1 AND u.role = 'agent'
               ORDER BY u.name ASC");
    
    return $db->resultSet();
}

/**
 * Get all enquiries with optional filtering
 * 
 * @param array $filters Associative array of filters (status, property_id, agent_id, etc)
 * @param int $limit Number of enquiries to return (0 for all)
 * @param int $offset Offset for pagination
 * @return array Enquiries data
 */
function getEnquiries($filters = [], $limit = 0, $offset = 0) {
    global $db;
    
    $sql = "SELECT e.*, p.title AS property_title, u.name AS agent_name 
            FROM enquiries e
            LEFT JOIN properties p ON e.property_id = p.id
            LEFT JOIN users u ON e.agent_id = u.id
            WHERE 1=1";
    
    // Add filters
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $sql .= " AND e.$key = :$key";
            }
        }
    }
    
    $sql .= " ORDER BY e.created_at DESC";
    
    // Add limit and offset
    if ($limit > 0) {
        $sql .= " LIMIT :limit";
        if ($offset > 0) {
            $sql .= " OFFSET :offset";
        }
    }
    
    $db->query($sql);
    
    // Bind filter values
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $db->bind(":$key", $value);
            }
        }
    }
    
    // Bind limit and offset
    if ($limit > 0) {
        $db->bind(':limit', $limit, PDO::PARAM_INT);
        if ($offset > 0) {
            $db->bind(':offset', $offset, PDO::PARAM_INT);
        }
    }
    
    return $db->resultSet();
}

/**
 * Enhanced file upload function - handles both localhost and server
 * 
 * @param array $file The $_FILES array element
 * @param string $destination Directory to save the file
 * @param array $allowed_extensions Array of allowed file extensions
 * @param int $max_size Maximum file size in bytes
 * @return array|bool Array with filename and path on success, false on failure
 */
function uploadFile($file, $destination, $allowed_extensions = [], $max_size = 0) {
    // Debug logging
    error_log('[UPLOAD DEBUG] Starting upload process');
    error_log('[UPLOAD DEBUG] Destination: ' . $destination);
    error_log('[UPLOAD DEBUG] File: ' . print_r($file, true));
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log('[UPLOAD ERROR] Upload error code: ' . $file['error']);
        return false;
    }
    
    // Check file size
    if ($max_size > 0 && $file['size'] > $max_size) {
        error_log('[UPLOAD ERROR] File too large: ' . $file['size'] . ' > ' . $max_size);
        return false;
    }
    
    // Get file extension
    $filename = $file['name'];
    $temp = explode('.', $filename);
    $ext = strtolower(end($temp));
    
    // Check file extension
    if (!empty($allowed_extensions) && !in_array($ext, $allowed_extensions)) {
        error_log('[UPLOAD ERROR] Invalid extension: ' . $ext);
        return false;
    }
    
    // Clean destination path - remove double slashes
    $destination = rtrim($destination, '/');
    
    // Create destination directory if it doesn't exist
    if (!file_exists($destination)) {
        error_log('[UPLOAD DEBUG] Creating directory: ' . $destination);
        if (!mkdir($destination, 0755, true)) {
            error_log('[UPLOAD ERROR] Failed to create directory: ' . $destination);
            return false;
        }
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $ext;
    $filepath = $destination . '/' . $new_filename;
    
    error_log('[UPLOAD DEBUG] Full filepath: ' . $filepath);
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        error_log('[UPLOAD SUCCESS] File uploaded: ' . $filepath);
        
        // Generate the relative path for database storage
        $relative_path = generateRelativePath($filepath);
        
        error_log('[UPLOAD DEBUG] Relative path for DB: ' . $relative_path);
        
        return [
            'filename' => $new_filename,
            'path' => $relative_path,
            'full_path' => $filepath
        ];
    }
    
    error_log('[UPLOAD ERROR] Failed to move uploaded file');
    return false;
}

/**
 * Delete a property and all related data with proper file cleanup
 * 
 * @param int $id Property ID
 * @return bool Success status
 */
function deleteProperty($id) {
    global $db;
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Get image paths to delete files
        $db->query("SELECT image_path FROM property_images WHERE property_id = :id");
        $db->bind(':id', $id);
        $images = $db->resultSet();
        
        // Delete property features mapping
        $db->query("DELETE FROM property_feature_mapping WHERE property_id = :id");
        $db->bind(':id', $id);
        $db->execute();
        
        // Delete property images from database
        $db->query("DELETE FROM property_images WHERE property_id = :id");
        $db->bind(':id', $id);
        $db->execute();
        
        // Delete property
        $db->query("DELETE FROM properties WHERE id = :id");
        $db->bind(':id', $id);
        $db->execute();
        
        // Commit transaction
        $db->endTransaction();
        
        // Delete image files from filesystem
        foreach ($images as $image) {
            $file_path = $_SERVER['DOCUMENT_ROOT'];
            
            // Add project folder for localhost
            if ($_SERVER['HTTP_HOST'] == 'localhost') {
                $file_path .= '/gunturProperties';
            }
            
            $file_path .= '/' . ltrim($image['image_path'], '/');
            
            if (file_exists($file_path)) {
                unlink($file_path);
                error_log('[DELETE] Removed file: ' . $file_path);
            }
        }
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->cancelTransaction();
        error_log('[DELETE ERROR] ' . $e->getMessage());
        return false;
    }
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M, Y') {
    return date($format, strtotime($date));
}

/**
 * Format price for display
 * 
 * @param float $price Price value
 * @param string $currency Currency symbol (default Indian Rupee)
 * @return string Formatted price
 */
function formatPrice($price, $currency = '₹') {
    return $currency . ' ' . number_format($price, 2);
}

/**
 * Get a setting value by key
 * 
 * @param string $key Setting key
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value or default
 */
function getSetting($key, $default = null) {
    global $db;
    
    $db->query("SELECT setting_value FROM settings WHERE setting_key = :key");
    $db->bind(':key', $key);
    
    $result = $db->single();
    
    return $result ? $result['setting_value'] : $default;
}

/**
 * Update a setting value
 * 
 * @param string $key Setting key
 * @param mixed $value Setting value
 * @return bool Success status
 */
function updateSetting($key, $value) {
    global $db;
    
    $db->query("INSERT INTO settings (setting_key, setting_value) 
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE setting_value = :value");
    $db->bind(':key', $key);
    $db->bind(':value', $value);
    
    return $db->execute();
}

/**
 * Generate pagination links
 * 
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param string $url_pattern URL pattern with %d placeholder for page number
 * @return string HTML pagination links
 */
function generatePagination($current_page, $total_pages, $url_pattern) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous page link
    if ($current_page > 1) {
        $html .= '<a href="' . sprintf($url_pattern, $current_page - 1) . '">&laquo; Previous</a>';
    }
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $html .= '<a class="active">' . $i . '</a>';
        } else {
            $html .= '<a href="' . sprintf($url_pattern, $i) . '">' . $i . '</a>';
        }
    }
    
    // Next page link
    if ($current_page < $total_pages) {
        $html .= '<a href="' . sprintf($url_pattern, $current_page + 1) . '">Next &raquo;</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Generates the correct URL path for an asset file.
 * Adjust $base_url based on your project structure.
 *
 * @param string $path The relative path to the asset from the assets directory (e.g., 'images/users/pic.jpg' or 'images/agent-placeholder.jpg').
 * @return string The full URL path to the asset.
 */
function getAssetPath($path) {
    // Option 1: Simple relative path (adjust if needed)
    // This assumes your assets folder is one level up from the 'admin/agents/' directory
    $base_url = '../../assets/'; // Go up from admin/agents/ to root, then into assets/

    // Option 2: Using a BASE_URL constant (Recommended if defined in config.php)
    // if (defined('BASE_URL')) {
    //     $base_url = BASE_URL . 'assets/'; // Assumes BASE_URL ends with '/' or adjust accordingly
    // } else {
    //     // Fallback if BASE_URL is not defined
    //     $base_url = '../../assets/'; // Adjust relative path as needed
    // }

    // Ensure no double slashes and remove leading slash from $path if present
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

/**
 * Check if user has a specific permission
 * 
 * @param string $permission Permission key to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permission) {
    // If user is not logged in, they have no permissions
    if (!isLoggedIn()) {
        return false;
    }
    
    // Get user role from session
    $role = $_SESSION['user_role'] ?? '';
    
    // Define role-based permissions
    $permissions = [
        'admin' => [
            'add_property', 'edit_property', 'delete_property',
            'manage_agents', 'manage_users', 'manage_settings',
            'view_reports', 'view_dashboard'
        ],
        'agent' => [
            'add_property', 'edit_own_property', 'view_dashboard',
            'view_own_leads'
        ],
        'manager' => [
            'add_property', 'edit_property', 'manage_agents',
            'view_reports', 'view_dashboard'
        ],
        'user' => [
            'view_property'
        ]
    ];
    
    // Admin has all permissions
    if ($role === 'admin') {
        return true;
    }
    
    // Check if user's role has the requested permission
    if (isset($permissions[$role]) && in_array($permission, $permissions[$role])) {
        return true;
    }
    
    return false;
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if token is valid, false otherwise
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Log user activity for audit trail
 * 
 * @param int $user_id User ID
 * @param string $action Action description
 * @return bool Success status
 */
function logActivity($user_id, $action) {
    $db = new Database();
    
    // Check if activity_log table exists, create if not
    $db->query("SHOW TABLES LIKE 'activity_log'");
    if ($db->rowCount() === 0) {
        $db->query("CREATE TABLE activity_log (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            action TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
    
    $db->query("INSERT INTO activity_log (user_id, action, ip_address, user_agent) 
               VALUES (:user_id, :action, :ip_address, :user_agent)");
    
    $db->bind(':user_id', $user_id);
    $db->bind(':action', $action);
    $db->bind(':ip_address', $_SERVER['REMOTE_ADDR']);
    $db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
    
    return $db->execute();
}

/**
 * ========================================
 * NEW IMAGE HANDLING FUNCTIONS
 * ========================================
 */

/**
 * Generate relative path for database storage
 */
function generateRelativePath($full_path) {
    // Remove the document root and project folder if present
    $doc_root = $_SERVER['DOCUMENT_ROOT'];
    
    // Handle both localhost and server environments
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        // Localhost: remove document root + /gunturProperties
        $relative = str_replace($doc_root . '/gunturProperties/', '', $full_path);
    } else {
        // Server: remove just document root
        $relative = str_replace($doc_root . '/', '', $full_path);
    }
    
    // Clean up any remaining issues
    $relative = ltrim($relative, '/');
    
    return $relative;
}

/**
 * Enhanced function to get proper image URLs
 */
function getPropertyImageUrl($image_path, $default = null) {
    // Set default image if none provided
    if ($default === null) {
        $default = 'assets/images/no-image.jpg';
    }
    
    // Return default if path is empty
    if (empty($image_path)) {
        return ROOT_URL . $default;
    }
    
    // If it's already a full URL, return as-is
    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
        return $image_path;
    }
    
    // Clean up the path - remove multiple slashes and leading slash
    $clean_path = preg_replace('/\/+/', '/', $image_path);
    $clean_path = ltrim($clean_path, '/');
    
    // Build the full URL
    $full_url = ROOT_URL . $clean_path;
    
    // For debugging - check if file exists (optional)
    $server_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $clean_path;
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $server_path = $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $clean_path;
    }
    
    if (!file_exists($server_path)) {
        error_log('[IMAGE WARNING] Image file not found: ' . $server_path);
        // Return default image URL
        return ROOT_URL . $default;
    }
    
    return $full_url;
}

/**
 * Enhanced function for agent/user profile images
 */
function getAgentImageUrl($image_path, $default = null) {
    if ($default === null) {
        $default = 'assets/images/agent-placeholder.jpg';
    }
    
    return getPropertyImageUrl($image_path, $default);
}

/**
 * Function to clean up existing double slash paths in database
 */
function fixImagePathsInDatabase() {
    global $db;
    
    try {
        // Fix property images
        $db->query("UPDATE property_images SET image_path = REPLACE(image_path, '//', '/') WHERE image_path LIKE '%//%'");
        $result1 = $db->execute();
        
        // Fix user profile pics
        $db->query("UPDATE users SET profile_pic = REPLACE(profile_pic, '//', '/') WHERE profile_pic LIKE '%//%'");
        $result2 = $db->execute();
        
        error_log('[DATABASE FIX] Fixed image paths in database');
        return $result1 && $result2;
    } catch (Exception $e) {
        error_log('[DATABASE ERROR] Failed to fix paths: ' . $e->getMessage());
        return false;
    }
}

/**
 * Helper function to check image accessibility
 */
function checkImageAccessibility($image_path) {
    $full_url = getPropertyImageUrl($image_path);
    
    // Try to get headers to check if image is accessible
    $headers = @get_headers($full_url);
    $is_accessible = $headers && strpos($headers[0], '200') !== false;
    
    return [
        'url' => $full_url,
        'accessible' => $is_accessible,
        'headers' => $headers
    ];
}


?>