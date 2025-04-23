<?php
/**
 * Property Features Management Page
 * Manage property features
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Property Features';

// Only admins can manage features
requireAdmin();

// Get database connection
$db = new Database();

// Handle add/edit/delete operations
$success = false;
$error = '';
$edit_id = null;
$edit_feature = null;

// Delete feature
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $feature_id = $_GET['delete'];
    
    // Check if feature is used in any properties
    $db->query("SELECT COUNT(*) as count FROM property_feature_mapping WHERE feature_id = :feature_id");
    $db->bind(':feature_id', $feature_id);
    $usage_count = $db->single()['count'];
    
    if ($usage_count > 0) {
        setFlashMessage('error', 'Cannot delete feature that is in use by properties.');
    } else {
        // Delete feature
        $db->query("DELETE FROM property_features WHERE id = :id");
        $db->bind(':id', $feature_id);
        
        if ($db->execute()) {
            setFlashMessage('success', 'Feature deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete feature.');
        }
    }
    
    redirect('features.php');
}

// Edit feature (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    
    $db->query("SELECT * FROM property_features WHERE id = :id");
    $db->bind(':id', $edit_id);
    $edit_feature = $db->single();
    
    if (!$edit_feature) {
        $error = 'Feature not found.';
        $edit_id = null;
    }
}

// Handle form submission (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feature_name = sanitize($_POST['name']);
    $feature_icon = sanitize($_POST['icon']);
    $action = $_POST['action'];
    
    if (empty($feature_name)) {
        $error = 'Feature name is required';
    } else {
        if ($action === 'add') {
            // Add new feature
            $db->query("INSERT INTO property_features (name, icon) VALUES (:name, :icon)");
            $db->bind(':name', $feature_name);
            $db->bind(':icon', $feature_icon);
            
            if ($db->execute()) {
                $success = true;
                setFlashMessage('success', 'Feature added successfully!');
                redirect('features.php');
            } else {
                $error = 'Failed to add feature.';
            }
        } elseif ($action === 'edit' && isset($_POST['feature_id']) && is_numeric($_POST['feature_id'])) {
            // Update feature
            $feature_id = $_POST['feature_id'];
            
            $db->query("UPDATE property_features SET name = :name, icon = :icon WHERE id = :id");
            $db->bind(':name', $feature_name);
            $db->bind(':icon', $feature_icon);
            $db->bind(':id', $feature_id);
            
            if ($db->execute()) {
                $success = true;
                setFlashMessage('success', 'Feature updated successfully!');
                redirect('features.php');
            } else {
                $error = 'Failed to update feature.';
            }
        }
    }
}

// Get all features with usage count
$db->query("SELECT pf.*, COUNT(pfm.property_id) as usage_count 
            FROM property_features pf
            LEFT JOIN property_feature_mapping pfm ON pf.id = pfm.feature_id
            GROUP BY pf.id
            ORDER BY pf.name ASC");
$features = $db->resultSet();

// Include header
include_once 'includes/header.php';
?>

<div class="card mb-3">
    <div class="card-header">
        <h2><?php echo $edit_id ? 'Edit Feature' : 'Add New Feature'; ?></h2>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form-inline">
            <input type="hidden" name="action" value="<?php echo $edit_id ? 'edit' : 'add'; ?>">
            <?php if ($edit_id): ?>
                <input type="hidden" name="feature_id" value="<?php echo $edit_id; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Feature Name</label>
                <input type="text" id="name" name="name" value="<?php echo $edit_feature ? htmlspecialchars($edit_feature['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="icon">Icon Class</label>
                <div class="input-with-icon">
                    <i class="fas fa-icons"></i>
                    <input type="text" id="icon" name="icon" value="<?php echo $edit_feature ? htmlspecialchars($edit_feature['icon']) : 'fas fa-check'; ?>" placeholder="fas fa-check">
                </div>
                <small class="form-text">Enter a Font Awesome icon class (e.g. fas fa-wifi)</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Update Feature' : 'Add Feature'; ?></button>
                <?php if ($edit_id): ?>
                    <a href="features.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Property Features</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Usage Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($features)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No features found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($features as $feature): ?>
                            <tr>
                                <td><?php echo $feature['id']; ?></td>
                                <td><i class="<?php echo htmlspecialchars($feature['icon'] ?: 'fas fa-check'); ?>"></i></td>
                                <td><?php echo htmlspecialchars($feature['name']); ?></td>
                                <td><?php echo $feature['usage_count']; ?></td>
                                <td class="table-actions">
                                    <a href="features.php?edit=<?php echo $feature['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($feature['usage_count'] == 0): ?>
                                        <a href="features.php?delete=<?php echo $feature['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm="Are you sure you want to delete this feature? This action cannot be undone.">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="feature-icons-preview card mt-3">
    <div class="card-header">
        <h3>Available Font Awesome Icons</h3>
    </div>
    <div class="card-body">
        <p>Here are some common icons you can use:</p>
        
        <div class="icon-grid">
            <div class="icon-item">
                <i class="fas fa-wifi"></i>
                <span>fas fa-wifi</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-swimming-pool"></i>
                <span>fas fa-swimming-pool</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-car"></i>
                <span>fas fa-car</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-dumbbell"></i>
                <span>fas fa-dumbbell</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-snowflake"></i>
                <span>fas fa-snowflake</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-tv"></i>
                <span>fas fa-tv</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-utensils"></i>
                <span>fas fa-utensils</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-couch"></i>
                <span>fas fa-couch</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-bed"></i>
                <span>fas fa-bed</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-bath"></i>
                <span>fas fa-bath</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-parking"></i>
                <span>fas fa-parking</span>
            </div>
            <div class="icon-item">
                <i class="fas fa-wheelchair"></i>
                <span>fas fa-wheelchair</span>
            </div>
        </div>
        
        <div class="more-icons">
            <p>For more icons, visit <a href="https://fontawesome.com/icons" target="_blank">Font Awesome Icons</a>.</p>
        </div>
    </div>
</div>

<style>
.icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.icon-item {
    text-align: center;
    padding: 15px;
    background-color: var(--bg-light);
    border-radius: var(--border-radius);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.icon-item i {
    font-size: 24px;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.icon-item span {
    font-size: 12px;
    color: var(--text-light);
    word-break: break-all;
}

.more-icons {
    text-align: center;
    margin-top: 20px;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?>