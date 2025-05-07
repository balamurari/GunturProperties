<?php
/**
 * Admin Dashboard Page
 * Main landing page after login
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

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
$db->query("SELECT COUNT(*) as count FROM agents");  // Changed from users to agents table
$total_agents = $db->single()['count'];

// Get total enquiries count
$db->query("SELECT COUNT(*) as count FROM enquiries");
$total_enquiries = $db->single()['count'];

// Get new enquiries count
$db->query("SELECT COUNT(*) as count FROM enquiries WHERE status = 'new'");
$new_enquiries = $db->single()['count'];

// Get recent properties
$db->query("SELECT p.*, pt.name AS type_name, u.name AS agent_name 
            FROM properties p
            LEFT JOIN property_types pt ON p.type_id = pt.id
            LEFT JOIN agents a ON p.agent_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 5");
$recent_properties = $db->resultSet();

// Get recent enquiries
$db->query("SELECT e.*, p.title AS property_title, a.id as agent_id, u.name as agent_name 
            FROM enquiries e
            LEFT JOIN properties p ON e.property_id = p.id
            LEFT JOIN agents a ON e.agent_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY e.created_at DESC
            LIMIT 5");
$recent_enquiries = $db->resultSet();

// Include header
include_once '../includes/header.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <!-- Total Properties -->
    <div class="stat-card stat-card-primary">
        <div class="stat-card-header">
            <div class="stat-card-title">TOTAL PROPERTIES</div>
            <div class="stat-card-icon">
                <i class="fas fa-home"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $total_properties; ?></div>
        <div class="stat-card-subtitle">
            <?php 
            // Display count of each status type
            $available = isset($status_counts['available']) ? $status_counts['available'] : 0;
            $pending = isset($status_counts['pending']) ? $status_counts['pending'] : 0;
            $sold = isset($status_counts['sold']) ? $status_counts['sold'] : 0;
            $rented = isset($status_counts['rented']) ? $status_counts['rented'] : 0;
            
            echo "Available: $available | Pending: $pending | Sold: $sold | Rented: $rented";
            ?>
        </div>
    </div>
    
    <!-- Total Agents -->
    <div class="stat-card stat-card-success">
        <div class="stat-card-header">
            <div class="stat-card-title">TOTAL AGENTS</div>
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $total_agents; ?></div>
        <div class="stat-card-subtitle">
            <?php 
            // Query for active agents count
            $db->query("SELECT COUNT(*) as count FROM agents a 
                        JOIN users u ON a.user_id = u.id 
                        WHERE u.status = 1");
            $active_agents = $db->single()['count'];
            
            echo "$active_agents active out of $total_agents total";
            ?>
        </div>
    </div>
    
    <!-- Total Enquiries -->
    <!-- <div class="stat-card stat-card-warning">
        <div class="stat-card-header">
            <div class="stat-card-title">TOTAL ENQUIRIES</div>
            <div class="stat-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $total_enquiries; ?></div>
        <div class="stat-card-subtitle">
            <?php echo $new_enquiries; ?> new enquiries
        </div>
    </div> -->
    
    <!-- Property Status Distribution -->
    <div class="stat-card stat-card-danger">
        <div class="stat-card-header">
            <div class="stat-card-title">PROPERTY STATUS</div>
            <div class="stat-card-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php 
            // Calculate percentage of sold+rented properties
            $completed = ($sold + $rented);
            $percent = $total_properties > 0 ? round(($completed / $total_properties) * 100) : 0;
            echo "$percent%";
            ?>
        </div>
        <div class="stat-card-subtitle">
            Sold/Rented conversion rate
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
                        <?php $i=1;
                        foreach ($recent_properties as $property): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                                <td><?php echo formatPrice($property['price']); ?></td>
                                <td><?php echo htmlspecialchars($property['agent_name'] ?? 'Unassigned'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../properties/edit.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary m-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../properties/property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-secondary m-1">
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
            <a href="../properties/index.php" class="btn btn-outline">View All Properties</a>
        </div>
    </div>
</div>

<!-- Recent enquiries -->
<!-- <div class="card">
    <div class="card-header">
        <h2 class="mb-0">Recent Enquiries</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Property/Agent</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_enquiries)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No enquiries found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_enquiries as $inquiry): ?>
                            <tr>
                                <td><?php echo $inquiry['id']; ?></td>
                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                <td>
                                    <?php 
                                    if ($inquiry['property_id']) {
                                        echo htmlspecialchars($inquiry['property_title']);
                                    } elseif ($inquiry['agent_id']) {
                                        echo 'Agent: ' . htmlspecialchars($inquiry['agent_name']);
                                    } else {
                                        echo 'General Inquiry';
                                    }
                                    ?>
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
                                    <a href="../enquiries/view.php?id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-primary">
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
            <a href="enquiries/index.php" class="btn btn-outline">View All Enquiries</a>
        </div>
    </div>
</div> -->

<?php
// Include footer
include_once '../includes/footer.php';
?>