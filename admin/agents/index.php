<?php
/**
 * Agents Index Page
 * Lists all agents with filtering and search
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Manage Agents';

// Get database connection
$db = new Database();

// Handle agent deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $agent_id = $_GET['delete'];
    
    // Check if agent has properties assigned
    $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
    $db->bind(':agent_id', $agent_id);
    $property_count = $db->single()['count'];
    
    if ($property_count > 0) {
        setFlashMessage('error', 'Cannot delete agent with assigned properties. Please reassign properties first.');
    } else {
        // Delete agent
        $db->query("DELETE FROM users WHERE id = :id AND role = 'agent'");
        $db->bind(':id', $agent_id);
        
        if ($db->execute()) {
            setFlashMessage('success', 'Agent deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete agent.');
        }
    }
    
    redirect($_SERVER['PHP_SELF']);
}

// Handle filters and pagination
$search = '';
$status_filter = '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
}

if (isset($_GET['status']) && in_array($_GET['status'], ['1', '0'])) {
    $status_filter = $_GET['status'];
}

// Get total agents count for pagination
$count_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'agent'";

if (!empty($search)) {
    $count_sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
}

if ($status_filter !== '') {
    $count_sql .= " AND status = :status";
}

$db->query($count_sql);

// Bind search parameter
if (!empty($search)) {
    $db->bind(':search', "%$search%");
}

// Bind status parameter
if ($status_filter !== '') {
    $db->bind(':status', $status_filter);
}

$total_count = $db->single()['total'];
$total_pages = ceil($total_count / $limit);

// Get agents with filters
$sql = "SELECT id, name, email, phone, profile_pic, status, created_at FROM users 
        WHERE role = 'agent'";

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
}

if ($status_filter !== '') {
    $sql .= " AND status = :status";
}

$sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

$db->query($sql);

// Bind search parameter
if (!empty($search)) {
    $db->bind(':search', "%$search%");
}

// Bind status parameter
if ($status_filter !== '') {
    $db->bind(':status', $status_filter);
}

$db->bind(':limit', $limit, PDO::PARAM_INT);
$db->bind(':offset', $offset, PDO::PARAM_INT);

$agents = $db->resultSet();

// Include header
include_once '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/agents.css">
<!-- Agent Filters and Search -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search agents...">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Agents List -->
<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2 class="mb-0">Agents</h2>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Agent
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
                        <th>Phone</th>
                        <th>Properties</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agents)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No agents found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($agents as $agent): ?>
                            <tr>
                                <td><?php echo $agent['id']; ?></td>
                                <td class="table-user">
                                    <img src="<?php echo !empty($agent['profile_pic']) ? '../' . $agent['profile_pic'] : '../assets/images/default-profile.jpg'; ?>" alt="<?php echo htmlspecialchars($agent['name']); ?>">
                                    <?php echo htmlspecialchars($agent['name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($agent['email']); ?></td>
                                <td><?php echo htmlspecialchars($agent['phone']); ?></td>
                                <td>
                                    <?php
                                    // Get property count
                                    $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
                                    $db->bind(':agent_id', $agent['id']);
                                    $property_count = $db->single()['count'];
                                    
                                    echo $property_count . ' properties';
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $agent['status'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $agent['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($agent['created_at']); ?></td>
                                <td class="table-actions">
                                    <a href="edit.php?id=<?php echo $agent['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($property_count === 0): ?>
                                        <a href="?delete=<?php echo $agent['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm="Are you sure you want to delete this agent? This action cannot be undone.">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="../agents.html?id=<?php echo $agent['id']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>