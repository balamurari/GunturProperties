<?php
/**
 * Property Details Page for Admin/Agents
 * Displays detailed information about a specific property
 */

// --- Dependencies ---
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- Session Start ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Authentication Check ---
requireLogin(); // Ensure the user is logged in

// --- Page Setup ---
$db = new Database();
$property_id = null;
$property = null;
$property_images = [];
$property_features = [];
$agent = null;
$similar_properties = [];

// --- Get Property ID from URL ---
if (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
    $property_id = (int)$_GET['id'];
} else {
    setFlashMessage('error', 'Invalid property ID.');
    redirect('index.php');
    exit;
}

// --- Fetch Property Data ---
try {
    // 1. Fetch property details
    $db->query("SELECT p.*, pt.name AS type_name, 
                a.id AS agent_id, u.name AS agent_name, u.email AS agent_email, 
                u.phone AS agent_phone, u.profile_pic AS agent_profile_pic
                FROM properties p
                LEFT JOIN property_types pt ON p.type_id = pt.id
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE p.id = :id");
    $db->bind(':id', $property_id);
    $property = $db->single();

    if (!$property) {
        setFlashMessage('error', 'Property not found.');
        redirect('index.php');
        exit;
    }

    // 2. Fetch property images
    $db->query("SELECT * FROM property_images WHERE property_id = :property_id ORDER BY is_primary DESC, sort_order ASC");
    $db->bind(':property_id', $property_id);
    $property_images = $db->resultSet();

    // 3. Fetch property features
    $db->query("SELECT f.name, f.icon, m.value 
                FROM property_feature_mapping m
                JOIN property_features f ON m.feature_id = f.id
                WHERE m.property_id = :property_id");
    $db->bind(':property_id', $property_id);
    $property_features = $db->resultSet();

    // 4. Fetch agent details if assigned
    if (!empty($property['agent_id'])) {
        $db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic
                   FROM agents a
                   JOIN users u ON a.user_id = u.id
                   WHERE a.id = :agent_id");
        $db->bind(':agent_id', $property['agent_id']);
        $agent = $db->single();
    }

    // 5. Fetch similar properties (same type, similar price range)
    $min_price = $property['price'] * 0.7; // 70% of property price
    $max_price = $property['price'] * 1.3; // 130% of property price

    $db->query("SELECT p.id, p.title, p.price, p.address, p.city, p.bedrooms, p.bathrooms,
                p.area, p.area_unit, p.status,
                (SELECT image_path FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                WHERE p.type_id = :type_id
                AND p.id != :property_id
                AND p.price BETWEEN :min_price AND :max_price
                ORDER BY ABS(p.price - :price)
                LIMIT 3");
    $db->bind(':type_id', $property['type_id']);
    $db->bind(':property_id', $property_id);
    $db->bind(':min_price', $min_price);
    $db->bind(':max_price', $max_price);
    $db->bind(':price', $property['price']);
    $similar_properties = $db->resultSet();

} catch (Exception $e) {
    error_log("Error fetching property details (ID: {$property_id}): " . $e->getMessage());
    setFlashMessage('error', 'An error occurred while loading property details.');
    redirect('index.php');
    exit;
}

// --- Set page title ---
$page_title = htmlspecialchars($property['title']) . ' - Property Details';

// --- Include Header ---
include_once '../includes/header.php';
?>

<div class="container-fluid my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php">Properties</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
        </ol>
    </nav>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mb-4">
        <h1 class="h2 mb-0"><?php echo htmlspecialchars($property['title']); ?></h1>
        <div>
            <a href="../properties/edit.php?id=<?php echo $property_id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Property
            </a>
            <a href="index.php" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Property Images -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body p-0">
                    <div id="propertyImageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php if (empty($property_images)): ?>
                                <div class="carousel-item active">
                                    <img src="<?php echo DEFAULT_IMAGE_URL; ?>" class="d-block w-100" alt="No image available" style="height: 500px; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <?php foreach ($property_images as $index => $image): ?>
                                    <?php 
                                    $image_url = PROPERTY_IMAGES_URL . basename($image['image_path']);
                                    ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo $image_url; ?>" class="d-block w-100" alt="Property image <?php echo $index + 1; ?>" style="height: 500px; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($property_images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#propertyImageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#propertyImageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        <?php endif; ?>

                        <!-- Thumbnail Navigation -->
                        <?php if (count($property_images) > 1): ?>
                            <div class="carousel-thumbnails mt-3 d-flex overflow-auto">
                                <?php foreach ($property_images as $index => $image): ?>
                                    <?php 
                                    $thumb_url = PROPERTY_IMAGES_URL . basename($image['image_path']);
                                    ?>
                                    <div class="thumbnail-item mx-1" data-bs-target="#propertyImageCarousel" data-bs-slide-to="<?php echo $index; ?>">
                                        <img src="<?php echo $thumb_url; ?>" class="img-thumbnail" alt="Thumbnail <?php echo $index + 1; ?>" style="width: 100px; height: 70px; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Property Details -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Property Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h2 class="h4 mb-3"><?php echo htmlspecialchars($property['title']); ?></h2>
                            <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state'] . ' ' . $property['zip_code']); ?></p>
                            <p class="h3 text-primary mb-4"><?php echo formatPrice($property['price']); ?></p>
                            
                            <div class="d-flex mb-4">
                                <span class="badge bg-<?php echo getStatusBadgeClass($property['status']); ?> fs-6 py-2 px-3">
                                    <?php echo ucfirst($property['status']); ?>
                                </span>
                                <?php if ($property['featured']): ?>
                                    <span class="badge bg-warning text-dark fs-6 py-2 px-3 ms-2">Featured</span>
                                <?php endif; ?>
                                <span class="badge bg-info fs-6 py-2 px-3 ms-2"><?php echo htmlspecialchars($property['type_name']); ?></span>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <?php if (!empty($property['bedrooms'])): ?>
                                <div class="col-6 col-lg-3">
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-bed text-primary mb-2"></i>
                                        <p class="mb-0"><?php echo $property['bedrooms']; ?> Beds</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property['bathrooms'])): ?>
                                <div class="col-6 col-lg-3">
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-bath text-primary mb-2"></i>
                                        <p class="mb-0"><?php echo $property['bathrooms']; ?> Baths</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property['area'])): ?>
                                <div class="col-6 col-lg-3">
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-vector-square text-primary mb-2"></i>
                                        <p class="mb-0"><?php echo number_format($property['area']); ?> <?php echo htmlspecialchars($property['area_unit']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property['facing'])): ?>
                                <div class="col-6 col-lg-3">
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-compass text-primary mb-2"></i>
                                        <p class="mb-0"><?php echo htmlspecialchars($property['facing']); ?> Facing</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Description</h5>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                            </div>
                            
                            <?php if (!empty($property_features)): ?>
                            <h5 class="mb-3">Features</h5>
                            <div class="row g-2 mb-4">
                                <?php foreach ($property_features as $feature): ?>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="<?php echo htmlspecialchars($feature['icon']); ?> text-primary me-2"></i>
                                        <span>
                                            <?php echo htmlspecialchars($feature['name']); ?>
                                            <?php if (!empty($feature['value']) && $feature['value'] !== 'yes'): ?>
                                                : <?php echo htmlspecialchars($feature['value']); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <small class="text-muted">Listed: <?php echo formatDate($property['created_at']); ?></small>
                                <small class="text-muted">Updated: <?php echo formatDate($property['updated_at']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Agent Information -->
            <?php if ($agent): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Agent Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex">
                        <?php 
                        $agent_img_url = !empty($agent['profile_pic']) 
                            ? AGENT_IMAGES_URL . basename($agent['profile_pic']) 
                            : DEFAULT_IMAGE_URL;
                        ?>
                        <img src="<?php echo $agent_img_url; ?>" class="rounded-circle me-3" alt="Agent profile" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($agent['name']); ?></h5>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($agent['position'] ?? 'Real Estate Agent'); ?></p>
                            <?php if (!empty($agent['rating'])): ?>
                            <p class="mb-2">
                                <span class="text-warning">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?php echo ($i <= round($agent['rating'])) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </span>
                                <small class="ms-1">(<?php echo number_format($agent['rating'], 1); ?>)</small>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <ul class="list-unstyled mb-3">
                        <?php if (!empty($agent['phone'])): ?>
                        <li class="mb-2"><i class="fas fa-phone fa-fw text-primary me-2"></i> <?php echo htmlspecialchars($agent['phone']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($agent['email'])): ?>
                        <li class="mb-2"><i class="fas fa-envelope fa-fw text-primary me-2"></i> <?php echo htmlspecialchars($agent['email']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($agent['experience'])): ?>
                        <li class="mb-2"><i class="fas fa-briefcase fa-fw text-primary me-2"></i> <?php echo $agent['experience']; ?>+ years experience</li>
                        <?php endif; ?>
                    </ul>
                    
                    <a href="agent-details.php?id=<?php echo $agent['id']; ?>" class="btn btn-primary w-100">View Agent Profile</a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Property Actions -->
            <!-- <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../properties/edit.php?id=<?php echo $property_id; ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Property
                        </a>
                        <a href="#" class="btn btn-outline-secondary" onclick="window.print();">
                            <i class="fas fa-print me-2"></i> Print Details
                        </a>
                        <a href="mailto:?subject=Property: <?php echo urlencode($property['title']); ?>&body=Check out this property: <?php echo urlencode(ROOT_URL . 'property-details.php?id=' . $property_id); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-share-alt me-2"></i> Share Property
                        </a>
                    </div>
                </div>
            </div>
             -->
            <!-- Similar Properties -->
            <?php if (!empty($similar_properties)): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Similar Properties</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($similar_properties as $similar): ?>
                        <div class="card mb-3 border">
                            <div class="row g-0">
                                <div class="col-4">
                                    <?php 
                                    $similar_img_url = !empty($similar['primary_image']) 
                                        ? PROPERTY_IMAGES_URL . basename($similar['primary_image']) 
                                        : DEFAULT_IMAGE_URL;
                                    ?>
                                    <img src="<?php echo $similar_img_url; ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($similar['title']); ?>" style="height: 100%; object-fit: cover;">
                                </div>
                                <div class="col-8">
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($similar['title']); ?></h6>
                                        <p class="card-text text-primary mb-1"><?php echo formatPrice($similar['price']); ?></p>
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span><?php echo $similar['bedrooms'] ?? '-'; ?> bed</span>
                                            <span><?php echo $similar['bathrooms'] ?? '-'; ?> bath</span>
                                            <span><?php echo number_format($similar['area'] ?? 0); ?> <?php echo $similar['area_unit'] ?? 'sq ft'; ?></span>
                                        </div>
                                        <a href="property-details.php?id=<?php echo $similar['id']; ?>" class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
/**
 * Helper function to get the appropriate badge class based on property status
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'available':
            return 'success';
        case 'sold':
            return 'danger';
        case 'pending':
            return 'warning text-dark';
        case 'rented':
            return 'info';
        default:
            return 'secondary';
    }
}

// Include Footer
include_once '../includes/footer.php';
?>

<style>
/* Carousel thumbnails styles */
.carousel-thumbnails {
    padding: 0 10px;
}
.thumbnail-item {
    cursor: pointer;
    transition: opacity 0.3s;
}
.thumbnail-item:hover {
    opacity: 0.8;
}

/* Print styles */
@media print {
    .breadcrumb, .card-header, .btn, nav, footer, .carousel-control-prev, .carousel-control-next {
        display: none !important;
    }
    .container-fluid {
        width: 100%;
        padding: 0;
        margin: 0;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
    .col-lg-8 {
        width: 100% !important;
    }
    .col-lg-4 {
        width: 100% !important;
    }
}
</style>

<script>
// Initialize carousel thumbnails
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    thumbnails.forEach(function(thumbnail, index) {
        thumbnail.addEventListener('click', function() {
            const carousel = document.getElementById('propertyImageCarousel');
            const bsCarousel = new bootstrap.Carousel(carousel);
            bsCarousel.to(index);
        });
    });
});
</script>
