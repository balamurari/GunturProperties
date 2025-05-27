<?php
/**
 * Property Details Page - Enhanced with Beautiful Image Gallery
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Property Details';

// Check if property ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid property ID.');
    redirect('index.php');
}

$property_id = (int)$_GET['id'];

// Get property details
$db = new Database();
$db->query("SELECT p.*, pt.name AS type_name 
            FROM properties p
            LEFT JOIN property_types pt ON p.type_id = pt.id
            WHERE p.id = :id");
$db->bind(':id', $property_id);
$property = $db->single();

// Check if property exists
if (!$property) {
    setFlashMessage('error', 'Property not found.');
    redirect('index.php');
}

// Get property images
$db->query("SELECT * FROM property_images WHERE property_id = :id ORDER BY is_primary DESC, sort_order ASC");
$db->bind(':id', $property_id);
$property_images = $db->resultSet();

// Get primary image
$primary_image = null;
foreach ($property_images as $image) {
    if ($image['is_primary']) {
        $primary_image = $image;
        break;
    }
}

// If no primary image, use the first image
if (!$primary_image && !empty($property_images)) {
    $primary_image = $property_images[0];
}

// Get property features
$db->query("SELECT pf.name, pf.icon, pfm.value 
            FROM property_feature_mapping pfm
            JOIN property_features pf ON pfm.feature_id = pf.id
            WHERE pfm.property_id = :id");
$db->bind(':id', $property_id);
$property_features = $db->resultSet();

// Get agent details if assigned
$agent = null;
if ($property['agent_id']) {
    $db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic 
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = :id");
    $db->bind(':id', $property['agent_id']);
    $agent = $db->single();

    // Get agent specializations
    if ($agent) {
        $db->query("SELECT s.name 
                    FROM agent_specialization_mapping m
                    JOIN agent_specializations s ON m.specialization_id = s.id
                    WHERE m.agent_id = :id");
        $db->bind(':id', $agent['id']);
        $specializations = $db->resultSet();
        
        $agent['specializations'] = array_column($specializations, 'name');
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid property-details-container">
    <!-- Property Header -->
    <div class="property-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="property-title-section">
                        <h1 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h1>
                        <div class="property-meta">
                            <span class="property-id">ID: <?php echo $property['id']; ?></span>
                            <span class="property-status status-<?php echo $property['status']; ?>">
                                <?php echo ucfirst($property['status']); ?>
                            </span>
                            <?php if ($property['featured']): ?>
                            <span class="property-featured">
                                <i class="fas fa-star"></i> Featured
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="property-price">
                            <?php echo formatIndianPrice($property['price']); ?>
                            <?php if ($property['status'] == 'rent'): ?>
                                <span class="price-period">/month</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="action-buttons">
                        <a href="edit.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Property
                        </a>
                        <a href="#" class="btn btn-danger" onclick="confirmDelete(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <!-- Left Column: Enhanced Image Gallery -->
            <div class="col-lg-8">
                <div class="property-gallery-section">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas fa-camera"></i> Property Gallery 
                                <span class="image-count">(<?php echo count($property_images); ?> images)</span>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($property_images)): ?>
                                <!-- Main Image Display -->
                                <div class="main-image-container">
                                    <?php if ($primary_image): ?>
                                        <img src="<?php echo getPropertyImageUrl($primary_image['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                             class="main-property-image" 
                                             id="mainImage"
                                             onclick="openLightbox(0)">
                                        <div class="image-overlay">
                                            <button class="btn btn-light btn-sm zoom-btn" onclick="openLightbox(0)">
                                                <i class="fas fa-search-plus"></i> View Full Size
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Thumbnail Gallery -->
                                <?php if (count($property_images) > 1): ?>
                                <div class="thumbnail-gallery">
                                    <div class="gallery-header">
                                        <h4>All Images</h4>
                                        <span class="view-all-btn" onclick="openLightbox(0)">
                                            <i class="fas fa-expand"></i> View Gallery
                                        </span>
                                    </div>
                                    <div class="thumbnail-grid">
                                        <?php foreach($property_images as $index => $image): ?>
                                            <div class="thumbnail-item <?php echo $image['is_primary'] ? 'active' : ''; ?>" 
                                                 onclick="changeMainImage('<?php echo getPropertyImageUrl($image['image_path']); ?>', <?php echo $index; ?>)">
                                                <img src="<?php echo getPropertyImageUrl($image['image_path']); ?>" 
                                                     alt="Property Image <?php echo $index + 1; ?>" 
                                                     class="thumbnail-image">
                                                <?php if ($image['is_primary']): ?>
                                                    <div class="primary-badge">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-images-placeholder">
                                    <i class="fas fa-image"></i>
                                    <h4>No Images Available</h4>
                                    <p>No images have been uploaded for this property yet.</p>
                                    <a href="edit.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Images
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Property Details -->
            <div class="col-lg-4">
                <!-- Quick Details Card -->
                <div class="card property-details-card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-info-circle"></i> Property Details</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Price</span>
                                    <span class="detail-value"><?php echo formatIndianPrice($property['price']); ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Type</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($property['type_name']); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($property['bedrooms']): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Bedrooms</span>
                                    <span class="detail-value"><?php echo $property['bedrooms']; ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($property['bathrooms']): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-bath"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Bathrooms</span>
                                    <span class="detail-value"><?php echo $property['bathrooms']; ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($property['area']): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-ruler-combined"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Area</span>
                                    <span class="detail-value"><?php echo number_format($property['area'], 0) . ' ' . $property['area_unit']; ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Location</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($property['facing']): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-compass"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Facing</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($property['facing']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card contact-card mt-3">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-phone"></i> Contact Information</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($property['phone_number'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?php echo $property['phone_number']; ?>">
                                <?php echo htmlspecialchars($property['phone_number']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['instagram_url'])): ?>
                        <div class="contact-item">
                            <i class="fab fa-instagram"></i>
                            <a href="<?php echo htmlspecialchars($property['instagram_url']); ?>" target="_blank">
                                View on Instagram
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-align-left"></i> Description</h3>
                    </div>
                    <div class="card-body">
                        <p class="property-description"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                        
                        <div class="property-meta-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-plus"></i> 
                                        Created: <?php echo formatDate($property['created_at']); ?>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-edit"></i> 
                                        Updated: <?php echo formatDate($property['updated_at']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Features Section -->
        <?php if (!empty($property_features)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-star"></i> Property Features</h3>
                    </div>
                    <div class="card-body">
                        <div class="features-grid">
                            <?php foreach ($property_features as $feature): ?>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="<?php echo $feature['icon']; ?>"></i>
                                </div>
                                <div class="feature-content">
                                    <span class="feature-name"><?php echo htmlspecialchars($feature['name']); ?></span>
                                    <?php if ($feature['value'] && strtolower($feature['value']) !== 'yes'): ?>
                                        <span class="feature-value"><?php echo htmlspecialchars($feature['value']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Agent Section -->
        <?php if ($agent): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card agent-card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-user-tie"></i> Property Agent</h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <div class="agent-photo">
                                    <?php if ($agent['profile_pic']): ?>
                                        <img src="<?php echo getAgentImageUrl($agent['profile_pic']); ?>" 
                                             alt="<?php echo htmlspecialchars($agent['name']); ?>" 
                                             class="agent-image">
                                    <?php else: ?>
                                        <div class="agent-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="agent-info">
                                    <h4 class="agent-name"><?php echo htmlspecialchars($agent['name']); ?></h4>
                                    <p class="agent-position"><?php echo htmlspecialchars($agent['position']); ?></p>
                                    
                                    <div class="agent-details">
                                        <div class="agent-detail">
                                            <i class="fas fa-briefcase"></i>
                                            <span><?php echo $agent['experience']; ?> years experience</span>
                                        </div>
                                        <div class="agent-detail">
                                            <i class="fas fa-envelope"></i>
                                            <a href="mailto:<?php echo htmlspecialchars($agent['email']); ?>">
                                                <?php echo htmlspecialchars($agent['email']); ?>
                                            </a>
                                        </div>
                                        <div class="agent-detail">
                                            <i class="fas fa-phone"></i>
                                            <a href="tel:<?php echo htmlspecialchars($agent['phone']); ?>">
                                                <?php echo htmlspecialchars($agent['phone']); ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <a href="../agents/agent-details.php?id=<?php echo $agent['id']; ?>" class="btn btn-outline-primary mt-2">
                                        <i class="fas fa-user"></i> View Full Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced Image Lightbox Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Property Gallery</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="lightbox-container">
                    <img id="lightboxImage" src="" alt="Property Image" class="lightbox-image">
                    <div class="lightbox-nav">
                        <button class="nav-btn prev-btn" onclick="changeLightboxImage(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="nav-btn next-btn" onclick="changeLightboxImage(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="lightbox-counter">
                        <span id="currentImageNumber">1</span> / <span id="totalImages"><?php echo count($property_images); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <span id="propertyName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete Property</a>
            </div>
        </div>
    </div>
</div>

<script>
// Image gallery functionality
const images = [
    <?php foreach($property_images as $index => $image): ?>
    '<?php echo getPropertyImageUrl($image['image_path']); ?>'<?php echo $index < count($property_images) - 1 ? ',' : ''; ?>
    <?php endforeach; ?>
];

let currentImageIndex = 0;

function changeMainImage(imageSrc, index) {
    document.getElementById('mainImage').src = imageSrc;
    currentImageIndex = index;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.thumbnail-item')[index].classList.add('active');
}

function openLightbox(index) {
    currentImageIndex = index;
    document.getElementById('lightboxImage').src = images[index];
    document.getElementById('currentImageNumber').textContent = index + 1;
    $('#imageModal').modal('show');
}

function changeLightboxImage(direction) {
    currentImageIndex += direction;
    
    if (currentImageIndex < 0) {
        currentImageIndex = images.length - 1;
    } else if (currentImageIndex >= images.length) {
        currentImageIndex = 0;
    }
    
    document.getElementById('lightboxImage').src = images[currentImageIndex];
    document.getElementById('currentImageNumber').textContent = currentImageIndex + 1;
}

function confirmDelete(id, name) {
    document.getElementById('propertyName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + id;
    $('#deleteModal').modal('show');
}

// Keyboard navigation for lightbox
document.addEventListener('keydown', function(e) {
    if ($('#imageModal').hasClass('show')) {
        if (e.key === 'ArrowLeft') {
            changeLightboxImage(-1);
        } else if (e.key === 'ArrowRight') {
            changeLightboxImage(1);
        } else if (e.key === 'Escape') {
            $('#imageModal').modal('hide');
        }
    }
});
</script>

<style>
/* Enhanced Property Details Styling */
.property-details-container {
    background: #f8f9fa;
    min-height: 100vh;
}

