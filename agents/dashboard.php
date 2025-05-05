<?php
/**
 * Agent Dashboard - Overview
 */

// --- Dependencies & Auth ---
require_once __DIR__ . '/includes/header.php'; // Use agent header (includes auth check)
// Database connection should be available via $db from included files if needed
// Agent ID is available in $_SESSION['agent_id']

// --- Fetch Dashboard Data ---
$agent_id = $_SESSION['agent_id'];
$stats = [
    'active_listings' => 0,
    'pending_listings' => 0, // Properties with status 'pending'
    'sold_listings' => 0,    // Properties with status 'sold'
    'total_enquiries' => 0   // Enquiries linked to this agent
];

try {
    // Count properties by status
    $db->query("SELECT status, COUNT(*) as count FROM properties WHERE agent_id = :agent_id GROUP BY status");
    $db->bind(':agent_id', $agent_id);
    $property_counts = $db->resultSet();
    foreach ($property_counts as $row) {
        if ($row['status'] == 'available') $stats['active_listings'] = $row['count'];
        if ($row['status'] == 'pending') $stats['pending_listings'] = $row['count'];
        if ($row['status'] == 'sold') $stats['sold_listings'] = $row['count'];
        // Add 'rented' if you track that status
    }

    // Count enquiries for this agent
    $db->query("SELECT COUNT(*) as count FROM enquiries WHERE agent_id = :agent_id");
    $db->bind(':agent_id', $agent_id);
    $enquiry_count_result = $db->single();
    $stats['total_enquiries'] = $enquiry_count_result ? $enquiry_count_result['count'] : 0;

} catch (Exception $e) {
    error_log("Error fetching agent dashboard stats (Agent ID: {$agent_id}): " . $e->getMessage());
    setFlashMessage('error', 'Could not load dashboard statistics.');
    // Display 0 for stats, page will still load
}

// --- Set Page Title ---
$page_title = 'Dashboard'; // Set before including header if needed by header logic

?>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card text-bg-primary shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $stats['active_listings']; ?></h5>
                        <p class="card-text mb-0">Active Listings</p>
                    </div>
                    <i class="fas fa-home fa-2x opacity-75"></i>
                </div>
            </div>
             <a href="<?php echo ROOT_URL; ?>agent/properties.php?status=available" class="card-footer text-white text-decoration-none stretched-link">
                View Details <i class="fas fa-arrow-circle-right fa-xs ms-1"></i>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
         <div class="card text-bg-warning shadow-sm h-100">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $stats['pending_listings']; ?></h5>
                        <p class="card-text mb-0">Pending Listings</p>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                </div>
            </div>
             <a href="<?php echo ROOT_URL; ?>agent/properties.php?status=pending" class="card-footer text-dark text-decoration-none stretched-link">
                View Details <i class="fas fa-arrow-circle-right fa-xs ms-1"></i>
            </a>
        </div>
    </div>
     <div class="col-md-6 col-xl-3">
         <div class="card text-bg-success shadow-sm h-100">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $stats['sold_listings']; ?></h5>
                        <p class="card-text mb-0">Sold Listings</p>
                    </div>
                    <i class="fas fa-handshake fa-2x opacity-75"></i>
                </div>
            </div>
             <a href="<?php echo ROOT_URL; ?>agent/properties.php?status=sold" class="card-footer text-white text-decoration-none stretched-link">
                View Details <i class="fas fa-arrow-circle-right fa-xs ms-1"></i>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card text-bg-info shadow-sm h-100">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $stats['total_enquiries']; ?></h5>
                        <p class="card-text mb-0">Total Enquiries</p>
                    </div>
                    <i class="fas fa-envelope-open-text fa-2x opacity-75"></i>
                </div>
            </div>
             <a href="<?php echo ROOT_URL; ?>agent/enquiries.php" class="card-footer text-white text-decoration-none stretched-link"> View Details <i class="fas fa-arrow-circle-right fa-xs ms-1"></i>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Agent'); ?>!</h5>
        <p class="card-text">From your dashboard, you can manage your profile, view your property listings, and add new properties.</p>
        <a href="<?php echo ROOT_URL; ?>agent/profile.php" class="btn btn-primary me-2"><i class="fas fa-user-edit me-1"></i> Edit My Profile</a>
        <a href="<?php echo ROOT_URL; ?>agent/property-add.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Add New Property</a>
    </div>
</div>

<?php
// --- Include Agent Footer ---
require_once __DIR__ . '/includes/footer.php';
?>