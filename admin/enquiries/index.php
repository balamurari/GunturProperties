<?php
/**
 * enquiries Index Page
 * Lists all enquiries with filtering and search
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Manage Enquiries';

// Get database connection
$db = new Database();

// Handle filters and pagination
$filters = [];
$search = '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

if (isset($_GET['property']) && is_numeric($_GET['property'])) {
    $filters['property_id'] = $_GET['property'];
}

if (isset($_GET['agent']) && is_numeric($_GET['agent'])) {
    $filters['agent_id'] = $_GET['agent'];
}

// Get total enquiries count for pagination
$count_sql = "SELECT COUNT(*) as total FROM enquiries WHERE 1=1";

if (!empty($search)) {
    $count_sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR message LIKE :search)";
}

// Add filters to count
if (!empty($filters)) {
    foreach ($filters as $key => $value) {
        if ($value !== null && $value !== '') {
            $count_sql .= " AND $key = :$key";
        }
    }
}

$db->query($count_sql);

// Bind search parameter
if (!empty($search)) {
    $db->bind(':search', "%$search%");
}

// Bind filter parameters
if (!empty($filters)) {
    foreach ($filters as $key => $value) {
        if ($value !== null && $value !== '') {
            $db->bind(":$key", $value);
        }
    }
}

$total_count = $db->single()['total'];
$total_pages = ceil($total_count / $limit);

// Get enquiries with filters
$sql = "SELECT e.*, p.title AS property_title, u.name AS agent_name 
        FROM enquiries e
        LEFT JOIN properties p ON e.property_id = p.id
        LEFT JOIN users u ON e.agent_id = u.id
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (e.name LIKE :search OR e.email LIKE :search OR e.phone LIKE :search OR e.message LIKE :search)";
}

// Add filters
if (!empty($filters)) {
    foreach ($filters as $key => $value) {
        if ($value !== null && $value !== '') {
            $sql .= " AND e.$key = :$key";
        }
    }
}

$sql .= " ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset";

$db->query($sql);

// Bind search parameter
if (!empty($search)) {
    $db->bind(':search', "%$search%");
}

// Bind filter parameters
if (!empty($filters)) {
    foreach ($filters as $key => $value) {
        if ($value !== null && $value !== '') {
            $db->bind(":$key", $value);
        }
    }
}

$db->bind(':limit', $limit, PDO::PARAM_INT);
$db->bind(':offset', $offset, PDO::PARAM_INT);

$enquiries = $db->resultSet();

// Get properties for filter dropdown
$db->query("SELECT id, title FROM properties ORDER BY title ASC");
$properties = $db->resultSet();

// Get agents for filter dropdown
$agents = getAgents();

// Include header
include_once '../includes/header.php';
?>

<!-- enquiry Filters and Search -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search enquiries...">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo isset($filters['status']) && $filters['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="in_progress" <?php echo isset($filters['status']) && $filters['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="closed" <?php echo isset($filters['status']) && $filters['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="property">Property</label>
                    <select id="property" name="property">
                        <option value="">All Properties</option>
                        <option value="0" <?php echo isset($filters['property_id']) && $filters['property_id'] === '0' ? 'selected' : ''; ?>>General enquiries</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?php echo $property['id']; ?>" <?php echo isset($filters['property_id']) && $filters['property_id'] == $property['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($property['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="agent">Assigned Agent</label>
                    <select id="agent" name="agent">
                        <option value="">All Agents</option>
                        <option value="0" <?php echo isset($filters['agent_id']) && $filters['agent_id'] === '0' ? 'selected' : ''; ?>>Unassigned</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" <?php echo isset($filters['agent_id']) && $filters['agent_id'] == $agent['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agent['name']); ?>
                            </option>
                        <?php endforeach; ?>
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

<!-- enquiries List -->
<div class="card">
    <div class="card-header">
        <h2 class="mb-0">enquiries</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Subject</th>
                        <th>Property</th>
                        <th>Agent</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enquiries)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No enquiries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i=1; foreach ($enquiries as $enquiry): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($enquiry['email']); ?></div>
                                    <div><?php echo htmlspecialchars($enquiry['phone']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                                <td>
                                    <?php if ($enquiry['property_id']): ?>
                                        <a href="../properties/edit.php?id=<?php echo $enquiry['property_id']; ?>">
                                            <?php echo htmlspecialchars($enquiry['property_title']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">General enquiry</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($enquiry['agent_id']): ?>
                                        <?php echo htmlspecialchars($enquiry['agent_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($enquiry['created_at']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $enquiry['status']; ?>">
                                        <?php 
                                            echo $enquiry['status'] == 'new' ? 'New' : 
                                                ($enquiry['status'] == 'in_progress' ? 'In Progress' : 'Closed'); 
                                        ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="view.php?id=<?php echo $enquiry['id']; ?>" class="btn btn-sm btn-primary">
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
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&<?php echo http_build_query($filters); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&<?php echo http_build_query($filters); ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&<?php echo http_build_query($filters); ?>">
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