.property-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 0;
}

.property-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.property-meta {
    margin-bottom: 1rem;
}

.property-meta span {
    margin-right: 1rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.property-id {
    background: rgba(255,255,255,0.2);
}

.property-status {
    background: rgba(255,255,255,0.3);
}

.property-featured {
    background: #ffc107;
    color: #333;
}

.property-price {
    font-size: 2rem;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.price-period {
    font-size: 1rem;
    opacity: 0.8;
}

/* Enhanced Image Gallery */
.property-gallery-section .card {
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border-radius: 15px;
    overflow: hidden;
}

.main-image-container {
    position: relative;
    background: #000;
}

.main-property-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.main-property-image:hover {
    transform: scale(1.02);
}

.image-overlay {
    position: absolute;
    top: 1rem;
    right: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.main-image-container:hover .image-overlay {
    opacity: 1;
}

.zoom-btn {
    backdrop-filter: blur(10px);
    background: rgba(255,255,255,0.9) !important;
    border: none;
    border-radius: 20px;
}

.thumbnail-gallery {
    padding: 1.5rem;
    background: #f8f9fa;
}

.gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.gallery-header h4 {
    margin: 0;
    color: #333;
}

.view-all-btn {
    color: #007bff;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
}

.view-all-btn:hover {
    text-decoration: underline;
}

.thumbnail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
}

.thumbnail-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
}

.thumbnail-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.thumbnail-item.active {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.3);
}

