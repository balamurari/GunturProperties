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
 * Delete a property and all related data
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
        
        // Delete property images
        $db->query("DELETE FROM property_images WHERE property_id = :id");
        $db->bind(':id', $id);
        $db->execute();
        
        // Delete property
        $db->query("DELETE FROM properties WHERE id = :id");
        $db->bind(':id', $id);
        $db->execute();
        
        // Commit transaction
        $db->endTransaction();
        
        // Delete image files
        foreach ($images as $image) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->cancelTransaction();
        return false;
    }
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
 * Handle file upload
 * 
 * @param array $file The $_FILES array element
 * @param string $destination Directory to save the file
 * @param array $allowed_extensions Array of allowed file extensions
 * @param int $max_size Maximum file size in bytes
 * @return array|bool Array with filename and path on success, false on failure
 */
function uploadFile($file, $destination, $allowed_extensions = [], $max_size = 0) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($max_size > 0 && $file['size'] > $max_size) {
        return false;
    }
    
    // Get file extension
    $filename = $file['name'];
    $temp = explode('.', $filename);
    $ext = strtolower(end($temp));
    
    // Check file extension
    if (!empty($allowed_extensions) && !in_array($ext, $allowed_extensions)) {
        return false;
    }
    
    // Create destination directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $ext;
    $filepath = $destination . '/' . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'filename' => $new_filename,
            'path' => str_replace($_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/', '', $filepath)
        ];
    }
    
    return false;
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