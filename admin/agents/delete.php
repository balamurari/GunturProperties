<?php
/**
 * Admin - Delete Agent (SECURE & COMPLETE VERSION)
 * Handles the secure deletion of an agent record and ALL associated data.
 * Includes complete file cleanup, CSRF protection, and comprehensive security checks.
 */

// --- Dependencies ---
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- FALLBACK CONSTANTS ---
if (!defined('AGENT_MAX_FILE_SIZE')) {
    define('AGENT_MAX_FILE_SIZE', 5000000);
}
if (!defined('AGENT_ALLOWED_EXTENSIONS')) {
    define('AGENT_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
}

// --- Security & Authentication ---
requireAdmin(); // Only admins can delete agents

// --- Initialize Database ---
$db = new Database();
$redirect_page = 'index.php';

// --- Security Function: Log deletion attempt for audit ---
function logDeletionAttempt($agent_id, $user_id, $action, $success = false, $error = null) {
    $log_message = sprintf(
        "[AGENT DELETE] User ID: %d | Agent ID: %d | Action: %s | Success: %s | Error: %s | IP: %s | Timestamp: %s",
        $user_id,
        $agent_id,
        $action,
        $success ? 'YES' : 'NO',
        $error ?? 'None',
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        date('Y-m-d H:i:s')
    );
    error_log($log_message);
}

// --- SECURITY CHECK 1: Validate Request Method and CSRF ---
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    logDeletionAttempt(0, getCurrentUserId(), 'INVALID_METHOD', false, 'Invalid request method');
    setFlashMessage('error', 'Invalid request method.');
    redirect($redirect_page);
    exit;
}

// For POST requests, validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        logDeletionAttempt(0, getCurrentUserId(), 'CSRF_FAILURE', false, 'Invalid CSRF token');
        setFlashMessage('error', 'Security token mismatch. Please try again.');
        redirect($redirect_page);
        exit;
    }
}

// --- SECURITY CHECK 2: Validate Agent ID ---
$agent_id_to_delete = null;

if (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
    $agent_id_to_delete = (int)$_GET['id'];
} elseif (isset($_POST['agent_id']) && is_numeric($_POST['agent_id']) && (int)$_POST['agent_id'] > 0) {
    $agent_id_to_delete = (int)$_POST['agent_id'];
} else {
    logDeletionAttempt(0, getCurrentUserId(), 'INVALID_ID', false, 'Invalid or missing agent ID');
    setFlashMessage('error', 'Invalid or missing Agent ID provided.');
    redirect($redirect_page);
    exit;
}

// --- SECURITY CHECK 3: Verify Agent Exists and Get Complete Data ---
$agent_data = null;
$user_data = null;

try {
    // Get complete agent and user data in one query
    $db->query("SELECT a.*, u.name, u.email, u.profile_pic, u.phone, u.created_at as user_created_at
                FROM agents a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.id = :id AND u.role = 'agent'");
    $db->bind(':id', $agent_id_to_delete);
    $agent_data = $db->single();

    if (!$agent_data) {
        logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'AGENT_NOT_FOUND', false, 'Agent not found');
        setFlashMessage('error', 'Agent with ID ' . $agent_id_to_delete . ' not found or not an agent.');
        redirect($redirect_page);
        exit;
    }

    $user_id_to_delete = $agent_data['user_id'];
    
    // Log successful agent verification
    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'AGENT_VERIFIED', true, null);

} catch (Exception $e) {
    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'DB_ERROR_VERIFY', false, $e->getMessage());
    setFlashMessage('error', 'An error occurred while verifying the agent.');
    redirect($redirect_page);
    exit;
}

// --- SECURITY CHECK 4: Check Property Dependencies ---
try {
    $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
    $db->bind(':agent_id', $agent_id_to_delete);
    $property_check_result = $db->single();
    $property_count = $property_check_result ? $property_check_result['count'] : 0;

    if ($property_count > 0) {
        logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'HAS_PROPERTIES', false, "Agent has {$property_count} properties");
        setFlashMessage('error', 'Cannot delete agent "' . htmlspecialchars($agent_data['name']) . '" because they have ' . $property_count . ' assigned properties. Please reassign or delete properties first.');
        redirect($redirect_page);
        exit;
    }
} catch (Exception $e) {
    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'DB_ERROR_PROPERTIES', false, $e->getMessage());
    setFlashMessage('error', 'An error occurred while checking agent properties.');
    redirect($redirect_page);
    exit;
}

