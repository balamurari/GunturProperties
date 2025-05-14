<?php
/**
 * Property Details Page
 * Display detailed information about a specific property
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Property Details';

// Get database connection
$db = new Database();

// Check if property ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: properties.php');
    exit;
}

$property_id = (int)$_GET['id'];

// Get property details with complete agent information
$db->query("SELECT p.*, pt.name as property_type, 
           a.id as agent_id, CONCAT(u.name) as agent_name, 
           u.email as agent_email, u.phone as agent_phone,
           u.profile_pic as agent_image,
           (SELECT COUNT(*) FROM properties WHERE agent_id = a.id) as agent_listings_count
           FROM properties p
           LEFT JOIN property_types pt ON p.type_id = pt.id
           LEFT JOIN agents a ON p.agent_id = a.id
           LEFT JOIN users u ON a.user_id = u.id
           WHERE p.id = :property_id");
$db->bind(':property_id', $property_id);
$property = $db->single();

// If property not found, redirect to properties page
if (!$property) {
    header('Location: properties.php');
    exit;
}

// Get property images
$db->query("SELECT * FROM property_images WHERE property_id = :property_id ORDER BY is_primary DESC, sort_order ASC");
$db->bind(':property_id', $property_id);
$property_images = $db->resultSet();

// Get property features
$db->query("SELECT pf.name, pfm.value 
           FROM property_feature_mapping pfm
           JOIN property_features pf ON pfm.feature_id = pf.id
           WHERE pfm.property_id = :property_id");
$db->bind(':property_id', $property_id);
$property_features = $db->resultSet();

// Get similar properties
$db->query("SELECT p.*, 
           (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
           FROM properties p 
           WHERE p.type_id = :type_id AND p.id != :property_id AND p.status = 'available' 
           ORDER BY p.featured DESC, p.created_at DESC 
           LIMIT 3");
$db->bind(':type_id', $property['type_id']);
$db->bind(':property_id', $property_id);
$similar_properties = $db->resultSet();

// Helper function to format price in Indian currency format
function formatIndianPrice($price) {
    if ($price >= 10000000) { // 1 crore
        return round($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) { // 1 lakh
        return round($price / 100000, 2) . ' L';
    } else {
        return '₹' . number_format($price);
    }
}

// Helper function to get the correct image URL
function getPropertyImageUrl($image_path) {
    if (empty($image_path)) {
        return DEFAULT_IMAGE_URL;
    }
    
    // Check if the image path already contains the full URL
    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
        return $image_path;
    }
    
    // Check if the image path has a leading slash
    if (strpos($image_path, '/') === 0) {
        $image_path = substr($image_path, 1);
    }
    
    // If the image path contains 'assets/images/properties/', extract just the filename
    if (strpos($image_path, 'assets/images/properties/') !== false) {
        $parts = explode('assets/images/properties/', $image_path);
        $image_path = end($parts);
    }
    
    // Return the full URL to the image
    return PROPERTY_IMAGES_URL . $image_path;
}

// Helper function to get the agent image URL
function getAgentImageUrl($agent_image, $agent_id) {
    if (!empty($agent_image)) {
        // Check if the image path already contains the full URL
        if (strpos($agent_image, 'http://') === 0 || strpos($agent_image, 'https://') === 0) {
            return $agent_image;
        }
        
        // Check if the path already contains 'assets/images/'
        if (strpos($agent_image, 'assets/images/') !== false) {
            // The image path already has the directory structure, so just return it
            // If your site is in a subdirectory, you might need to prepend the base URL
            return '/' . ltrim($agent_image, '/');
        }
        
        // Return the full URL to the agent image
        return AGENT_IMAGES_URL . $agent_image;
    } else {
        // Return default agent placeholder
        return AGENT_IMAGES_URL . 'agent-placeholder.jpg';
    }
}


// Include header
include "header.php";
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($property['title']); ?></h1>
        <p>
            <i class="fas fa-map-marker-alt"></i> 
            <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state']); ?>
            <?php if(!empty($property['zip_code'])): ?> - <?php echo htmlspecialchars($property['zip_code']); ?><?php endif; ?>
        </p>
    </div>
</section>

<!-- Property Details Section -->
<section class="property-details-section">
    <div class="container">
        <!-- Property Gallery Section -->
        <section class="property-gallery-section">
            <div class="container">
                <?php if (!empty($property_images)): ?>
                    <div class="gallery-container">
                        <div class="main-gallery">
                            <?php 
                            // Display the first image as the main image
                            if(isset($property_images[0])): 
                            ?>
                                <div class="gallery-main-image active" data-index="0">
                                    <img src="<?php echo getPropertyImageUrl($property_images[0]['image_path']); ?>" alt="Property Image 1">
                                </div>
                            <?php endif; ?>
                            
                            <?php 
                            // Create hidden containers for all other images
                            for($i = 1; $i < count($property_images); $i++): 
                            ?>
                                <div class="gallery-main-image" data-index="<?php echo $i; ?>" style="display: none;">
                                    <img src="<?php echo getPropertyImageUrl($property_images[$i]['image_path']); ?>" alt="Property Image <?php echo $i+1; ?>">
                                </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="thumbnail-gallery">
                            <div class="thumbnail-container">
                                <?php 
                                // Display the first 3 images as visible thumbnails
                                $maxVisibleThumbs = min(3, count($property_images));
                                for($i = 0; $i < $maxVisibleThumbs; $i++): 
                                ?>
                                    <div class="gallery-thumb <?php echo ($i == 0) ? 'active' : ''; ?>" data-index="<?php echo $i; ?>">
                                        <img src="<?php echo getPropertyImageUrl($property_images[$i]['image_path']); ?>" alt="Thumbnail <?php echo $i+1; ?>">
                                    </div>
                                <?php endfor; ?>
                                
                                <?php if (count($property_images) > 3): ?>
                                    <div class="gallery-thumb more-photos" id="viewMorePhotos">
                                        <div class="more-overlay">
                                            <span>+<?php echo count($property_images) - 3; ?> more</span>
                                        </div>
                                        <img src="<?php echo getPropertyImageUrl($property_images[3]['image_path']); ?>" alt="More Photos">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($property_images) > 3): ?>
                    <!-- Full Gallery Modal -->
                    <div id="galleryModal" class="gallery-modal">
                        <div class="modal-content">
                            <span class="close-modal">&times;</span>
                            <div class="modal-gallery">
                                <div class="modal-main-image">
                                    <img id="modalMainImage" src="<?php echo getPropertyImageUrl($property_images[0]['image_path']); ?>" alt="Property Image">
                                    <div class="nav-buttons">
                                        <button class="nav-button prev">&lt;</button>
                                        <button class="nav-button next">&gt;</button>
                                    </div>
                                </div>
                                <div class="modal-thumbnails">
                                    <?php foreach($property_images as $index => $image): ?>
                                        <div class="modal-thumb <?php echo ($index == 0) ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                            <img src="<?php echo getPropertyImageUrl($image['image_path']); ?>" alt="Thumbnail <?php echo $index+1; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="gallery-container">
                        <div class="main-gallery">
                            <div class="gallery-main-image active">
                                <img src="<?php echo DEFAULT_IMAGE_URL; ?>" alt="Default Property Image">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="property-details-container">
            <div class="property-main-details">
                <div class="property-header">
                    <div class="property-title-price">
                        <h2><?php echo htmlspecialchars($property['title']); ?></h2>
                        <div class="property-price"><?php echo formatIndianPrice($property['price']); ?></div>
                    </div>
                    <div class="property-actions">
                        <button class="btn btn-outline share-btn"><i class="fas fa-share-alt"></i> Share</button>
                    </div>
                </div>

                <div class="property-highlights">
                    <?php if (!empty($property['bedrooms'])): ?>
                    <div class="highlight-item">
                        <i class="fas fa-bed"></i>
                        <span class="highlight-value"><?php echo $property['bedrooms']; ?></span>
                        <span class="highlight-label">Bedrooms</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($property['bathrooms'])): ?>
                    <div class="highlight-item">
                        <i class="fas fa-bath"></i>
                        <span class="highlight-value"><?php echo $property['bathrooms']; ?></span>
                        <span class="highlight-label">Bathrooms</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($property['area'])): ?>
                    <div class="highlight-item">
                        <i class="fas fa-ruler-combined"></i>
                        <span class="highlight-value"><?php echo $property['area']; ?></span>
                        <span class="highlight-label"><?php echo $property['area_unit']; ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($property['facing'])): ?>
                    <div class="highlight-item">
                        <i class="fas fa-compass"></i>
                        <span class="highlight-value"><?php echo htmlspecialchars($property['facing']); ?></span>
                        <span class="highlight-label">Facing</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="highlight-item">
                        <i class="fas fa-home"></i>
                        <span class="highlight-value"><?php echo htmlspecialchars($property['property_type']); ?></span>
                        <span class="highlight-label">Type</span>
                    </div>
                    
                    <div class="highlight-item">
                        <i class="fas fa-tag"></i>
                        <span class="highlight-value status-<?php echo $property['status']; ?>"><?php echo ucfirst($property['status']); ?></span>
                        <span class="highlight-label">Status</span>
                    </div>
                </div>
                
                <div class="property-description">
                    <h3>Description</h3>
                    <div class="description-content">
                        <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                    </div>
                </div>
                
                <?php if (!empty($property_features)): ?>
                <div class="property-features">
                    <h3>Features & Amenities</h3>
                    <div class="features-grid">
                        <?php foreach ($property_features as $feature): ?>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <?php echo htmlspecialchars($feature['name']); ?>
                            <?php if (!empty($feature['value']) && $feature['value'] !== 'Yes'): ?>
                            : <?php echo htmlspecialchars($feature['value']); ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                
    <h3>Location</h3>
    <div class="location-details">
        <div class="location-address">
            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state']); ?>
            <?php if(!empty($property['zip_code'])): ?> - <?php echo htmlspecialchars($property['zip_code']); ?><?php endif; ?></p>
            
            <?php if(!empty($property['neighborhood'])): ?>
            <p><i class="fas fa-street-view"></i> <strong>Neighborhood:</strong> <?php echo htmlspecialchars($property['neighborhood']); ?></p>
            <?php endif; ?>
            
            <!-- Add nearby landmarks if available -->
            <?php if(!empty($property['landmarks'])): ?>
            <p><i class="fas fa-landmark"></i> <strong>Nearby:</strong> <?php echo htmlspecialchars($property['landmarks']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="location-map">
        <?php
        // Prepare address for Google Maps
        $map_address = urlencode($property['address'] . ', ' . $property['city'] . ', ' . $property['state']);
        $api_key = "YOUR_GOOGLE_MAPS_API_KEY"; // Replace with your actual API key
        
        // Check if coordinates are available directly
        if(!empty($property['latitude']) && !empty($property['longitude'])):
            $map_src = "https://www.google.com/maps/embed/v1/place?key={$api_key}&q={$property['latitude']},{$property['longitude']}&zoom=15";
        else:
            $map_src = "https://www.google.com/maps/embed/v1/place?key={$api_key}&q={$map_address}&zoom=15";
        endif;
        ?>
        
        <?php if(!empty($api_key) && $api_key != "YOUR_GOOGLE_MAPS_API_KEY"): ?>
        <!-- Google Maps iframe when API key is available -->
        <iframe 
            width="100%" 
            height="350" 
            frameborder="0" 
            style="border:0; border-radius: 10px;" 
            src="<?php echo $map_src; ?>" 
            allowfullscreen>
        </iframe>
        <?php else: ?>
        <!-- Fallback map placeholder when no API key is available -->
        <div class="map-placeholder">
            <div class="map-content">
                <i class="fas fa-map-marked-alt"></i>
                <h4>Location Map</h4>
                <p><?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state']); ?></p>
                <a href="https://maps.google.com/?q=<?php echo $map_address; ?>" target="_blank" class="btn btn-outline map-btn">
                    <i class="fas fa-external-link-alt"></i> View on Google Maps
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Location Advantages Section -->
    <?php if(!empty($property_features) && array_filter($property_features, function($feature) { 
        return strpos(strtolower($feature['name']), 'distance') !== false || 
               strpos(strtolower($feature['name']), 'nearby') !== false; 
    })): ?>
    <div class="location-advantages">
        <h4>Proximity Highlights</h4>
        <div class="advantages-grid">
            <?php foreach($property_features as $feature): ?>
                <?php if(strpos(strtolower($feature['name']), 'distance') !== false || 
                         strpos(strtolower($feature['name']), 'nearby') !== false): ?>
                <div class="advantage-item">
                    <?php 
                    // Choose icon based on feature name
                    $icon = 'fa-check';
                    if(strpos(strtolower($feature['name']), 'school') !== false) $icon = 'fa-school';
                    elseif(strpos(strtolower($feature['name']), 'hospital') !== false) $icon = 'fa-hospital';
                    elseif(strpos(strtolower($feature['name']), 'mall') !== false || 
                          strpos(strtolower($feature['name']), 'shop') !== false) $icon = 'fa-shopping-cart';
                    elseif(strpos(strtolower($feature['name']), 'airport') !== false) $icon = 'fa-plane';
                    elseif(strpos(strtolower($feature['name']), 'station') !== false) $icon = 'fa-train';
                    elseif(strpos(strtolower($feature['name']), 'park') !== false) $icon = 'fa-tree';
                    elseif(strpos(strtolower($feature['name']), 'beach') !== false) $icon = 'fa-umbrella-beach';
                    ?>
                    <i class="fas <?php echo $icon; ?>"></i>
                    <div class="advantage-content">
                        <span class="advantage-name"><?php echo htmlspecialchars($feature['name']); ?></span>
                        <?php if(!empty($feature['value']) && $feature['value'] !== 'Yes'): ?>
                            <span class="advantage-value"><?php echo htmlspecialchars($feature['value']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
            
            <div class="property-sidebar">
                <?php if (!empty($property['agent_id'])): ?>
                <div class="agent-card">
                    <h3>Property Agent</h3>
                    <div class="agent-info">
                        <div class="agent-image">
                            <?php 
                            $agent_image_url = '';
                            if (!empty($property['agent_image'])) {
                                // Directly use the profile_pic from the database if it exists
                                $agent_image_url = $property['agent_image'];
                                
                                // Check if path needs adjustment
                                if (strpos($agent_image_url, 'assets/images/') === false && 
                                    strpos($agent_image_url, 'http://') !== 0 && 
                                    strpos($agent_image_url, 'https://') !== 0) {
                                    // Add full path if it's just a filename
                                    $agent_image_url = 'assets/images/agents/' . $agent_image_url;
                                }
                            } else {
                                // Default placeholder
                                $agent_image_url = 'assets/images/agents/agent-placeholder.jpg';
                            }
                            ?>
                            <img src="<?php echo $agent_image_url; ?>" alt="Agent" 
                                 onerror="this.onerror=null; this.src='assets/images/agents/agent-placeholder.jpg';">
                        </div>
                        <div class="agent-details">
                            <h4><?php echo htmlspecialchars($property['agent_name'] ?? 'Agent Name Not Available'); ?></h4>
                            <p class="agent-listings"><?php echo $property['agent_listings_count'] ?? 0; ?> Properties</p>
                            <div class="agent-contact">
                                <?php if (!empty($property['agent_phone'])): ?>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($property['agent_phone']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($property['agent_email'])): ?>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($property['agent_email']); ?></p>
                                <?php endif; ?>
                            </div>
                            <a href="agent-details.php?id=<?php echo $property['agent_id']; ?>" class="btn btn-outline view-profile-btn">View Profile</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- <div class="contact-form-card">
                    <h3>Request Information</h3>
                    <form class="contact-form" id="propertyInquiryForm">
                        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                        
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="tel" name="phone" placeholder="Your Phone">
                        </div>
                        
                        <div class="form-group">
                            <textarea name="message" rows="4" placeholder="Your Message" required>I'm interested in this property. Please contact me with more information.</textarea>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div> -->
                
                <!-- <div class="property-share">
                    <h3>Share This Property</h3>
                    <div class="social-links">
                        <a href="#" class="facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="linkedin"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</section>

<!-- Similar Properties Section -->
<?php if (!empty($similar_properties)): ?>
<section class="similar-properties">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <div class="dot"></div>
                <span>Similar Properties</span>
            </div>
            <h2>You May Also Like</h2>
        </div>
        
        <div class="grid-container">
            <?php foreach ($similar_properties as $similar): ?>
            <div class="property-card">
                <?php if (!empty($similar['featured']) && $similar['featured'] == 1): ?>
                <div class="property-featured"></div>
                <?php endif; ?>
                
                <div class="property-status status-<?php echo strtolower($similar['status']); ?>">
                    <?php echo ucfirst($similar['status']); ?>
                </div>
                
                <div class="property-images">
                    <img src="<?php echo getPropertyImageUrl($similar['primary_image']); ?>" 
                         alt="<?php echo htmlspecialchars($similar['title']); ?>">
                </div>
                
                <div class="property-info">
                    <h3><?php echo htmlspecialchars($similar['title']); ?></h3>
                    <div class="property-price"><?php echo formatIndianPrice($similar['price']); ?></div>
                    <div class="property-details-row">
                        <?php if (!empty($similar['bedrooms'])): ?>
                        <span><i class="fas fa-bed"></i> <?php echo $similar['bedrooms']; ?> Beds</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($similar['bathrooms'])): ?>
                        <span><i class="fas fa-bath"></i> <?php echo $similar['bathrooms']; ?> Baths</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($similar['area'])): ?>
                        <span><i class="fas fa-ruler-combined"></i> <?php echo $similar['area']; ?> <?php echo $similar['area_unit']; ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="property-location">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($similar['city']); ?>, <?php echo htmlspecialchars($similar['state']); ?>
                    </p>
                    <a href="property-details.php?id=<?php echo $similar['id']; ?>" class="view-details-btn">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- JavaScript for Property Gallery -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Main gallery functionality
    initMainGallery();
    
    // Modal gallery functionality
    initModalGallery();
});

function initMainGallery() {
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const mainImages = document.querySelectorAll('.gallery-main-image');
    const viewMoreBtn = document.getElementById('viewMorePhotos');
    
    // Add click event to each thumbnail
    galleryThumbs.forEach(thumb => {
        if (!thumb.classList.contains('more-photos')) {
            thumb.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                
                // Update active thumbnail
                galleryThumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update main image
                mainImages.forEach(img => {
                    if (img.getAttribute('data-index') === index) {
                        img.style.display = 'block';
                    } else {
                        img.style.display = 'none';
                    }
                });
            });
        }
    });
    
    // "View more photos" button
    if (viewMoreBtn) {
        viewMoreBtn.addEventListener('click', function() {
            const modal = document.getElementById('galleryModal');
            if (modal) {
                modal.style.display = 'block';
                
                // Prevent body scrolling when modal is open
                document.body.style.overflow = 'hidden';
            }
        });
    }
}

function initModalGallery() {
    const modal = document.getElementById('galleryModal');
    if (!modal) return;
    
    const closeBtn = document.querySelector('.close-modal');
    const modalThumbs = document.querySelectorAll('.modal-thumb');
    const mainModalImg = document.getElementById('modalMainImage');
    const prevBtn = document.querySelector('.nav-button.prev');
    const nextBtn = document.querySelector('.nav-button.next');
    
    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // Click outside to close
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Escape key to close
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Thumbnail click in modal
    modalThumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            updateModalImage(index, modalThumbs, mainModalImg);
        });
    });
    
    // Previous button
    prevBtn.addEventListener('click', function() {
        const currentIndex = getCurrentIndex(modalThumbs);
        const newIndex = (currentIndex - 1 + modalThumbs.length) % modalThumbs.length;
        updateModalImage(newIndex, modalThumbs, mainModalImg);
    });
    
    // Next button
    nextBtn.addEventListener('click', function() {
        const currentIndex = getCurrentIndex(modalThumbs);
        const newIndex = (currentIndex + 1) % modalThumbs.length;
        updateModalImage(newIndex, modalThumbs, mainModalImg);
    });
    
    // Left/right arrow keys for navigation
    window.addEventListener('keydown', function(event) {
        if (modal.style.display !== 'block') return;
        
        if (event.key === 'ArrowLeft') {
            prevBtn.click();
        } else if (event.key === 'ArrowRight') {
            nextBtn.click();
        }
    });
}

// Helper Functions
function getCurrentIndex(thumbnails) {
    for (let i = 0; i < thumbnails.length; i++) {
        if (thumbnails[i].classList.contains('active')) {
            return parseInt(thumbnails[i].getAttribute('data-index'));
        }
    }
    return 0;
}

function updateModalImage(index, thumbnails, mainImage) {
    // Update active thumbnail
    thumbnails.forEach(thumb => {
        if (thumb.getAttribute('data-index') === index.toString()) {
            thumb.classList.add('active');
            // Also scroll this thumbnail into view
            thumb.scrollIntoView({ behavior: 'smooth', inline: 'center' });
        } else {
            thumb.classList.remove('active');
        }
    });
    
    // Update main image
    const selectedThumb = document.querySelector(`.modal-thumb[data-index="${index}"] img`);
    if (selectedThumb && mainImage) {
        mainImage.src = selectedThumb.src;
        mainImage.alt = selectedThumb.alt;
    }
}
</script>

<?php include 'footer.php'; ?>

<style>
        /* Complete Property Details Page CSS */
    /* Main Variables */
    :root {
        --primary-color: #0056b3;
        --secondary-color: #f9f9fb;
        --white: #ffffff;
        --gray: #dddddd;
        --gray-light: #f5f5f5;
        --text-dark: #333333;
        --text-light: #666666;
        --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* ===== Property Gallery Section ===== */
    .property-gallery-section {
        padding: 20px 0;
    }

    .property-gallery-section .container {
        padding: 0;
    }

    .gallery-container {
        width: 100%;
        margin-bottom: 20px;
    }

    /* Main Gallery Image - REDUCED HEIGHT */
    .main-gallery {
        position: relative;
        width: 100%;
        height: 400px; /* Reduced height from 500px */
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .gallery-main-image {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        transition: opacity 0.5s ease;
    }

    /* Ensure full image fit with object-fit: contain */
    .gallery-main-image img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Changed from cover to contain to fit full image */
        background-color: #f8f8f8; /* Light background for images with transparency */
    }

    /* Thumbnail Gallery */
    .thumbnail-gallery {
        width: 100%;
        padding: 5px 0;
    }

    .thumbnail-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        height: 100px; /* Reduced thumbnail height */
    }

    .gallery-thumb {
        height: 100%;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-thumb:hover img {
        transform: scale(1.05);
    }

    .gallery-thumb.active {
        border: 3px solid var(--primary-color);
    }

    /* View more photos button */
    .more-photos {
        position: relative;
    }

    .more-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
        z-index: 2;
    }

    /* Gallery Modal */
    .gallery-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        overflow: hidden;
    }

    .modal-content {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .close-modal {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10;
    }

    .modal-gallery {
        width: 90%;
        height: 90%;
        display: flex;
        flex-direction: column;
    }

    .modal-main-image {
        position: relative;
        height: 80%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-main-image img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain; /* Ensure full image is visible in modal */
    }

    .nav-buttons {
        position: absolute;
        width: 100%;
        display: flex;
        justify-content: space-between;
        padding: 0 20px;
    }

    .nav-button {
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 24px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .nav-button:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-thumbnails {
        height: 100px;
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 5px 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.5) transparent;
    }

    .modal-thumbnails::-webkit-scrollbar {
        height: 6px;
    }

    .modal-thumbnails::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-thumbnails::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 6px;
    }

    .modal-thumb {
        height: 100%;
        min-width: 150px;
        border-radius: 5px;
        overflow: hidden;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }

    .modal-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .modal-thumb.active {
        opacity: 1;
        border: 2px solid white;
    }

    /* Optional: Add subtle enhancement to main gallery image */
    .main-gallery::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.1);
        pointer-events: none;
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        .main-gallery {
            height: 350px;
        }
        
        .thumbnail-container {
            height: 90px;
        }
    }

    @media (max-width: 768px) {
        .main-gallery {
            height: 300px;
        }
        
        .thumbnail-container {
            height: 80px;
        }
        
        .modal-thumbnails {
            height: 80px;
        }
        
        .modal-thumb {
            min-width: 120px;
        }
    }

    @media (max-width: 576px) {
        .main-gallery {
            height: 250px;
        }
        
        .thumbnail-container {
            height: 70px;
            grid-template-columns: repeat(3, 1fr);
        }
        
        .nav-button {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
        
        .modal-thumbnails {
            height: 60px;
        }
        
        .modal-thumb {
            min-width: 90px;
        }
    }

    .modal-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .modal-thumb.active {
        opacity: 1;
        border: 2px solid white;
    }

    /* ===== Property Details Section ===== */
    .property-details-section {
        padding: 60px 0;
    }

    .property-details-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-top: 30px;
    }

    .property-main-details {
        flex: 1;
        min-width: 60%;
        background-color: var(--white);
        border-radius: 10px;
        padding: 25px;
        box-shadow: var(--box-shadow);
    }

    .property-sidebar {
        width: 300px;
    }

    /* Property Header */
    .property-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .property-title-price h2 {
        margin-bottom: 5px;
        font-size: 24px;
        color: var(--text-dark);
    }

    .property-price {
        color: var(--primary-color);
        font-size: 1.5rem;
        font-weight: 600;
    }

    .property-actions {
        display: flex;
        gap: 10px;
    }

    /* Property Highlights */
    .property-highlights {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background-color: var(--secondary-color);
        border-radius: 10px;
    }

    .highlight-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .highlight-item i {
        color: var(--primary-color);
        font-size: 24px;
        margin-bottom: 5px;
    }

    .highlight-value {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .highlight-label {
        font-size: 0.8rem;
        color: var(--text-light);
    }

    /* Status Colors */
    .status-available {
        color: #27ae60;
    }

    .status-sold {
        color: #e74c3c;
    }

    .status-pending {
        color: #f39c12;
    }

    .status-rented {
        color: #3498db;
    }

    /* Property Content Sections */
    .property-description,
    .property-features,
    .property-location {
        margin-bottom: 30px;
    }

    .property-description h3,
    .property-features h3,
    .property-location h3 {
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .description-content {
        line-height: 1.8;
        color: var(--text-light);
    }

    /* Features Grid */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
        background-color: var(--gray-light);
    }

    .feature-item i {
        color: var(--primary-color);
        margin-right: 10px;
    }

    /* Location Map */
    .location-map {
        height: 300px;
        border-radius: 10px;
        overflow: hidden;
        background-color: var(--gray-light);
    }

    .map-placeholder {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .map-placeholder p:first-child {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    /* ===== Sidebar Components ===== */
    .agent-card,
    .contact-form-card,
    .property-share {
        background-color: var(--white);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: var(--box-shadow);
    }

    .agent-card h3,
    .contact-form-card h3,
    .property-share h3 {
        font-size: 1.2rem;
        margin-bottom: 15px;
    }

    /* Agent Card */
    .agent-info {
        display: flex;
        gap: 15px;
        margin-top: 15px;
        align-items: center;
        margin-bottom: 15px;
    }

    .agent-image {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
    }

    .agent-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .agent-details {
        flex: 1;
    }

    .agent-details h4 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 16px;
    }

    .agent-listings {
        color: var(--primary-color);
        font-weight: 500;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .agent-contact p {
        margin: 5px 0;
        font-size: 14px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
    }

    .agent-contact p i {
        color: var(--primary-color);
        margin-right: 10px;
        width: 15px;
    }

    .view-profile-btn {
        margin-top: 10px;
        display: inline-block;
        padding: 8px 15px;
        font-size: 14px;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.3s;
        width: 100%;
        text-align: center;
    }

    .view-profile-btn:hover {
        background-color: var(--primary-color);
        color: var(--white);
    }

    /* Contact Form */
    .contact-form .form-group {
        margin-bottom: 15px;
    }

    .contact-form input,
    .contact-form textarea,
    .contact-form select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--gray);
        border-radius: 5px;
        font-family: 'Urbanist', sans-serif;
        font-size: 14px;
    }

    .contact-form textarea {
        resize: vertical;
    }

    .checkbox-group {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .checkbox-group input {
        width: auto;
        margin-right: 10px;
        margin-top: 5px;
    }

    .checkbox-group label {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .contact-form button {
        width: 100%;
    }

    /* Property Share */
    .property-share .social-links {
        display: flex;
        justify-content: space-between;
    }

    .property-share .social-links a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--gray-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        transition: all 0.3s ease;
    }

    .property-share .social-links a:hover {
        color: var(--white);
    }

    .property-share .social-links a.facebook:hover {
        background-color: #3b5998;
    }

    .property-share .social-links a.twitter:hover {
        background-color: #1da1f2;
    }

    .property-share .social-links a.whatsapp:hover {
        background-color: #25d366;
    }

    .property-share .social-links a.linkedin:hover {
        background-color: #0077b5;
    }

    /* ===== Similar Properties Section ===== */
    .similar-properties {
        padding: 60px 0;
        background-color: var(--gray-light);
    }

    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .section-tag {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .section-tag .dot {
        width: 8px;
        height: 8px;
        background-color: var(--primary-color);
        border-radius: 50%;
        margin-right: 8px;
    }

    .section-tag span {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--primary-color);
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    .property-card {
        background-color: var(--white);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .property-featured {
        position: absolute;
        top: 15px;
        left: 15px;
        background-color: var(--primary-color);
        color: var(--white);
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 2;
    }

    .property-status {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        z-index: 2;
        background-color: rgba(255, 255, 255, 0.9);
    }

    .property-images {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .property-images img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .property-card:hover .property-images img {
        transform: scale(1.1);
    }

    .property-info {
        padding: 20px;
    }

    .property-info h3 {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 18px;
        color: var(--text-dark);
    }

    .property-details-row {
        display: flex;
        justify-content: space-between;
        margin: 15px 0;
    }

    .property-details-row span {
        font-size: 14px;
        color: var(--text-light);
        display: flex;
        align-items: center;
    }

    .property-details-row span i {
        margin-right: 5px;
        color: var(--primary-color);
    }

    .property-location {
        font-size: 14px;
        color: var(--text-light);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .property-location i {
        margin-right: 5px;
        color: var(--primary-color);
    }

    .view-details-btn {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: var(--primary-color);
        color: var(--white);
        text-align: center;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .view-details-btn:hover {
        background-color: #004494;
    }

    /* ===== Buttons ===== */
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: var(--white);
        border: none;
    }

    .btn-primary:hover {
        background-color: #004494;
    }

    .btn-outline {
        background-color: transparent;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    .btn-outline:hover {
        background-color: var(--primary-color);
        color: var(--white);
    }

    /* ===== Responsive Styles ===== */
    @media (max-width: 992px) {
        .property-details-container {
            grid-template-columns: 1fr;
            flex-direction: column;
        }
        
        .property-sidebar {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .main-gallery {
            height: 400px;
        }
        
        .agent-card,
        .contact-form-card,
        .property-share {
            margin-bottom: 0;
        }
        
        .grid-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .thumbnail-container {
            height: 120px;
        }
        
        .main-gallery {
            height: 350px;
        }
        
        .property-header {
            flex-direction: column;
            gap: 15px;
        }
        
        .property-actions {
            width: 100%;
        }
        
        .property-actions .btn {
            flex: 1;
        }
        
        .property-highlights {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .features-grid {
            grid-template-columns: 1fr;
        }
        
        .property-sidebar {
            grid-template-columns: 1fr;
        }
        
        .grid-container {
            grid-template-columns: 1fr;
        }
        
        .modal-thumbnails {
            height: 80px;
        }
        
        .modal-thumb {
            min-width: 120px;
        }
    }

    @media (max-width: 576px) {
        .main-gallery {
            height: 300px;
        }
        
        .thumbnail-container {
            height: 80px;
            grid-template-columns: repeat(3, 1fr);
        }
        
        .property-highlights {
            grid-template-columns: repeat(2, 1fr);
            padding: 15px;
            gap: 15px;
        }
        
        .nav-button {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
        
        .modal-thumbnails {
            height: 60px;
        }
        
        .modal-thumb {
            min-width: 90px;
        }
        
        .gallery-thumb {
            width: 70px;
            height: 50px;
        }
        
        .section-header {
            margin-bottom: 30px;
        }
        
        .property-images {
            height: 180px;
        }
    }

    /* Fix for IE11 */
    @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
        .gallery-main-image {
            position: relative;
            display: none;
        }
        
        .gallery-main-image.active {
            display: block;
        }
        
        .property-details-container {
            display: -ms-grid;
            -ms-grid-columns: 2fr 1fr;
        }
        
        .property-highlights {
            display: -ms-grid;
            -ms-grid-columns: 1fr 1fr 1fr;
        }
    }    
</style>
