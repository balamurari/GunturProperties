<?php
/**
 * Properties Index Page
 * Lists all properties with filtering and search
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Manage Properties';

// Get database connection
$db = new Database();

// Get property types for filter
$db->query("SELECT * FROM property_types ORDER BY name ASC");
$property_types = $db->resultSet();

// Get agents for filter
$agents = getAgents();

// Handle property deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $property_id = $_GET['delete'];
    
    if (deleteProperty($property_id)) {
        setFlashMessage('success', 'Property deleted successfully!');
    } else {
        setFlashMessage('error', 'Failed to delete property.');
    }
    
    redirect($_SERVER['PHP_SELF']);
}

// Handle filters and pagination
$filters = [];
$search = '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
}

if (isset($_GET['type']) && is_numeric($_GET['type'])) {
    $filters['type_id'] = $_GET['type'];
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

if (isset($_GET['agent']) && is_numeric($_GET['agent'])) {
    $filters['agent_id'] = $_GET['agent'];
}

if (isset($_GET['featured'])) {
    $filters['featured'] = 1;
}

// Get total properties count for pagination
$count_sql = "SELECT COUNT(*) as total FROM properties WHERE 1=1";

if (!empty($search)) {
    $count_sql .= " AND (title LIKE :search OR description LIKE :search OR address LIKE :search)";
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

// Get properties with filters
$sql = "SELECT p.*, pt.name AS type_name, u.name AS agent_name 
        FROM properties p
        LEFT JOIN property_types pt ON p.type_id = pt.id
        LEFT JOIN agents a ON p.agent_id = a.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (p.title LIKE :search OR p.description LIKE :search OR p.address LIKE :search)";
}

// Add filters
if (!empty($filters)) {
    foreach ($filters as $key => $value) {
        if ($value !== null && $value !== '') {
            $sql .= " AND p.$key = :$key";
        }
    }
}

$sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

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

$properties = $db->resultSet();

// Include header
include_once '../includes/header.php';
?>

<!-- Property Filters and Search -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
           
                <div class="form-group">
                    <label for="type">Property Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo isset($filters['type_id']) && $filters['type_id'] == $type['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="available" <?php echo isset($filters['status']) && $filters['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="sold" <?php echo isset($filters['status']) && $filters['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="pending" <?php echo isset($filters['status']) && $filters['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="rented" <?php echo isset($filters['status']) && $filters['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="agent">Agent</label>
                    <select id="agent" name="agent">
                        <option value="">All Agents</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" <?php echo isset($filters['agent_id']) && $filters['agent_id'] == $agent['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="featured" name="featured" value="1" <?php echo isset($filters['featured']) ? 'checked' : ''; ?>>
                    <label for="featured">Featured Only</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Properties List -->
<div class="card">
    <div class="card-header ">
    <div class="card-header-actions row justify-content-between w-100">
    <div class="text-start">
        <h2 class="mb-0">Properties</h2>
        
    </div>
    <div class="text-end">
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Property
        </a>
    </div>
</div>

    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Address</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="10" class="text-center">No properties found.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i=1;
                         foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td class="table-image">
                                    <?php
                                    // Get primary image
                                    $db->query("SELECT image_path FROM property_images WHERE property_id = :id AND is_primary = 1 LIMIT 1");
                                    $db->bind(':id', $property['id']);
                                    $image = $db->single();
                                    
                                    // Use proper URL path for image
                                    if ($image && !empty($image['image_path'])) {
                                        // Extract filename from path
                                        $filename = basename($image['image_path']);
                                        // Create proper URL using constant
                                        $image_path = PROPERTY_IMAGES_URL . $filename;
                                    } else {
                                        $image_path = DEFAULT_IMAGE_URL;
                                    }
                                    ?>
                                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="fixed-size-image">
                                </td>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                                <td><?php echo formatPrice($property['price']); ?></td>
                                <td><?php echo htmlspecialchars($property['address']); ?></td>
                                <td><?php echo htmlspecialchars($property['agent_name'] ?: 'Not Assigned'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($property['featured']): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="edit.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-secondary mb-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger confirm-action mb-1" data-confirm="Are you sure you want to delete this property? This action cannot be undone.">
                                        <i class="fas fa-trash"></i>
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

<style>
/* Fixed size property images in tables */
.table-image {
    width: 120px;
    height: 90px;
    text-align: center;
}

.fixed-size-image {
    width: 100px;
    height: 75px;
    object-fit: cover;
    border-radius: 4px;
    max-width: 100%;
}

/* Responsive adjustments */
@media screen and (max-width: 1200px) {
    .table-image {
        width: 100px;
        height: 80px;
    }
    
    .fixed-size-image {
        width: 90px;
        height: 65px;
    }
}

@media screen and (max-width: 992px) {
    .table-image {
        width: 90px;
        height: 70px;
    }
    
    .fixed-size-image {
        width: 80px;
        height: 60px;
    }
}

@media screen and (max-width: 768px) {
    .table-image {
        width: 80px;
        height: 60px;
    }
    
    .fixed-size-image {
        width: 70px;
        height: 50px;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    table {
        min-width: 600px;
    }
}

@media screen and (max-width: 576px) {
    .table-image {
        width: 60px;
        height: 50px;
    }
    
    .fixed-size-image {
        width: 50px;
        height: 40px;
    }
}
</style>

<script>
// Confirm action for delete buttons
document.addEventListener('DOMContentLoaded', function() {
    const confirmButtons = document.querySelectorAll('.confirm-action');
    
    confirmButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>