// --- SECURITY CHECK 5: Additional Safety Checks ---
try {
    // Check for active enquiries
    $db->query("SELECT COUNT(*) as count FROM enquiries WHERE agent_id = :agent_id AND status = 'new'");
    $db->bind(':agent_id', $agent_id_to_delete);
    $enquiry_result = $db->single();
    $active_enquiries = $enquiry_result ? $enquiry_result['count'] : 0;

    if ($active_enquiries > 0) {
        logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'HAS_ACTIVE_ENQUIRIES', false, "Agent has {$active_enquiries} active enquiries");
        setFlashMessage('warning', 'Warning: Agent "' . htmlspecialchars($agent_data['name']) . '" has ' . $active_enquiries . ' active enquiries that will be deleted.');
    }
} catch (Exception $e) {
    // Log but don't stop - this is not critical
    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'ENQUIRY_CHECK_ERROR', false, $e->getMessage());
}

// --- CONFIRMATION STEP: Show confirmation page for GET requests ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page_title = 'Delete Agent Confirmation';
    include_once '../includes/header.php';
    ?>
    
    <div class="admin-container">
        <div class="admin-content">
            <div class="container-fluid">
                <!-- Header -->
                <div class="admin-content-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Delete Agent</h2>
                        <p class="text-muted mb-0">This action cannot be undone</p>
                    </div>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Agents
                    </a>
                </div>

                <!-- Confirmation Card -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-user-times me-2"></i>Confirm Agent Deletion</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <img src="<?php echo getAgentImageUrl($agent_data['profile_pic']); ?>" 
                                     alt="Agent Photo" 
                                     class="rounded-circle border mb-3" 
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <h4 class="text-danger mb-3"><?php echo htmlspecialchars($agent_data['name']); ?></h4>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <strong>Email:</strong> <?php echo htmlspecialchars($agent_data['email']); ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Phone:</strong> <?php echo htmlspecialchars($agent_data['phone'] ?? 'Not provided'); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <strong>Position:</strong> <?php echo htmlspecialchars($agent_data['position'] ?? 'Real Estate Agent'); ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Experience:</strong> <?php echo intval($agent_data['experience']); ?> years
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Joined:</strong> <?php echo date('M d, Y', strtotime($agent_data['user_created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Warning Information -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>The following data will be permanently deleted:</h6>
                            <ul class="mb-0">
                                <li><strong>User Account:</strong> Login credentials and profile information</li>
                                <li><strong>Agent Profile:</strong> Professional details, bio, and experience</li>
                                <li><strong>Profile Images:</strong> All uploaded photos and media files</li>
                                <li><strong>Specializations:</strong> Area expertise mappings</li>
                                <li><strong>Reviews & Ratings:</strong> All client reviews and feedback</li>
                                <li><strong>Awards & Certifications:</strong> Professional achievements</li>
                                <li><strong>Gallery Images:</strong> Portfolio and showcase photos</li>
                                <li><strong>Enquiries:</strong> All associated client inquiries (<?php echo $active_enquiries; ?> active)</li>
                            </ul>
                        </div>
                        
                        <!-- Confirmation Form -->
                        <form method="POST" action="delete.php" class="mt-4">
                            <input type="hidden" name="agent_id" value="<?php echo $agent_id_to_delete; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="confirm_delete" required>
                                <label class="form-check-label" for="confirm_delete">
                                    <strong>I understand that this action cannot be undone and will permanently delete all agent data.</strong>
                                </label>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-danger btn-lg" id="deleteBtn" disabled>
                                    <i class="fas fa-trash me-2"></i>Delete Agent Permanently
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmCheckbox = document.getElementById('confirm_delete');
        const deleteBtn = document.getElementById('deleteBtn');
        
        confirmCheckbox.addEventListener('change', function() {
            deleteBtn.disabled = !this.checked;
        });
        
        deleteBtn.addEventListener('click', function(e) {
            if (!confirm('Are you absolutely sure you want to delete this agent? This action CANNOT be undone!')) {
                e.preventDefault();
            } else {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
            }
        });
    });
    </script>

    <?php
    include_once '../includes/footer.php';
    exit;
}

