<?php
/**
 * Admin Dashboard Page
 * Main landing page after login
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Dashboard';

// Get dashboard statistics
$db = new Database();

// Get total properties count
$db->query("SELECT COUNT(*) as count FROM properties");
$total_properties = $db->single()['count'];

// Get properties count by status
$db->query("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
$properties_by_status = $db->resultSet();

// Format properties by status
$status_counts = [];
foreach ($properties_by_status as $item) {
    $status_counts[$item['status']] = $item['count'];
}

// Get total agents count
$db->query("SELECT COUNT(*) as count FROM users WHERE role = 'agent'");
$total_agents = $db->single()['count'];

// Get total inquiries count
$db->query("SELECT COUNT(*) as count FROM enquiries");
$total_inquiries = $db->single()['count'];

// Get new inquiries count
$db->query("SELECT COUNT(*) as count FROM enquiries WHERE status = 'new'");
$new_inquiries = $db->single()['count'];

// Get recent properties
$db->query("SELECT p.*, pt.name AS type_name, u.name AS agent_name 
            FROM properties p
            LEFT JOIN property_types pt ON p.type_id = pt.id
            LEFT JOIN users u ON p.agent_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 5");
$recent_properties = $db->resultSet();

// Get recent inquiries
$db->query("SELECT e.*, p.title AS property_title 
            FROM enquiries e
            LEFT JOIN properties p ON e.property_id = p.id
            ORDER BY e.created_at DESC
            LIMIT 5");
$recent_inquiries = $db->resultSet();

// Include header
include_once 'includes/header.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-header">
            <div class="stat-card-title">TOTAL PROPERTIES</div>
            <div class="stat-card-icon">
                <i class="fas fa-home"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $total_properties; ?></div>
        <div class="stat-card-subtitle">
            <?php echo isset($status_counts['available']) ? $status_counts['available'] : 0; ?> available
        </div>
    </div>
    
    <div class="stat-card stat-card-success">
        <div class="stat-card-header">
            <div class="stat-card-title">TOTAL AGENTS</div>
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $total_agents; ?></div>
        <div class="stat-card-subtitle">Active property managers</div>
    </div>
    
    <div class="stat-card stat-card-warning">
        <div class="stat-card-header">
            <div class="stat-card-title">TOTAL INQUIRIES</div>
            <div class="stat-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $total_inquiries; ?></div>
        <div class="stat-card-subtitle">
            <?php echo $new_inquiries; ?> new inquiries
        </div>
    </div>
    
    <div class="stat-card stat-card-danger">
        <div class="stat-card-header">
            <div class="stat-card-title">SOLD PROPERTIES</div>
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo isset($status_counts['sold']) ? $status_counts['sold'] : 0; ?></div>
        <div class="stat-card-subtitle">
            Property sales completed
        </div>
    </div>
</div>

<!-- Recent Properties -->
<div class="card mb-3">
    <div class="card-header">
        <h2 class="mb-0">Recent Properties</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_properties)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No properties found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_properties as $property): ?>
                            <tr>
                                <td><?php echo $property['id']; ?></td>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                                <td><?php echo formatPrice($property['price']); ?></td>
                                <td><?php echo htmlspecialchars($property['agent_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="properties/edit.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="properties/index.php?view=<?php echo $property['id']; ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <a href="properties/index.php" class="btn btn-outline">View All Properties</a>
        </div>
    </div>
</div>

<!-- Recent Inquiries -->
<div class="card">
    <div class="card-header">
        <h2 class="mb-0">Recent Inquiries</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Property</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_inquiries)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No inquiries found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_inquiries as $inquiry): ?>
                            <tr>
                                <td><?php echo $inquiry['id']; ?></td>
                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                <td>
                                    <?php echo $inquiry['property_id'] ? htmlspecialchars($inquiry['property_title']) : 'General Inquiry'; ?>
                                </td>
                                <td><?php echo formatDate($inquiry['created_at']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                                        <?php 
                                            echo $inquiry['status'] == 'new' ? 'New' : 
                                                ($inquiry['status'] == 'in_progress' ? 'In Progress' : 'Closed'); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="inquiries/view.php?id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <a href="inquiries/index.php" class="btn btn-outline">View All Inquiries</a>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>