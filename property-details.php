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

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id > 0) {
 
    
    
    // Check property status
    $db->query("SELECT status FROM properties WHERE id = :id LIMIT 1");
    $db->bind(':id', $property_id);
    $property = $db->single();
    
    // If property exists and status is 'rent', check admin authentication
    if ($property && $property['status'] == 'rent') {
        // Check if user is logged in as admin
        $is_admin = false;
        
        // Method 1: Check if admin session exists
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $is_admin = true;
        }
        
        // Method 2: Alternative - check for admin user session
        // if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        //     $is_admin = true;
        // }
        
        // Method 3: Alternative - check for specific admin ID
        // if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
        //     $is_admin = true;
        // }
        
        // If not admin, redirect to login
        if (!$is_admin) {
            // Store the current URL to redirect back after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            // Redirect to admin login
            header('Location: admin/login.php');
            exit();
        }
    }
    
    // If property doesn't exist, you might want to handle this case
    if (!$property) {
        // Redirect to 404 or properties page
        header('Location: properties.php');
        exit();
    }
}



// Get property details with complete agent information and property Instagram URL
$db->query("SELECT p.*, pt.name as property_type, p.instagram_url as property_instagram,
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

// Get similar properties based on location, BHK, and price range
$price_min = $property['price'] * 0.7;  // 30% below current price
$price_max = $property['price'] * 1.3;  // 30% above current price
$bedrooms_min = max(1, $property['bedrooms'] - 1);  // ±1 bedroom
$bedrooms_max = $property['bedrooms'] + 1;

$similar_query = "
    SELECT p.*, 
           (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
           (
               -- Scoring system for relevance
               CASE WHEN p.city = :city THEN 3 ELSE 0 END +
               CASE WHEN p.bedrooms BETWEEN :bedrooms_min AND :bedrooms_max THEN 2 ELSE 0 END +
               CASE WHEN p.price BETWEEN :price_min AND :price_max THEN 2 ELSE 0 END +
               CASE WHEN p.type_id = :type_id THEN 1 ELSE 0 END +
               CASE WHEN p.featured = 1 THEN 1 ELSE 0 END
           ) as relevance_score
    FROM properties p 
    WHERE p.id != :property_id 
      AND p.status IN ('buy', 'rent')
      AND (
          p.city = :city OR 
          p.bedrooms BETWEEN :bedrooms_min AND :bedrooms_max OR
          p.price BETWEEN :price_min AND :price_max OR
          p.type_id = :type_id
      )
    ORDER BY relevance_score DESC, p.featured DESC, p.created_at DESC 
    LIMIT 6";

$db->query($similar_query);
$db->bind(':property_id', $property_id);
$db->bind(':city', $property['city']);
$db->bind(':bedrooms_min', $bedrooms_min);
$db->bind(':bedrooms_max', $bedrooms_max);
$db->bind(':price_min', $price_min);
$db->bind(':price_max', $price_max);
$db->bind(':type_id', $property['type_id']);
$similar_properties = $db->resultSet();

// If we don't have enough similar properties, get some backup properties
if (count($similar_properties) < 3) {
    $backup_query = "
        SELECT p.*, 
               (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
               0 as relevance_score
        FROM properties p 
        WHERE p.id != :property_id 
          AND p.status IN ('buy', 'rent')
          AND p.id NOT IN (" . implode(',', array_column($similar_properties, 'id')) . (empty($similar_properties) ? '0' : '') . ")
        ORDER BY p.featured DESC, p.created_at DESC 
        LIMIT " . (6 - count($similar_properties));
    
    $db->query($backup_query);
    $db->bind(':property_id', $property_id);
    $backup_properties = $db->resultSet();
    
    $similar_properties = array_merge($similar_properties, $backup_properties);
}

// Limit to 6 properties maximum
$similar_properties = array_slice($similar_properties, 0, 6);

// Helper function to format price in Indian currency format
function formatIndianPrice($price) {
    if ($price >= 10000000) { // 1 crore
        return '₹' . round($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) { // 1 lakh
        return '₹' . round($price / 100000, 2) . ' L';
    } else {
        return '₹' . number_format($price);
    }
}



// Helper function to format Instagram handle
function formatInstagramHandle($instagram_url) {
    if (empty($instagram_url)) {
        return '';
    }
    
    // Extract username from Instagram URL
    if (strpos($instagram_url, 'instagram.com/') !== false) {
        $parts = explode('instagram.com/', $instagram_url);
        if (isset($parts[1])) {
            $username = trim($parts[1], '/');
            // Remove any additional path parameters
            $username = explode('/', $username)[0];
            return '@' . $username;
        }
    }
    
    return 'Instagram';
}

// Helper function to get status display info
function getStatusInfo($status) {
    $status_info = [
        'buy' => ['text' => 'For Sale', 'class' => 'buy'],
        'rent' => ['text' => 'For Rent', 'class' => 'rent'],
        'pending' => ['text' => 'Pending', 'class' => 'pending'],
        'sold' => ['text' => 'Sold', 'class' => 'sold'],
        'rented' => ['text' => 'Rented', 'class' => 'rented']
    ];
    
    return $status_info[$status] ?? ['text' => ucfirst($status), 'class' => $status];
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
                                <img src="assets/images/no-image.jpg" alt="Default Property Image">
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
                        <button class="btn btn-outline share-btn" onclick="shareProperty()">
                            <i class="fas fa-share-alt"></i> Share
                        </button>
                        <?php if (!empty($property['property_instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($property['property_instagram']); ?>" 
                           target="_blank" rel="noopener noreferrer" class="btn btn-outline instagram-btn">
                            <i class="fab fa-instagram"></i> View on Instagram
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($property['phone_number'])): ?>
                        <!-- <a href="tel:<?php echo htmlspecialchars($property['phone_number']); ?>" 
                           class="btn btn-outline phone-btn">
                            <i class="fas fa-phone"></i> Call
                        </a> -->
                        <?php endif; ?>
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
                    
                    <?php 
                    $status_info = getStatusInfo($property['status']);
                    ?>
                    <div class="highlight-item">
                        <i class="fas fa-tag"></i>
                        <span class="highlight-value status-<?php echo $status_info['class']; ?>"><?php echo $status_info['text']; ?></span>
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
                            <?php if (!empty($feature['value']) && strtolower($feature['value']) !== 'yes'): ?>
                            : <?php echo htmlspecialchars($feature['value']); ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="property-location">
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
                                        <?php if(!empty($feature['value']) && strtolower($feature['value']) !== 'yes'): ?>
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
            </div>
            
            <div class="property-sidebar">
                <?php if (!empty($property['agent_id'])): ?>
                <div class="agent-card">
                    <h3>Property Agent</h3>
                    <div class="agent-info">
                        <div class="agent-image">
                            <?php 
                            $agent_image_url = getAgentImageUrl($property['agent_image']);
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
                
                <?php if (!empty($property['property_instagram'])): ?>
                <div class="property-instagram-card">
                    <h3>More Photos & Updates</h3>
                    <div class="instagram-content">
                        <div class="instagram-info">
                            <i class="fab fa-instagram"></i>
                            <div class="instagram-text">
                                <h4>Follow us on Instagram</h4>
                                <p>See more photos, virtual tours, and updates about this property</p>
                                <span class="instagram-handle"><?php echo formatInstagramHandle($property['property_instagram']); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo htmlspecialchars($property['property_instagram']); ?>" 
                           target="_blank" rel="noopener noreferrer" class="btn btn-primary instagram-follow-btn">
                            <i class="fab fa-instagram"></i> View on Instagram
                        </a>
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
                </div>
                
                <div class="property-share">
                    <h3>Share This Property</h3>
                    <div class="social-links">
                        <a href="#" class="facebook" onclick="shareOnFacebook()"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="twitter" onclick="shareOnTwitter()"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="whatsapp" onclick="shareOnWhatsApp()"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="linkedin" onclick="shareOnLinkedIn()"><i class="fab fa-linkedin-in"></i></a>
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
                <div class="property-featured">Featured</div>
                <?php endif; ?>
                
                <?php 
                $status_info = getStatusInfo($similar['status']);
                ?>
                <div class="property-status status-<?php echo $status_info['class']; ?>">
                    <?php echo $status_info['text']; ?>
                </div>
                
                <div class="property-images">
                    <img src="<?php echo getPropertyImageUrl($similar['primary_image']); ?>" 
                         alt="<?php echo htmlspecialchars($similar['title']); ?>"
                         onerror="this.onerror=null; this.src='assets/images/no-image.jpg';">
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

<!-- Additional CSS -->
<style>
/* Property Details Specific Styles */
.property-details-section {
    padding: 2rem 0;
}

.property-details-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    margin-top: 2rem;
}

.property-main-details {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 2rem;
}

.property-title-price h2 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 2rem;
}

.property-price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #28a745;
}

.property-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Button Styles */
.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border-radius: 5px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-primary {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
}

.btn-outline {
    background: transparent;
    color: #007bff;
    border-color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
}

/* Instagram Button */
.instagram-btn {
    background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
    color: white;
    border: none;
}

.instagram-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(188, 24, 136, 0.3);
    color: white;
}

/* Phone Button */
.phone-btn {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.phone-btn:hover {
    background: #218838;
    border-color: #1e7e34;
    color: white;
}

.property-highlights {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.highlight-item {
    text-align: center;
}

.highlight-item i {
    font-size: 2rem;
    color: #007bff;
    margin-bottom: 0.5rem;
    display: block;
}

.highlight-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.25rem;
}

.highlight-label {
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Status Colors */
.status-buy {
    color: #28a745;
}

.status-rent {
    color: #007bff;
}

.status-pending {
    color: #ffc107;
}

.status-sold {
    color: #dc3545;
}

.status-rented {
    color: #6c757d;
}

.property-description {
    margin-bottom: 2rem;
}

.property-description h3 {
    margin-bottom: 1rem;
    color: #333;
}

.description-content {
    line-height: 1.6;
    color: #666;
}

.property-features {
    margin-bottom: 2rem;
}

.property-features h3 {
    margin-bottom: 1rem;
    color: #333;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.feature-item i {
    color: #28a745;
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

.property-location h3 {
    margin-bottom: 1rem;
    color: #333;
}

.location-details {
    margin-bottom: 2rem;
}

.location-address p {
    margin-bottom: 0.75rem;
    color: #666;
}

.location-address i {
    color: #007bff;
    margin-right: 0.5rem;
    width: 20px;
}

.location-map {
    margin-bottom: 2rem;
}

.map-placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 4rem 2rem;
    text-align: center;
}

.map-content i {
    font-size: 4rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.map-content h4 {
    margin-bottom: 1rem;
    color: #333;
}

.map-content p {
    color: #666;
    margin-bottom: 2rem;
}

.location-advantages {
    margin-top: 2rem;
}

.location-advantages h4 {
    margin-bottom: 1rem;
    color: #333;
}

.advantages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.advantage-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.advantage-item i {
    color: #007bff;
    margin-right: 1rem;
    font-size: 1.2rem;
    width: 20px;
}

.advantage-content {
    flex: 1;
}

.advantage-name {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.advantage-value {
    font-size: 0.9rem;
    color: #666;
}

/* Sidebar Styles */
.property-sidebar {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.agent-card,
.contact-form-card,
.property-share,
.property-instagram-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.agent-card h3,
.contact-form-card h3,
.property-share h3,
.property-instagram-card h3 {
    margin: 0 0 1rem 0;
    color: #333;
}

.agent-info {
    display: flex;
    gap: 1rem;
}

.agent-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.agent-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agent-details h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.agent-listings {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.agent-contact p {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: #666;
    display: flex;
    align-items: center;
}

.agent-contact i {
    margin-right: 0.5rem;
    width: 16px;
    color: #007bff;
}

.view-profile-btn {
    margin-top: 1rem;
    width: 100%;
    text-align: center;
}

/* Property Instagram Card */
.property-instagram-card {
    background: linear-gradient(135deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
    color: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.property-instagram-card h3 {
    color: white;
    margin-bottom: 1rem;
}

.instagram-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.instagram-info {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.instagram-info i {
    font-size: 3rem;
    color: white;
    flex-shrink: 0;
}

.instagram-text h4 {
    margin: 0 0 0.5rem 0;
    color: white;
    font-size: 1.2rem;
}

.instagram-text p {
    margin: 0 0 0.5rem 0;
    color: rgba(255,255,255,0.9);
    font-size: 0.9rem;
    line-height: 1.4;
}

.instagram-handle {
    font-weight: 600;
    color: white;
    font-size: 1rem;
}

.instagram-follow-btn {
    background: white;
    color: #bc1888;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.instagram-follow-btn:hover {
    background: rgba(255,255,255,0.9);
    color: #bc1888;
    transform: translateY(-2px);
}

/* Contact Form */
.contact-form .form-group {
    margin-bottom: 1rem;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #007bff;
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.checkbox-group label {
    font-size: 0.85rem;
    color: #666;
    line-height: 1.4;
}

.checkbox-group a {
    color: #007bff;
}

/* Social Links */
.social-links {
    display: flex;
    gap: 0.5rem;
}

.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.social-links a:hover {
    transform: translateY(-2px);
}

.social-links .facebook {
    background: #3b5998;
}

.social-links .twitter {
    background: #1da1f2;
}

.social-links .whatsapp {
    background: #25d366;
}

.social-links .linkedin {
    background: #0077b5;
}

/* Gallery Styles */
.gallery-container {
    margin-bottom: 2rem;
}

.main-gallery {
    position: relative;
    height: 500px;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.gallery-main-image {
    width: 100%;
    height: 100%;
}

.gallery-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-gallery {
    display: flex;
    gap: 1rem;
}

.thumbnail-container {
    display: flex;
    gap: 1rem;
    flex: 1;
}

.gallery-thumb {
    width: 120px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid transparent;
    transition: border-color 0.3s ease;
    position: relative;
}

.gallery-thumb:hover,
.gallery-thumb.active {
    border-color: #007bff;
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.more-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

/* Modal Styles */
.gallery-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
}

.modal-content {
    position: relative;
    width: 90%;
    height: 90%;
    margin: 5% auto;
    background: white;
    border-radius: 10px;
    overflow: hidden;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10001;
}

.modal-main-image {
    position: relative;
    height: 70%;
    background: black;
}

.modal-main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.nav-buttons {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 2rem;
}

.nav-button {
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.nav-button:hover {
    background: rgba(0,0,0,0.8);
}

.modal-thumbnails {
    height: 30%;
    padding: 1rem;
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    background: #f8f9fa;
}

.modal-thumb {
    width: 120px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid transparent;
    flex-shrink: 0;
    transition: border-color 0.3s ease;
}

.modal-thumb:hover,
.modal-thumb.active {
    border-color: #007bff;
}

.modal-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Similar Properties */
.similar-properties {
    padding: 3rem 0;
    background: #f8f9fa;
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.section-tag .dot {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.property-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    position: relative;
}

.property-card:hover {
    transform: translateY(-5px);
}

.property-featured {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: linear-gradient(45deg, #ff6b6b, #feca57);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

.property-status {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    z-index: 2;
}

.status-buy {
    background: #d4edda;
    color: #155724;
}

.status-rent {
    background: #cce5ff;
    color: #004085;
}

.status-sold {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-rented {
    background: #e2e3e5;
    color: #383d41;
}

.property-images {
    height: 200px;
    overflow: hidden;
}

.property-images img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card:hover .property-images img {
    transform: scale(1.05);
}

.property-info {
    padding: 1.5rem;
}

.property-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
}

.property-info .property-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 1rem;
}

.property-details-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.property-details-row span {
    font-size: 0.9rem;
    color: #666;
    display: flex;
    align-items: center;
}

.property-details-row i {
    margin-right: 0.25rem;
    color: #007bff;
}

.property-location {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.view-details-btn {
    display: inline-block;
    background: #007bff;
    color: white;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    transition: background 0.3s ease;
}

.view-details-btn:hover {
    background: #0056b3;
    color: white;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .property-details-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .property-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .property-price {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .property-highlights {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .highlight-item i {
        font-size: 1.5rem;
    }
    
    .highlight-value {
        font-size: 1.2rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .advantages-grid {
        grid-template-columns: 1fr;
    }
    
    .agent-info {
        flex-direction: column;
        text-align: center;
    }
    
    .thumbnail-container {
        flex-wrap: wrap;
    }
    
    .gallery-thumb {
        width: calc(50% - 0.5rem);
    }
    
    .main-gallery {
        height: 300px;
    }
    
    .property-actions {
        justify-content: flex-start;
    }
    
    .instagram-info {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .property-highlights {
        grid-template-columns: 1fr;
    }
    
    .property-title-price h2 {
        font-size: 1.5rem;
    }
    
    .property-price {
        font-size: 1.8rem;
    }
    
    .gallery-thumb {
        width: 100%;
    }
    
    .property-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<!-- JavaScript for Property Gallery and Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Main gallery functionality
    initMainGallery();
    
    // Modal gallery functionality
    initModalGallery();
    
    // Property inquiry form
    initInquiryForm();
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

function initInquiryForm() {
    const form = document.getElementById('propertyInquiryForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(form);
            
            // Here you would typically send the data to your server
            // For now, we'll just show a success message
            alert('Thank you for your inquiry! We will contact you soon.');
            
            // Reset form
            form.reset();
        });
    }
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

// Social Sharing Functions
function shareProperty() {
    const url = window.location.href;
    const title = document.querySelector('h1').textContent;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(function() {
            alert('Property link copied to clipboard!');
        });
    }
}

function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.querySelector('h1').textContent);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${title}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.querySelector('h1').textContent);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.querySelector('h1').textContent);
    window.open(`https://wa.me/?text=${title} ${url}`, '_blank');
}

function shareOnLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.querySelector('h1').textContent);
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank', 'width=600,height=400');
}
</script>

<?php include 'footer.php'; ?>