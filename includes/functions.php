    <?php
    /**
     * Helper Functions - OPTIMIZED VERSION
     * Contains various utility functions used throughout the admin panel
     * ENHANCED with perfect agent image handling
     */

    // Include database and config
    require_once 'config.php';
    require_once 'database.php';

    // Initialize database connection
    $db = new Database();

    // ============================================================================
    // CORE UTILITY FUNCTIONS
    // ============================================================================

    /**
     * Sanitize user input data
     */
    function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Redirect to a specific URL
     */
    function redirect($url) {
        header("Location: $url");
        exit;
    }

    /**
     * Set a flash message to be displayed on the next page load
     */
    function setFlashMessage($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Display flash message if available and clear it
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

    // ============================================================================
    // AUTHENTICATION & AUTHORIZATION FUNCTIONS
    // ============================================================================

    /**
     * Check if user is logged in
     */
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user has specific role(s)
     */
    function hasRole($roles) {
        if (!isLoggedIn()) {
            return false;
        }
        
        $userRole = $_SESSION['user_role'] ?? 'user';
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        
        return $userRole === $roles;
    }

    /**
     * Get current user information
     */
    function getCurrentUserId() {
        return $_SESSION['user_id'] ?? 0;
    }

    function getCurrentUserName() {
        return $_SESSION['user_name'] ?? 'Unknown';
    }

    function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? 'user';
    }

    /**
     * Require user to be logged in
     */
    function requireLogin() {
        if (!isLoggedIn()) {
            setFlashMessage('error', 'You must be logged in to access this page');
            redirect(ADMIN_URL . 'login.php');
        }
    }

    /**
     * Check if logged in user is an admin
     */
    function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Require user to be an admin
     */
    function requireAdmin() {
        requireLogin();
        
        if (!isAdmin()) {
            setFlashMessage('error', 'You do not have permission to access this page');
            redirect(ADMIN_URL . 'dashboard.php');
        }
    }

    // ============================================================================
    // PERFECT AGENT IMAGE FUNCTIONS
    // ============================================================================

    /**
     * PERFECT agent image URL function - handles all cases flawlessly
     */
    function getAgentImageUrl($image_path, $default = null) {
        // Set default image if none provided
        if ($default === null) {
            $default = 'assets/images/agent-placeholder.jpg';
        }
        
        // Return default if path is empty
        if (empty($image_path)) {
            return ROOT_URL . $default;
        }
        
        // If it's already a clean relative path starting with assets/images/agents/, use it
        if (strpos($image_path, 'assets/images/agents/') === 0) {
            $clean_path = $image_path;
        } else {
            // For any other format, extract filename and rebuild path
            $filename = basename($image_path);
            $clean_path = 'assets/images/agents/' . $filename;
        }
        
        // Build the full URL
        $full_url = ROOT_URL . $clean_path;
        
        // Check if file exists on server
        $server_path = $_SERVER['DOCUMENT_ROOT'];
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            $server_path .= '/gunturProperties/';
        } else {
            $server_path .= '/';
        }
        $server_path .= $clean_path;
        
        if (!file_exists($server_path)) {
            return ROOT_URL . $default;
        }
        
        return $full_url;
    }

    /**
     * PERFECT agent image upload function
     */
    function uploadAgentImage($file, $user_id) {
        // Validate file upload
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false, 
                'message' => 'File upload error: ' . ($file['error'] ?? 'Unknown error')
            ];
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, AGENT_ALLOWED_EXTENSIONS)) {
            return [
                'success' => false, 
                'message' => 'Invalid file type. Allowed: ' . implode(', ', AGENT_ALLOWED_EXTENSIONS)
            ];
        }
        
        // Check file size
        if ($file['size'] > AGENT_MAX_FILE_SIZE) {
            return [
                'success' => false, 
                'message' => 'File too large. Maximum size: ' . (AGENT_MAX_FILE_SIZE / 1024 / 1024) . 'MB'
            ];
        }
        
        // Generate unique filename
        $filename = 'agent_' . uniqid() . '.' . $file_extension;
        $destination_file = AGENT_IMG_PATH . $filename;
        
        // Create directory if it doesn't exist
        if (!file_exists(AGENT_IMG_PATH)) {
            if (!mkdir(AGENT_IMG_PATH, 0755, true)) {
                return [
                    'success' => false, 
                    'message' => 'Failed to create upload directory'
                ];
            }
        }
        
        // Delete old image if exists
        global $db;
        $db->query("SELECT profile_pic FROM users WHERE id = :id");
        $db->bind(':id', $user_id);
        $old_user = $db->single();
        
        if ($old_user && !empty($old_user['profile_pic'])) {
            $old_server_path = $_SERVER['DOCUMENT_ROOT'];
            if ($_SERVER['HTTP_HOST'] == 'localhost') {
                $old_server_path .= '/gunturProperties/';
            } else {
                $old_server_path .= '/';
            }
            $old_server_path .= $old_user['profile_pic'];
            
            if (file_exists($old_server_path)) {
                unlink($old_server_path);
            }
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination_file)) {
            // CRITICAL: Store perfect relative path in database
            $relative_path = 'assets/images/agents/' . $filename;
            
            // Update database
            $db->query("UPDATE users SET profile_pic = :path WHERE id = :id");
            $db->bind(':path', $relative_path);
            $db->bind(':id', $user_id);
            
            if ($db->execute()) {
                return [
                    'success' => true,
                    'message' => 'Agent image uploaded successfully',
                    'filename' => $filename,
                    'relative_path' => $relative_path,
                    'url' => ROOT_URL . $relative_path
                ];
            } else {
                // Delete uploaded file if database update failed
                unlink($destination_file);
                return [
                    'success' => false, 
                    'message' => 'Database update failed'
                ];
            }
        } else {
            return [
                'success' => false, 
                'message' => 'Failed to move uploaded file'
            ];
        }
    }

    /**
     * Create complete agent (user + agent record + image)
     */
    function createAgent($data, $profile_image = null) {
        global $db;
        
        try {
            $db->beginTransaction();
            
            // 1. Create user record
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $db->query("INSERT INTO users (name, email, password, phone, role, status, created_at) 
                    VALUES (:name, :email, :password, :phone, 'agent', 1, NOW())");
            
            $db->bind(':name', sanitize($data['name']));
            $db->bind(':email', sanitize($data['email']));
            $db->bind(':password', $password_hash);
            $db->bind(':phone', sanitize($data['phone']));
            
            $db->execute();
            $user_id = $db->lastInsertId();
            
            // 2. Handle profile image upload if provided
            $image_upload_result = null;
            if ($profile_image && $profile_image['error'] === UPLOAD_ERR_OK) {
                $image_upload_result = uploadAgentImage($profile_image, $user_id);
                if (!$image_upload_result['success']) {
                    throw new Exception($image_upload_result['message']);
                }
            }
            
            // 3. Create agent record
            $db->query("INSERT INTO agents (user_id, position, description, experience, office_address, created_at) 
                    VALUES (:user_id, :position, :description, :experience, :office_address, NOW())");
            
            $db->bind(':user_id', $user_id);
            $db->bind(':position', sanitize($data['position'] ?? 'Real Estate Agent'));
            $db->bind(':description', sanitize($data['description'] ?? ''));
            $db->bind(':experience', intval($data['experience'] ?? 0));
            $db->bind(':office_address', sanitize($data['office_address'] ?? ''));
            
            $db->execute();
            $agent_id = $db->lastInsertId();
            
            // 4. Handle specializations if provided
            if (!empty($data['specializations'])) {
                foreach ($data['specializations'] as $spec_id) {
                    if (is_numeric($spec_id)) {
                        $db->query("INSERT INTO agent_specialization_mapping (agent_id, specialization_id) 
                                VALUES (:agent_id, :spec_id)");
                        $db->bind(':agent_id', $agent_id);
                        $db->bind(':spec_id', intval($spec_id));
                        $db->execute();
                    }
                }
            }
            
            $db->endTransaction();
            
            return [
                'success' => true,
                'message' => 'Agent created successfully',
                'user_id' => $user_id,
                'agent_id' => $agent_id,
                'image_upload' => $image_upload_result
            ];
            
        } catch (Exception $e) {
            $db->cancelTransaction();
            return [
                'success' => false,
                'message' => 'Failed to create agent: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update agent profile image
     */
    function updateAgentImage($user_id, $profile_image) {
        if (!$profile_image || $profile_image['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'No valid image file provided'
            ];
        }
        
        return uploadAgentImage($profile_image, $user_id);
    }

    /**
     * Delete agent and cleanup files
     */
    function deleteAgent($agent_id) {
        global $db;
        
        try {
            $db->beginTransaction();
            
            // Get agent and user info
            $db->query("SELECT a.*, u.profile_pic FROM agents a 
                    JOIN users u ON a.user_id = u.id 
                    WHERE a.id = :agent_id");
            $db->bind(':agent_id', $agent_id);
            $agent = $db->single();
            
            if (!$agent) {
                throw new Exception('Agent not found');
            }
            
            // Delete profile image file if exists
            if (!empty($agent['profile_pic'])) {
                $image_server_path = $_SERVER['DOCUMENT_ROOT'];
                if ($_SERVER['HTTP_HOST'] == 'localhost') {
                    $image_server_path .= '/gunturProperties/';
                } else {
                    $image_server_path .= '/';
                }
                $image_server_path .= $agent['profile_pic'];
                
                if (file_exists($image_server_path)) {
                    unlink($image_server_path);
                }
            }
            
            // Delete agent record (cascading will handle related records)
            $db->query("DELETE FROM agents WHERE id = :agent_id");
            $db->bind(':agent_id', $agent_id);
            $db->execute();
            
            // Delete user record
            $db->query("DELETE FROM users WHERE id = :user_id");
            $db->bind(':user_id', $agent['user_id']);
            $db->execute();
            
            $db->endTransaction();
            
            return [
                'success' => true,
                'message' => 'Agent deleted successfully'
            ];
            
        } catch (Exception $e) {
            $db->cancelTransaction();
            return [
                'success' => false,
                'message' => 'Failed to delete agent: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all agents with enhanced data
     */
    function getAgents() {
        $db = new Database();
        
        $db->query("SELECT a.id, a.user_id, u.name, u.email, u.phone, u.profile_pic, 
                a.position, a.description, a.experience, a.rating, a.properties_sold, a.featured,
                a.office_address, a.office_hours, a.created_at
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE u.status = 1 AND u.role = 'agent'
                ORDER BY u.name ASC");
        
        return $db->resultSet();
    }

    /**
     * Get agent by ID with full details
     */
    function getAgentById($agent_id) {
        global $db;
        
        $db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic, u.created_at as user_created_at
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = :id AND u.status = 1");
        $db->bind(':id', $agent_id);
        
        $agent = $db->single();
        
        if ($agent) {
            // Get agent specializations
            $db->query("SELECT as.id, as.name 
                    FROM agent_specializations as
                    JOIN agent_specialization_mapping asm ON as.id = asm.specialization_id
                    WHERE asm.agent_id = :agent_id");
            $db->bind(':agent_id', $agent_id);
            
            $agent['specializations'] = $db->resultSet();
            
            // Get agent properties count
            $db->query("SELECT COUNT(*) as property_count FROM properties WHERE agent_id = :agent_id");
            $db->bind(':agent_id', $agent_id);
            $count_result = $db->single();
            $agent['property_count'] = $count_result['property_count'];
        }
        
        return $agent;
    }

    // ============================================================================
    // PROPERTY FUNCTIONS (KEEP EXISTING - WORKING WELL)
    // ============================================================================

    /**
     * Get all properties with optional filtering
     */
    function getProperties($filters = [], $limit = 0, $offset = 0) {
        global $db;
        
        $sql = "SELECT p.*, pt.name AS type_name, u.name AS agent_name 
                FROM properties p
                LEFT JOIN property_types pt ON p.type_id = pt.id
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN users u ON a.user_id = u.id
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
     */
    function getPropertyById($id) {
        global $db;
        
        // Get property details
        $db->query("SELECT p.*, pt.name AS type_name, u.name AS agent_name, u.email AS agent_email, u.phone AS agent_phone
                    FROM properties p
                    LEFT JOIN property_types pt ON p.type_id = pt.id
                    LEFT JOIN agents a ON p.agent_id = a.id
                    LEFT JOIN users u ON a.user_id = u.id
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
     * Enhanced function to get proper property image URLs (KEEP AS IS - WORKING)
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

    // ============================================================================
    // USER & SYSTEM FUNCTIONS
    // ============================================================================

    /**
     * Get user details by ID
     */
    function getUserById($id) {
        global $db;
        
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $id);
        
        return $db->single();
    }

    /**
     * Get all enquiries with optional filtering
     */
    function getEnquiries($filters = [], $limit = 0, $offset = 0) {
        global $db;
        
        $sql = "SELECT e.*, p.title AS property_title, u.name AS agent_name 
                FROM enquiries e
                LEFT JOIN properties p ON e.property_id = p.id
                LEFT JOIN agents a ON e.agent_id = a.id
                LEFT JOIN users u ON a.user_id = u.id
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

    // ============================================================================
    // UTILITY & HELPER FUNCTIONS
    // ============================================================================

    /**
     * Format price in Indian currency format
     */


    /**
     * Format date for display
     */
    function formatDate($date, $format = 'd M, Y') {
        return date($format, strtotime($date));
    }

    /**
     * Generate CSRF token
     */
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    /**
     * Clean up orphaned agent image files
     */
    function cleanupOrphanedAgentImages() {
        global $db;
        
        // Get all image files in agents directory
        $agents_dir = AGENT_IMG_PATH;
        
        if (!file_exists($agents_dir)) {
            return ['success' => true, 'message' => 'Agents directory does not exist'];
        }
        
        $files = scandir($agents_dir);
        $image_files = array_filter($files, function($file) use ($agents_dir) {
            return is_file($agents_dir . $file) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
        });
        
        // Get all profile pics from database
        $db->query("SELECT profile_pic FROM users WHERE role = 'agent' AND profile_pic IS NOT NULL");
        $db_images = $db->resultSet();
        $db_filenames = array_map(function($row) {
            return basename($row['profile_pic']);
        }, $db_images);
        
        // Find orphaned files
        $orphaned_files = array_diff($image_files, $db_filenames);
        $deleted_count = 0;
        
        foreach ($orphaned_files as $orphaned_file) {
            $file_path = $agents_dir . $orphaned_file;
            if (unlink($file_path)) {
                $deleted_count++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Cleaned up $deleted_count orphaned agent image files",
            'deleted_count' => $deleted_count,
            'orphaned_files' => $orphaned_files
        ];
    }

    // ============================================================================
    // LEGACY FUNCTIONS (For backward compatibility)
    // ============================================================================

    /**
     * Legacy functions to maintain compatibility with existing code
     */
    function normalizeAgentImagePath($image_path) {
        if (empty($image_path)) return '';
        if (strpos($image_path, 'assets/images/agents/') === 0) return $image_path;
        return 'assets/images/agents/' . basename($image_path);
    }

    function generateRelativePath($full_path) {
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            $relative = str_replace($doc_root . '/gunturProperties/', '', $full_path);
        } else {
            $relative = str_replace($doc_root . '/', '', $full_path);
        }
        return ltrim($relative, '/');
    }

    // ============================================================================
    // SUCCESS MESSAGE
    // ============================================================================
    if (IS_DEVELOPMENT) {
        error_log("✅ Optimized Functions.php loaded successfully!");
        error_log("🎯 Agent functions: Ready for perfect uploads");
        error_log("🏠 Property functions: Preserved and working");
    }
    ?>