// --- DELETION PROCESS: Execute complete deletion ---
try {
    // Start transaction
    if (!$db->beginTransaction()) {
        throw new Exception("Failed to start database transaction.");
    }

    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'DELETION_STARTED', true, null);

    // --- STEP 1: Delete all related data from ALL agent tables ---
    $related_tables_with_conditions = [
        'agent_reviews' => 'agent_id = :agent_id',
        'agent_specialization_mapping' => 'agent_id = :agent_id',
        'agent_certifications' => 'agent_id = :agent_id', 
        'agent_awards' => 'agent_id = :agent_id',
        'agent_gallery' => 'agent_id = :agent_id',
        'enquiries' => 'agent_id = :agent_id', // Delete all enquiries for this agent
        // Add more tables as needed
    ];

    foreach ($related_tables_with_conditions as $table => $condition) {
        $db->query("DELETE FROM {$table} WHERE {$condition}");
        $db->bind(':agent_id', $agent_id_to_delete);
        if (!$db->execute()) {
            throw new Exception("Failed to delete related data from table: {$table}");
        }
        
        // Log each table cleanup
        $affected_rows = $db->rowCount();
        if ($affected_rows > 0) {
            logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), "CLEANED_{$table}", true, "{$affected_rows} rows deleted");
        }
    }

    // --- STEP 2: Get all agent gallery images for deletion ---
    $gallery_images = [];
    try {
        $db->query("SELECT image_path FROM agent_gallery WHERE agent_id = :agent_id");
        $db->bind(':agent_id', $agent_id_to_delete);
        $gallery_results = $db->resultSet();
        $gallery_images = array_column($gallery_results, 'image_path');
    } catch (Exception $e) {
        // Log but continue - gallery might not exist
        logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'GALLERY_CLEANUP_ERROR', false, $e->getMessage());
    }

    // --- STEP 3: Delete the main agent record ---
    $db->query("DELETE FROM agents WHERE id = :id");
    $db->bind(':id', $agent_id_to_delete);
    if (!$db->execute()) {
        throw new Exception("Failed to delete agent record.");
    }

    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'AGENT_RECORD_DELETED', true, null);

    // --- STEP 4: Delete the user account ---
    $db->query("DELETE FROM users WHERE id = :user_id");
    $db->bind(':user_id', $user_id_to_delete);
    if (!$db->execute()) {
        throw new Exception("Failed to delete associated user account.");
    }

    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'USER_ACCOUNT_DELETED', true, null);

    // --- STEP 5: Commit transaction before file operations ---
    if (!$db->endTransaction()) {
        throw new Exception("Failed to commit database transaction.");
    }

    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'DB_TRANSACTION_COMMITTED', true, null);

    // --- STEP 6: Delete all associated files (outside transaction) ---
    $deleted_files = [];
    $failed_files = [];

    // Delete profile picture
    if (!empty($agent_data['profile_pic'])) {
        $profile_pic_paths = [
            // Try multiple possible paths
            $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $agent_data['profile_pic'],
            $_SERVER['DOCUMENT_ROOT'] . '/' . $agent_data['profile_pic'],
            AGENT_IMG_PATH . basename($agent_data['profile_pic'])
        ];

        foreach ($profile_pic_paths as $path) {
            if (file_exists($path)) {
                if (unlink($path)) {
                    $deleted_files[] = $path;
                    break; // File deleted successfully, stop trying other paths
                } else {
                    $failed_files[] = $path;
                }
            }
        }
    }

    // Delete gallery images
    foreach ($gallery_images as $gallery_image) {
        if (!empty($gallery_image)) {
            $gallery_paths = [
                $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $gallery_image,
                $_SERVER['DOCUMENT_ROOT'] . '/' . $gallery_image,
                AGENT_IMG_PATH . basename($gallery_image)
            ];

            foreach ($gallery_paths as $path) {
                if (file_exists($path)) {
                    if (unlink($path)) {
                        $deleted_files[] = $path;
                        break;
                    } else {
                        $failed_files[] = $path;
                    }
                }
            }
        }
    }

    // Log file deletion results
    if (!empty($deleted_files)) {
        logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'FILES_DELETED', true, count($deleted_files) . ' files deleted');
    }
    if (!empty($failed_files)) {
        logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'FILES_DELETE_FAILED', false, count($failed_files) . ' files failed to delete');
    }

    // --- SUCCESS: All operations completed ---
    $success_message = 'Agent "' . htmlspecialchars($agent_data['name']) . '" and all associated data deleted successfully!';
    if (!empty($deleted_files)) {
        $success_message .= ' (' . count($deleted_files) . ' files removed)';
    }
    
    setFlashMessage('success', $success_message);
    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'DELETION_COMPLETED', true, 'All operations successful');

} catch (Exception $e) {
    // Rollback database transaction
    $db->cancelTransaction();
    
    // Log the error
    logDeletionAttempt($agent_id_to_delete, getCurrentUserId(), 'DELETION_FAILED', false, $e->getMessage());
    
    // Set user-friendly error message
    setFlashMessage('error', 'Failed to delete agent due to a server error. No changes were made.');
}

// --- FINAL REDIRECT ---
redirect($redirect_page);
exit;
?>