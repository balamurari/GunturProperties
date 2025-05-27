<?php
/**
 * Secure Delete Property
 * Only admins can delete properties with proper security measures
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Enable debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

function debug_log($message) {
    error_log('[SECURE DELETE] ' . $message);
}

// ===========================================
// SECURITY CHECKS - CRITICAL
// ===========================================

// 1. Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to access this page.');
    redirect('../login.php');
}

// 2. Check if user has admin role (CRITICAL SECURITY CHECK)
if (!hasRole(['admin'])) {
    setFlashMessage('error', 'Access denied. Only administrators can delete properties.');
    debug_log("Unauthorized deletion attempt by user ID: " . getCurrentUserId());
    redirect('index.php');
}

// 3. Rate limiting (prevent spam deletions)

if (!isset($_SESSION['last_delete_attempt'])) {
    $_SESSION['last_delete_attempt'] = 0;
}

if (time() - $_SESSION['last_delete_attempt'] < 2) {
    setFlashMessage('error', 'Please wait before attempting another deletion.');
    redirect('index.php');
}

$_SESSION['last_delete_attempt'] = time();

// Get database connection
$db = new Database();

// ===========================================
// HANDLE POST REQUEST (Actual Deletion)
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 4. CSRF Protection (CRITICAL)
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        setFlashMessage('error', 'Security token mismatch. Please try again.');
        debug_log("CSRF token mismatch - potential attack detected");
        redirect('index.php');
    }
    
    // 5. Validate property ID
    if (!isset($_POST['property_id']) || !is_numeric($_POST['property_id'])) {
        setFlashMessage('error', 'Invalid property ID.');
        redirect('index.php');
    }
    
    $property_id = (int)$_POST['property_id'];
    
    // 6. Double-check confirmation
    if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] !== 'yes') {
        setFlashMessage('error', 'Deletion not confirmed.');
        redirect('index.php');
    }
    
    try {
        // Verify property exists and get details for logging
        $db->query("SELECT id, title, agent_id FROM properties WHERE id = :id");
        $db->bind(':id', $property_id);
        $property = $db->single();
        
        if (!$property) {
            setFlashMessage('error', 'Property not found.');
            redirect('index.php');
        }
        
        // Start transaction
        $db->beginTransaction();
        
        // Get all image paths BEFORE deletion
        $db->query("SELECT id, image_path FROM property_images WHERE property_id = :property_id");
        $db->bind(':property_id', $property_id);
        $images = $db->resultSet();
        
        debug_log("Starting deletion of property ID: $property_id with " . count($images) . " images");
        
        // Delete in correct order (foreign key constraints)
        
        // 1. Delete property feature mappings
        $db->query("DELETE FROM property_feature_mapping WHERE property_id = :property_id");
        $db->bind(':property_id', $property_id);
        $db->execute();
        
        // 2. Delete property images records
        $db->query("DELETE FROM property_images WHERE property_id = :property_id");
        $db->bind(':property_id', $property_id);
        $db->execute();
        
        // 3. Update enquiries (don't delete them, just unlink)
        $db->query("UPDATE enquiries SET property_id = NULL WHERE property_id = :property_id");
        $db->bind(':property_id', $property_id);
        $db->execute();
        
        // 4. Delete the property itself
        $db->query("DELETE FROM properties WHERE id = :id");
        $db->bind(':id', $property_id);
        $deleted = $db->execute();
        
        if (!$deleted || $db->rowCount() === 0) {
            throw new Exception("Failed to delete property from database");
        }
        
        // Commit database transaction
        $db->endTransaction();
        
        debug_log("Database deletion successful for property ID: $property_id");
        
        // ===========================================
        // DELETE PHYSICAL FILES (After DB success)
        // ===========================================
        
        $deleted_files = [];
        $failed_files = [];
        
        foreach ($images as $image) {
            $relative_path = getRelativePath($image['image_path']);
            
            // Build correct file path
            if ($_SERVER['HTTP_HOST'] == 'localhost') {
                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . ltrim($relative_path, '/');
            } else {
                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($relative_path, '/');
            }
            
            debug_log("Attempting to delete file: $file_path");
            
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    $deleted_files[] = basename($file_path);
                    debug_log("Successfully deleted file: $file_path");
                } else {
                    $failed_files[] = basename($file_path);
                    debug_log("Failed to delete file: $file_path");
                }
            } else {
                debug_log("File does not exist: $file_path");
            }
        }
        
        // ===========================================
        // AUDIT LOGGING (Security/Compliance)
        // ===========================================
        
        $user_id = getCurrentUserId();
        $user_name = getCurrentUserName();
        
        // Log to database (if you have audit table)
        // $db->query("INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, created_at) 
        //            VALUES (:user_id, 'DELETE', 'property', :property_id, :details, NOW())");
        
        // Log to file
        $audit_message = "Property deleted: ID={$property_id}, Title='{$property['title']}', User={$user_name} (ID: {$user_id}), Files deleted: " . count($deleted_files) . ", Files failed: " . count($failed_files);
        debug_log("AUDIT: " . $audit_message);
        
        // Success message
        $success_message = "Property \"" . htmlspecialchars($property['title']) . "\" has been permanently deleted.";
        
        if (!empty($deleted_files)) {
            $success_message .= " Removed " . count($deleted_files) . " image files.";
        }
        
        if (!empty($failed_files)) {
            $success_message .= " Warning: " . count($failed_files) . " image files could not be removed.";
        }
        
        setFlashMessage('success', $success_message);
        
    } catch (Exception $e) {
        // Rollback on any error
        $db->cancelTransaction();
        
        $error_message = "Failed to delete property: " . $e->getMessage();
        debug_log("ERROR: " . $error_message);
        setFlashMessage('error', $error_message);
    }
    
    redirect('index.php');
}

// ===========================================
// HANDLE GET REQUEST (Show Confirmation)
// ===========================================

// Validate property ID for GET request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid property ID.');
    redirect('index.php');
}

$property_id = (int)$_GET['id'];

// Get property details for confirmation page
$db->query("SELECT p.*, pt.name as type_name, u.name as agent_name,
           (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count,
           (SELECT COUNT(*) FROM enquiries WHERE property_id = p.id) as enquiry_count
           FROM properties p 
           LEFT JOIN property_types pt ON p.type_id = pt.id 
           LEFT JOIN agents a ON p.agent_id = a.id
           LEFT JOIN users u ON a.user_id = u.id
           WHERE p.id = :id");
$db->bind(':id', $property_id);
$property = $db->single();

if (!$property) {
    setFlashMessage('error', 'Property not found.');
    redirect('index.php');
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function (same as your original)
function getRelativePath($path) {
    if (strpos($path, 'public_html') !== false) {
        $parts = explode('public_html/', $path, 2);
        if (count($parts) > 1) {
            return $parts[1];
        }
    }
    
    if (strpos($path, '/home/') === 0) {
        if (preg_match('/assets\/images\/.*$/', $path, $matches)) {
            return $matches[0];
        }
    }
    
    if (strpos($path, 'assets/images/') === 0) {
        return $path;
    }
    
    return basename($path);
}

$page_title = 'Delete Property - Confirmation Required';
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        DANGER: Confirm Property Deletion
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Critical Warning -->
                    <div class="alert alert-danger border-2">
                        <h5><i class="fas fa-skull-crossbones"></i> <strong>PERMANENT DELETION WARNING</strong></h5>
                        <p class="mb-0">This action <strong>CANNOT BE UNDONE</strong>. All data will be permanently destroyed.</p>
                    </div>
                    
                    <!-- Property Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-home"></i> Property Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td><?php echo htmlspecialchars($property['title']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Price:</strong></td>
                                    <td><?php echo formatIndianPrice($property['price']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td><?php echo htmlspecialchars($property['address']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>City:</strong></td>
                                    <td><?php echo htmlspecialchars($property['city']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $property['status']; ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-database"></i> Data Impact</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Images:</strong></td>
                                    <td><span class="badge badge-info"><?php echo $property['image_count']; ?> files</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Enquiries:</strong></td>
                                    <td><span class="badge badge-warning"><?php echo $property['enquiry_count']; ?> will be unlinked</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Agent:</strong></td>
                                    <td><?php echo htmlspecialchars($property['agent_name'] ?: 'None assigned'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($property['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($property['updated_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- What Will Be Deleted -->
                    <div class="mt-4">
                        <h5><i class="fas fa-trash-alt"></i> What Will Be Permanently Deleted:</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Property information</span>
                                        <i class="fas fa-check text-danger"></i>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>All property images (<?php echo $property['image_count']; ?> files)</span>
                                        <i class="fas fa-check text-danger"></i>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Property features & amenities</span>
                                        <i class="fas fa-check text-danger"></i>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Enquiries will be unlinked</span>
                                        <i class="fas fa-unlink text-warning"></i>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>No backup will be created</span>
                                        <i class="fas fa-exclamation-triangle text-danger"></i>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Action will be logged</span>
                                        <i class="fas fa-clipboard-list text-info"></i>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <form method="POST" id="deleteForm" class="d-inline">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="confirm_delete" value="yes">
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                            <label class="form-check-label text-danger" for="confirmCheck">
                                <strong>I understand that this action cannot be undone and will permanently delete all property data.</strong>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-danger btn-lg" id="deleteBtn" disabled>
                            <i class="fas fa-skull-crossbones"></i> 
                            PERMANENTLY DELETE PROPERTY
                        </button>
                    </form>
                    
                    <a href="index.php" class="btn btn-success btn-lg ml-3">
                        <i class="fas fa-shield-alt"></i> 
                        Keep Property Safe
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheck = document.getElementById('confirmCheck');
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteForm = document.getElementById('deleteForm');
    
    // Enable/disable delete button based on checkbox
    confirmCheck.addEventListener('change', function() {
        deleteBtn.disabled = !this.checked;
    });
    
    // Final confirmation before submit
    deleteForm.addEventListener('submit', function(e) {
        if (!confirm('FINAL WARNING: Are you absolutely sure you want to delete this property?\n\nThis action CANNOT be undone!')) {
            e.preventDefault();
        }
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>