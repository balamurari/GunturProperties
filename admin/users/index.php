<?php
/**
 * Users Management Page
 * Manage admin users
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Manage Users';

// Only admins can manage users
requireAdmin();

// Get database connection
$db = new Database();

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Prevent admin from deleting their own account
    if ($user_id == $_SESSION['user_id']) {
        setFlashMessage('error', 'You cannot delete your own account.');
        redirect('index.php');
    }
    
    // Delete user
    $db->query("DELETE FROM users WHERE id = :id AND role = 'admin'");
    $db->bind(':id', $user_id);
    
    if ($db->execute()) {
        setFlashMessage('success', 'User deleted successfully!');
    } else {
        setFlashMessage('error', 'Failed to delete user.');
    }
    
    redirect('index.php');
}

// Handle activate/deactivate
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $user_id = $_GET['toggle_status'];
    
    // Prevent admin from deactivating their own account
    if ($user_id == $_SESSION['user_id']) {
        setFlashMessage('error', 'You cannot change the status of your own account.');
        redirect('index.php');
    }
    
    // Get current status
    $db->query("SELECT status FROM users WHERE id = :id AND role = 'admin'");
    $db->bind(':id', $user_id);
    $user = $db->single();
    
    if ($user) {
        $new_status = $user['status'] ? 0 : 1;
        
        // Update status
        $db->query("UPDATE users SET status = :status WHERE id = :id");
        $db->bind(':status', $new_status);
        $db->bind(':id', $user_id);
        
        if ($db->execute()) {
            $status_text = $new_status ? 'activated' : 'deactivated';
            setFlashMessage('success', "User {$status_text} successfully!");
        } else {
            setFlashMessage('error', 'Failed to update user status.');
        }
    } else {
        setFlashMessage('error', 'User not found.');
    }
    
    redirect('index.php');
}

// Get all admin users
$db->query("SELECT * FROM users WHERE role = 'admin' ORDER BY name ASC");
$users = $db->resultSet();

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2>Admin Users</h2>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Admin
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No admin users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i=1; foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td class="table-user">
                                    <img src="<?php echo !empty($user['profile_pic']) ? '../../' . $user['profile_pic'] : '../../assets/images/default-profile.jpg'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="current-user-badge">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td class="table-actions">
                                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?toggle_status=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-<?php echo $user['status'] ? 'ban' : 'check'; ?>"></i>
                                        </a>
                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm="Are you sure you want to delete this user? This action cannot be undone.">
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

<style>
.current-user-badge {
    display: inline-block;
    background-color: var(--bg-light);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    color: var(--text-light);
    margin-left: 6px;
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>