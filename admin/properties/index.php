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

// Handle filters and pagination
$filters = [];
$search = '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Sort order
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

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

// FIXED: Enhanced query to get properties with their primary images in one query
$sql = "SELECT p.*, pt.name AS type_name, u.name AS agent_name,
               (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
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

// Add sort order
switch ($sort_order) {
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$sql .= " LIMIT :limit OFFSET :offset";

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
<?php if (isLoggedIn() && hasRole(['admin'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-shield-alt"></i> 
        <strong>Administrator Mode:</strong> You have permission to delete properties.
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>
<!-- Property Filters and Search -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="filter-form">
            <div class="form-row">
                <!-- FIXED: Added missing search input -->
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search properties...">
                </div>
                
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
                        <option value="buy" <?php echo isset($filters['status']) && $filters['status'] == 'buy' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo isset($filters['status']) && $filters['status'] == 'rent' ? 'selected' : ''; ?>>For Rent</option>
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
                
                <div class="form-group">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort">
                        <option value="newest" <?php echo $sort_order == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort_order == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_high" <?php echo $sort_order == 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="price_low" <?php echo $sort_order == 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
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
    <div class="card-header">
        <div class="card-header-actions row justify-content-between w-100">
            <div class="text-start">
                <h2 class="mb-0">Properties (<?php echo $total_count; ?>)</h2>
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
                        <th>Phone Number</th>
                        <th>Address</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="12" class="text-center">No properties found.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1;
                         foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td class="table-image">
                                    <?php
                                    // FIXED: Use our enhanced getPropertyImageUrl function
                                    $image_url = getPropertyImageUrl($property['primary_image']);
                                    ?>
                                    <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="fixed-size-image" onerror="this.src='<?php echo ROOT_URL; ?>assets/images/no-image.jpg'">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($property['title']); ?></strong>
                                    <?php if (!empty($property['city'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($property['city']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                                <td>
                                    <strong><?php echo formatIndianPrice($property['price']); ?></strong>
                                    <?php if ($property['status'] == 'rent'): ?>
                                        <br><small class="text-muted">per month</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($property['phone_number']) ? htmlspecialchars($property['phone_number']) : '-'; ?></td>
                                <td>
                                    <?php echo htmlspecialchars(substr($property['address'], 0, 30)); ?>
                                    <?php if (strlen($property['address']) > 30): ?>...<?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($property['agent_name'] ?: 'Not Assigned'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($property['featured']): ?>
                                        <i class="fas fa-star text-warning" title="Featured"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-muted" title="Not Featured"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                              <td class="table-actions">
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                class="btn btn-sm btn-info mb-1" 
                                title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="edit.php?id=<?php echo $property['id']; ?>" 
                                class="btn btn-sm btn-primary mb-1" 
                                title="Edit Property">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php 
                                // CRITICAL SECURITY: Only show delete button to admins
                                if (isLoggedIn() && hasRole(['admin'])): 
                                ?>
                                    <a href="delete.php?id=<?php echo $property['id']; ?>" 
                                    class="btn btn-sm btn-danger mb-1" 
                                    title="Delete Property (Admin Only)">
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
    <div class="card-footer">
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort_order; ?>&<?php echo http_build_query($filters); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php 
                // Show pagination numbers with ellipsis for better UX
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=1&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort_order; ?>&<?php echo http_build_query($filters); ?>">1</a>
                    <?php if ($start_page > 2): ?><span>...</span><?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort_order; ?>&<?php echo http_build_query($filters); ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?><span>...</span><?php endif; ?>
                    <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort_order; ?>&<?php echo http_build_query($filters); ?>"><?php echo $total_pages; ?></a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort_order; ?>&<?php echo http_build_query($filters); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

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
        border: 1px solid #ddd;
    }

    /* Status badges */
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .status-buy {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-rent {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    
    .status-sold {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status-rented {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    /* Pagination improvements */
    .pagination span {
        padding: 8px 12px;
        color: #6c757d;
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
<style>
/* Admin-only elements styling */
.admin-only {
    border-left: 3px solid #dc3545;
    padding-left: 10px;
}

/* Make delete buttons more obvious for admins */
.btn-danger[title*="Admin"] {
    position: relative;
}

.btn-danger[title*="Admin"]:after {
    content: "⚡";
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 10px;
    color: #ffc107;
}

/* Security indicator */
.security-badge {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 1050;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
}
</style>

<!-- Add security indicator if user is admin -->
<?php if (isLoggedIn() && hasRole(['admin'])): ?>
    <div class="security-badge">
        <i class="fas fa-shield-alt"></i> ADMIN
    </div>
<?php endif; ?>