.thumbnail-image {
    width: 100%;
    height: 80px;
    object-fit: cover;
}

.primary-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ffc107;
    color: #333;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

/* No Images Placeholder */
.no-images-placeholder {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.no-images-placeholder i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Property Details Cards */
.property-details-card,
.contact-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.detail-grid {
    padding: 0;
}

.detail-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item:hover {
    background-color: #f8f9fa;
}

.detail-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
}

.detail-content {
    flex: 1;
}

.detail-label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.detail-value {
    display: block;
    font-size: 1rem;
    color: #333;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Contact Card */
.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.contact-item:last-child {
    margin-bottom: 0;
}

.contact-item i {
    width: 30px;
    color: #007bff;
    margin-right: 0.75rem;
}

.contact-item a {
    color: #333;
    text-decoration: none;
    font-weight: 500;
}

.contact-item a:hover {
    color: #007bff;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.feature-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
}

.feature-content {
    flex: 1;
}

.feature-name {
    display: block;
    font-weight: 600;
    color: #333;
}

.feature-value {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Agent Card */
.agent-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.agent-photo {
    text-align: center;
}

.agent-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.agent-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 2rem;
    margin: 0 auto;
}

.agent-name {
    color: #333;
    margin-bottom: 0.25rem;
}

.agent-position {
    color: #6c757d;
    margin-bottom: 1rem;
}

.agent-details {
    margin-bottom: 1rem;
}

.agent-detail {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.agent-detail i {
    width: 20px;
    color: #007bff;
    margin-right: 0.5rem;
}

.agent-detail a {
    color: #333;
    text-decoration: none;
}

.agent-detail a:hover {
    color: #007bff;
}

/* Lightbox Modal */
.lightbox-container {
    position: relative;
    background: #000;
}

.lightbox-image {
    width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 1rem;
    transform: translateY(-50%);
}

.nav-btn {
    background: rgba(255,255,255,0.8);
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nav-btn:hover {
    background: rgba(255,255,255,0.9);
    transform: scale(1.1);
}

.lightbox-counter {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .property-title {
        font-size: 1.8rem;
    }
    
    .property-price {
        font-size: 1.5rem;
    }
    
    .main-property-image {
        height: 250px;
    }
    
    .thumbnail-grid {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 0.5rem;
    }
    
    .thumbnail-image {
        height: 60px;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        margin-top: 1rem;
    }
    
    .action-buttons .btn {
        flex: 1;
        min-width: 120px;
    }
}

@media (max-width: 576px) {
    .detail-item {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }
    
    .detail-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include_once '../includes/footer.php'; ?>