<?php
/**
 * Agent Details Page
 * Displays the public profile of a real estate agent.
 */

// --- Dependencies ---
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- Session Start (Optional - if needed for public site features) ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Page Setup ---
$db = new Database();
$agent_id = null;
$agent = null; // Will hold combined agent & user data
$specializations = [];
$featured_properties = [];
$available_properties = [];
$sold_properties = [];
$awards = [];
$certifications = [];
$reviews = [];
$gallery = [];

// --- Get Agent ID from URL ---
if (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
    $agent_id = (int)$_GET['id'];
} else {
    // Redirect to an agents list page or homepage if ID is invalid
    redirect('agents.php');
    exit;
}

// --- Fetch Agent Data ---
try {
    // 1. Fetch Core Agent and User Data
    $db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic, u.status as user_status
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = :agent_id AND u.role = 'agent' AND u.status = 1");
    $db->bind(':agent_id', $agent_id);
    $agent = $db->single();

    // If agent not found or not active, redirect
    if (!$agent) {
        setFlashMessage('error', 'Agent not found or is inactive.');
        redirect('agents.php');
        exit;
    }

    // 2. Fetch Specializations
    $db->query("SELECT s.name
                FROM agent_specialization_mapping m
                JOIN agent_specializations s ON m.specialization_id = s.id
                WHERE m.agent_id = :agent_id
                ORDER BY s.name ASC");
    $db->bind(':agent_id', $agent_id);
    $specializations = $db->resultSet();

    // 3. Fetch Agent's Featured Properties
    $db->query("SELECT p.id, p.title, p.price, p.address, p.city, p.bedrooms, p.bathrooms, p.area, p.area_unit, pt.name as type_name,
                       (SELECT image_path FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                JOIN property_types pt ON p.type_id = pt.id
                WHERE p.agent_id = :agent_id AND p.status = 'available' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT 6");
    $db->bind(':agent_id', $agent_id);
    $featured_properties = $db->resultSet();

    // 4. Fetch Agent's Available Non-Featured Properties
    $db->query("SELECT p.id, p.title, p.price, p.address, p.city, p.bedrooms, p.bathrooms, p.area, p.area_unit, pt.name as type_name,
                       (SELECT image_path FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                JOIN property_types pt ON p.type_id = pt.id
                WHERE p.agent_id = :agent_id AND p.status = 'available' AND p.featured = 0
                ORDER BY p.created_at DESC
                LIMIT 6");
    $db->bind(':agent_id', $agent_id);
    $available_properties = $db->resultSet();

    // 5. Fetch Agent's Sold Properties
    $db->query("SELECT p.id, p.title, p.price, p.address, p.city, p.bedrooms, p.bathrooms, p.area, p.area_unit, p.status, pt.name as type_name,
    (SELECT image_path FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
    FROM properties p
    JOIN property_types pt ON p.type_id = pt.id
    WHERE p.agent_id = :agent_id AND p.status IN ('sold', 'rented')
    ORDER BY p.updated_at DESC
    LIMIT 6");
    $db->bind(':agent_id', $agent_id);
    $sold_properties = $db->resultSet();

    // 6. Fetch Awards
    $db->query("SELECT name, issuing_organization, year, description
                FROM agent_awards
                WHERE agent_id = :agent_id
                ORDER BY year DESC, name ASC");
    $db->bind(':agent_id', $agent_id);
    $awards = $db->resultSet();

    // 7. Fetch Certifications
    $db->query("SELECT name, issuing_organization, issue_date, expiry_date, description
                FROM agent_certifications
                WHERE agent_id = :agent_id
                ORDER BY issue_date DESC, name ASC");
    $db->bind(':agent_id', $agent_id);
    $certifications = $db->resultSet();

    // 8. Fetch Approved Reviews
    $db->query("SELECT name, rating, title, review, created_at
                FROM agent_reviews
                WHERE agent_id = :agent_id AND status = 'approved'
                ORDER BY created_at DESC");
    $db->bind(':agent_id', $agent_id);
    $reviews = $db->resultSet();

    // 9. Fetch Gallery Images (Optional)
    $db->query("SELECT image_path, title, description FROM agent_gallery WHERE agent_id = :agent_id ORDER BY sort_order ASC");
    $db->bind(':agent_id', $agent_id);
    $gallery = $db->resultSet();

} catch (Exception $e) {
    error_log("Error fetching data for agent details (ID: {$agent_id}): " . $e->getMessage());
    die("An error occurred while loading agent details. Please try again later.");
}

// --- Set Page Title Dynamically ---
$page_title = htmlspecialchars($agent['name'] ?? '') . ' - Real Estate Agent';

// --- Include Public Header ---
include_once '../includes/header.php';
?>

<div class="container my-5">
    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card shadow-sm sticky-lg-top" style="top: 20px;">
                <?php
                // Properly construct the profile image URL
                if (!empty($agent['profile_pic'])) {
                    // For agents, the images should be in the agents directory
                    $profile_pic_url = AGENT_IMAGES_URL . basename($agent['profile_pic']);
                } else {
                    $profile_pic_url = DEFAULT_IMAGE_URL;
                }
                ?>
                <img src="<?php echo $profile_pic_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($agent['name'] ?? ''); ?>" style="max-height: 400px; object-fit: cover;">
                <div class="card-body text-center">
                    <h4 class="card-title mb-1"><?php echo htmlspecialchars($agent['name'] ?? ''); ?></h4>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($agent['position'] ?? 'Real Estate Agent'); ?></p>
                    <?php if (!empty($agent['rating']) && $agent['rating'] > 0): ?>
                        <p class="mb-3">
                            <span class="text-warning">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="<?php echo ($i <= round($agent['rating'])) ? 'fas' : 'far'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="ms-1">(<?php echo number_format($agent['rating'], 1); ?>)</span>
                        </p>
                    <?php endif; ?>

                    <?php
                    // Only display the contact info section if at least one contact method exists
                    $has_contact_info = !empty($agent['phone']) || !empty($agent['email']) || 
                                       !empty($agent['office_address']) || !empty($agent['office_hours']) || 
                                       !empty($agent['website_url']);
                    
                    if ($has_contact_info):
                    ?>
                    <ul class="list-unstyled text-start mb-4">
                        <?php if (!empty($agent['phone'])): ?>
                            <li><i class="fas fa-phone fa-fw me-2 text-primary"></i> <?php echo htmlspecialchars($agent['phone']); ?></li>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['email'])): ?>
                            <li><i class="fas fa-envelope fa-fw me-2 text-primary"></i> <?php echo htmlspecialchars($agent['email']); ?></li>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['office_address'])): ?>
                            <li><i class="fas fa-map-marker-alt fa-fw me-2 text-primary"></i> <?php echo nl2br(htmlspecialchars($agent['office_address'])); ?></li>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['office_hours'])): ?>
                            <li><i class="fas fa-clock fa-fw me-2 text-primary"></i> <?php echo htmlspecialchars($agent['office_hours']); ?></li>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['website_url'])): ?>
                            <li><i class="fas fa-globe fa-fw me-2 text-primary"></i> <a href="<?php echo htmlspecialchars($agent['website_url']); ?>" target="_blank" rel="noopener noreferrer">Visit Website</a></li>
                        <?php endif; ?>
                    </ul>
                    <?php endif; ?>

                    <?php
                    // Only display social media section if at least one social media link exists
                    $has_social_media = !empty($agent['facebook_url']) || !empty($agent['twitter_url']) || 
                                       !empty($agent['instagram_url']) || !empty($agent['linkedin_url']) || 
                                       !empty($agent['youtube_url']);
                    
                    if ($has_social_media):
                    ?>
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <?php if (!empty($agent['facebook_url'])): ?><a href="<?php echo htmlspecialchars($agent['facebook_url']); ?>" target="_blank" class="fs-4 text-secondary"><i class="fab fa-facebook"></i></a><?php endif; ?>
                        <?php if (!empty($agent['twitter_url'])): ?><a href="<?php echo htmlspecialchars($agent['twitter_url']); ?>" target="_blank" class="fs-4 text-secondary"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if (!empty($agent['instagram_url'])): ?><a href="<?php echo htmlspecialchars($agent['instagram_url']); ?>" target="_blank" class="fs-4 text-secondary"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        <?php if (!empty($agent['linkedin_url'])): ?><a href="<?php echo htmlspecialchars($agent['linkedin_url']); ?>" target="_blank" class="fs-4 text-secondary"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                        <?php if (!empty($agent['youtube_url'])): ?><a href="<?php echo htmlspecialchars($agent['youtube_url']); ?>" target="_blank" class="fs-4 text-secondary"><i class="fab fa-youtube"></i></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">About <?php echo htmlspecialchars(explode(' ', $agent['name'] ?? '')[0] ?? ''); // First name ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($agent['experience'])): ?>
                        <p><strong>Experience:</strong> <?php echo (int)$agent['experience']; ?>+ years</p>
                    <?php endif; ?>
                    <?php if (!empty($agent['description'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($agent['description'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No description provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($specializations)): ?>
            <div class="card shadow-sm mb-4">
                 <div class="card-header bg-light">
                    <h5 class="mb-0">Specializations</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($specializations as $spec): ?>
                        <span class="badge bg-secondary me-1 mb-1 fs-6"><?php echo htmlspecialchars($spec['name'] ?? ''); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($awards)): ?>
            <div class="card shadow-sm mb-4">
                 <div class="card-header bg-light">
                    <h5 class="mb-0">Awards</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                    <?php foreach ($awards as $award): ?>
                        <li class="mb-2">
                            <i class="fas fa-award text-warning me-2"></i>
                            <strong><?php echo htmlspecialchars($award['name'] ?? ''); ?></strong>
                            <?php if (!empty($award['year'])): ?> (<?php echo htmlspecialchars($award['year']); ?>)<?php endif; ?>
                            <?php if (!empty($award['issuing_organization'])): ?> - <small class="text-muted"><?php echo htmlspecialchars($award['issuing_organization']); ?></small><?php endif; ?>
                            <?php if (!empty($award['description'])): ?><p class="mb-0 ps-4"><small><?php echo htmlspecialchars($award['description']); ?></small></p><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($certifications)): ?>
            <div class="card shadow-sm mb-4">
                 <div class="card-header bg-light">
                    <h5 class="mb-0">Certifications</h5>
                </div>
                <div class="card-body">
                     <ul class="list-unstyled">
                    <?php foreach ($certifications as $cert): ?>
                         <li class="mb-2">
                            <i class="fas fa-certificate text-info me-2"></i>
                            <strong><?php echo htmlspecialchars($cert['name'] ?? ''); ?></strong>
                            <?php if (!empty($cert['issuing_organization'])): ?> - <small class="text-muted"><?php echo htmlspecialchars($cert['issuing_organization']); ?></small><?php endif; ?>
                            <?php if (!empty($cert['issue_date'])): ?> <small class="text-muted">(Issued: <?php echo formatDate($cert['issue_date'], 'M Y'); ?>)</small><?php endif; ?>
                            <?php if (!empty($cert['description'])): ?><p class="mb-0 ps-4"><small><?php echo htmlspecialchars($cert['description']); ?></small></p><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Featured Properties Section -->
            <?php if (!empty($featured_properties)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Featured Listings</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($featured_properties as $prop): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm listing-card">
                                    <?php
                                    // Properly construct property image URLs
                                    if (!empty($prop['primary_image'])) {
                                        $property_img_url = PROPERTY_IMAGES_URL . basename($prop['primary_image']);
                                    } else {
                                        $property_img_url = DEFAULT_IMAGE_URL;
                                    }
                                    ?>
                                    <div class="position-relative">
                                        <span class="position-absolute top-0 start-0 bg-warning text-dark px-2 py-1 m-2 rounded-end">Featured</span>
                                        <a href="property-details.php?id=<?php echo $prop['id']; ?>">
                                            <img src="<?php echo $property_img_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prop['title'] ?? ''); ?>" style="height: 200px; object-fit: cover;">
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><a href="property-details.php?id=<?php echo $prop['id']; ?>" class="text-decoration-none text-dark stretched-link"><?php echo htmlspecialchars($prop['title'] ?? ''); ?></a></h6>
                                        <p class="card-text text-muted small mb-1"><i class="fas fa-map-marker-alt fa-xs me-1"></i><?php echo htmlspecialchars(($prop['address'] ?? '') . ', ' . ($prop['city'] ?? '')); ?></p>
                                        <p class="card-text fw-bold text-primary fs-5 mb-2"><?php echo formatPrice($prop['price'] ?? 0); ?></p>
                                        <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                            <span><i class="fas fa-bed fa-xs me-1"></i> <?php echo $prop['bedrooms'] ?? 'N/A'; ?> Beds</span>
                                            <span><i class="fas fa-bath fa-xs me-1"></i> <?php echo $prop['bathrooms'] ?? 'N/A'; ?> Baths</span>
                                            <span><i class="fas fa-vector-square fa-xs me-1"></i> <?php echo number_format($prop['area'] ?? 0); ?> <?php echo htmlspecialchars($prop['area_unit'] ?? 'sq ft'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Available Properties Section -->
            <?php if (!empty($available_properties)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Available Properties</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($available_properties as $prop): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm listing-card">
                                    <?php
                                    // Properly construct property image URLs
                                    if (!empty($prop['primary_image'])) {
                                        $property_img_url = PROPERTY_IMAGES_URL . basename($prop['primary_image']);
                                    } else {
                                        $property_img_url = DEFAULT_IMAGE_URL;
                                    }
                                    ?>
                                    <a href="property-details.php?id=<?php echo $prop['id']; ?>">
                                        <img src="<?php echo $property_img_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prop['title'] ?? ''); ?>" style="height: 200px; object-fit: cover;">
                                    </a>
                                    <div class="card-body">
                                        <h6 class="card-title"><a href="property-details.php?id=<?php echo $prop['id']; ?>" class="text-decoration-none text-dark stretched-link"><?php echo htmlspecialchars($prop['title'] ?? ''); ?></a></h6>
                                        <p class="card-text text-muted small mb-1"><i class="fas fa-map-marker-alt fa-xs me-1"></i><?php echo htmlspecialchars(($prop['address'] ?? '') . ', ' . ($prop['city'] ?? '')); ?></p>
                                        <p class="card-text fw-bold text-primary fs-5 mb-2"><?php echo formatPrice($prop['price'] ?? 0); ?></p>
                                        <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                            <span><i class="fas fa-bed fa-xs me-1"></i> <?php echo $prop['bedrooms'] ?? 'N/A'; ?> Beds</span>
                                            <span><i class="fas fa-bath fa-xs me-1"></i> <?php echo $prop['bathrooms'] ?? 'N/A'; ?> Baths</span>
                                            <span><i class="fas fa-vector-square fa-xs me-1"></i> <?php echo number_format($prop['area'] ?? 0); ?> <?php echo htmlspecialchars($prop['area_unit'] ?? 'sq ft'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sold Properties Section -->
            <?php if (!empty($sold_properties)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recently Sold/Rented Properties</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($sold_properties as $prop): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm listing-card">
                                    <?php
                                    // Properly construct property image URLs
                                    if (!empty($prop['primary_image'])) {
                                        $property_img_url = PROPERTY_IMAGES_URL . basename($prop['primary_image']);
                                    } else {
                                        $property_img_url = DEFAULT_IMAGE_URL;
                                    }
                                    ?>
                                    <div class="position-relative">
                                        <span class="position-absolute top-0 start-0 bg-dark text-white px-2 py-1 m-2 rounded-end">
                                            <?php echo isset($prop['status']) ? ucfirst($prop['status']) : 'Unavailable'; ?>
                                        </span>
                                        <a href="property-details.php?id=<?php echo $prop['id']; ?>">
                                            <img src="<?php echo $property_img_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prop['title'] ?? ''); ?>" style="height: 200px; object-fit: cover; filter: grayscale(50%);">
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><a href="property-details.php?id=<?php echo $prop['id']; ?>" class="text-decoration-none text-dark stretched-link"><?php echo htmlspecialchars($prop['title'] ?? ''); ?></a></h6>
                                        <p class="card-text text-muted small mb-1"><i class="fas fa-map-marker-alt fa-xs me-1"></i><?php echo htmlspecialchars(($prop['address'] ?? '') . ', ' . ($prop['city'] ?? '')); ?></p>
                                        <p class="card-text fw-bold text-secondary fs-5 mb-2"><?php echo formatPrice($prop['price'] ?? 0); ?></p>
                                        <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                            <span><i class="fas fa-bed fa-xs me-1"></i> <?php echo $prop['bedrooms'] ?? 'N/A'; ?> Beds</span>
                                            <span><i class="fas fa-bath fa-xs me-1"></i> <?php echo $prop['bathrooms'] ?? 'N/A'; ?> Baths</span>
                                            <span><i class="fas fa-vector-square fa-xs me-1"></i> <?php echo number_format($prop['area'] ?? 0); ?> <?php echo htmlspecialchars($prop['area_unit'] ?? 'sq ft'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- No Properties Message -->
            <?php if (empty($featured_properties) && empty($available_properties) && empty($sold_properties)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Properties</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">This agent currently has no listed properties.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Client Reviews</h5>
                </div>
                <div class="card-body">
                     <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <h6><?php echo htmlspecialchars($review['title'] ?? 'Review'); ?></h6>
                                <p class="mb-1">
                                    <span class="text-warning">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?php echo ($i <= ($review['rating'] ?? 0)) ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="ms-2 fw-bold"><?php echo htmlspecialchars($review['name'] ?? 'Anonymous'); ?></span>
                                    <small class="text-muted ms-2">- <?php echo formatDate($review['created_at'] ?? '', 'M d, Y'); ?></small>
                                </p>
                                <p class="mb-0 fst-italic">"<?php echo nl2br(htmlspecialchars($review['review'] ?? '')); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                     <?php else: ?>
                         <p class="text-muted">No reviews available for this agent yet.</p>
                     <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($gallery)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Gallery</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-2 row-cols-md-3 g-3">
                        <?php foreach ($gallery as $item): ?>
                            <div class="col">
                                <?php
                                // Properly construct gallery image URL
                                $gallery_img_url = AGENT_IMAGES_URL . 'gallery/' . basename($item['image_path'] ?? '');
                                ?>
                                <a href="<?php echo $gallery_img_url; ?>" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                                    <img src="<?php echo $gallery_img_url; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['title'] ?? 'Gallery image'); ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// --- Include Public Footer ---
include_once '../includes/footer.php';
?>