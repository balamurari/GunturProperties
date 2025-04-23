<?php
/**
 * Property Types Management Page
 * Manage property types
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Property Types';

// Only admins can manage property types
requireAdmin();

// Get database connection
$db = new Database();

// Handle add/edit/delete operations
$success = false;
$error = '';
$edit_id = null;
$edit_type = null;

// Delete property type
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $type_id = $_GET['delete'];
    
    // Check if type is used in any properties
    $db->query("SELECT COUNT(*) as count FROM properties WHERE type_id = :type_id");
    $db->bind(':type_id', $type_id);
    $property_count = $db->single()['count'];
    
    if ($property_count > 0) {
        setFlashMessage('error', 'Cannot delete property type that is in use. Reassign properties first.');
    } else {
        // Delete property type
        $db->query("DELETE FROM property_types WHERE id = :id");
        $db->bind(':id', $type_id);
        
        if ($db->execute()) {
            setFlashMessage('success', 'Property type deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete property type.');
        }
    }
    
    redirect('property-types.php');
}

// Edit property type (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    
    $db->query("SELECT * FROM property_types WHERE id = :id");
    $db->bind(':id', $edit_id);
    $edit_type = $db->single();
    
    if (!$edit_type) {
        $error = 'Property type not found.';
        $edit_id = null;
    }
}

// Handle form submission (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = sanitize($_POST['name']);
    $type_description = sanitize($_POST['description']);
    $action = $_POST['action'];
    
    if (empty($type_name)) {
        $error = 'Property type name is required';
    } else {
        if ($action === 'add') {
            // Add new property type
            $db->query("INSERT INTO property_types (name, description) VALUES (:name, :description)");
            $db->bind(':name', $type_name);
            $db->bind(':description', $type_description);
            
            if ($db->execute()) {
                $success = true;
                setFlashMessage('success', 'Property type added successfully!');
                redirect('property-types.php');
            } else {
                $error = 'Failed to add property type.';
            }
        } elseif ($action === 'edit' && isset($_POST['type_id']) && is_numeric($_POST['type_id'])) {
            // Update property type
            $type_id = $_POST['type_id'];
            
            $db->query("UPDATE property_types SET name = :name, description = :description WHERE id = :id");
            $db->bind(':name', $type_name);
            $db->bind(':description', $type_description);
            $db->bind(':id', $type_id);
            
            if ($db->execute()) {
                $success = true;
                setFlashMessage('success', 'Property type updated successfully!');
                redirect('property-types.php');
            } else {
                $error = 'Failed to update property type.';
            }
        }
    }
}

// Get all property types
$db->query("SELECT pt.*, COUNT(p.id) as property_count 
            FROM property_types pt
            LEFT JOIN properties p ON pt.id = p.type_id
            GROUP BY pt.id
            ORDER BY pt.name ASC");
$property_types = $db->resultSet();

// Include header
include_once 'includes/header.php';
?>

<div class="card mb-3">
    <div class="card-header">
        <h2><?php echo $edit_id ? 'Edit Property Type' : 'Add New Property Type'; ?></h2>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form-inline">
            <input type="hidden" name="action" value="<?php echo $edit_id ? 'edit' : 'add'; ?>">
            <?php if ($edit_id): ?>
                <input type="hidden" name="type_id" value="<?php echo $edit_id; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Type Name</label>
                <input type="text" id="name" name="name" value="<?php echo $edit_type ? htmlspecialchars($edit_type['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" id="description" name="description" value="<?php echo $edit_type ? htmlspecialchars($edit_type['description']) : ''; ?>" class="form-control-lg">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Update Type' : 'Add Type'; ?></button>
                <?php if ($edit_id): ?>
                    <a href="property-types.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Property Types</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Properties</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($property_types)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No property types found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($property_types as $type): ?>
                            <tr>
                                <td><?php echo $type['id']; ?></td>
                                <td><?php echo htmlspecialchars($type['name']); ?></td>
                                <td><?php echo htmlspecialchars($type['description'] ?: 'No description'); ?></td>
                                <td>
                                    <?php echo $type['property_count']; ?>
                                    <?php if ($type['property_count'] > 0): ?>
                                        <a href="properties/index.php?type=<?php echo $type['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="property-types.php?edit=<?php echo $type['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($type['property_count'] === 0): ?>
                                        <a href="property-types.php?delete=<?php echo $type['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm="Are you sure you want to delete this property type? This action cannot be undone.">
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

<?php
// Include footer
include_once 'includes/footer.php';
?>