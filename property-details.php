<?php
/**
 * Property Details Page
 * Display detailed information about a specific property;
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

// Get property details
$db->query("SELECT p.*, pt.name as property_type, 
           a.id as agent_id, CONCAT(u.name) as agent_name, 
           u.email as agent_email, u.phone as agent_phone,
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

<!-- Property Gallery Section -->
<section class="property-details-section">
    <div class="container">
        <div class="property-gallery-container">
            <?php if (!empty($property_images)): ?>
            <div class="property-gallery">
                <?php foreach ($property_images as $index => $image): ?>
                <div class="property-image <?php echo $index === 0 ? 'main-image' : ($index === 1 ? 'secondary-image' : ''); ?>" data-index="<?php echo $index; ?>">
                    <img src="<?php echo $image['image_path']; ?>" alt="Property Image <?php echo $index + 1; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="gallery-thumbs">
                <?php foreach ($property_images as $index => $image): ?>
                <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                    <img src="<?php echo $image['image_path']; ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="property-gallery">
                <div class="property-image main-image">
                    <img src="assets/images/property-placeholder.jpg" alt="Property Image">
                </div>
            </div>
            <?php endif; ?>
        </div>

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
                
                <div class="property-location">
                    <h3>Location</h3>
                    <div class="location-map">
                        <!-- Map would go here, using a placeholder for now -->
                        <div class="map-placeholder">
                            <p>Location Map</p>
                            <p><?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="property-sidebar">
                <?php if (!empty($property['agent_id'])): ?>
                <div class="agent-card">
                    <h3>Property Agent</h3>
                    <div class="agent-info">
                        <div class="agent-image">
                            <img src="assets/images/agent-placeholder.jpg" alt="Agent">
                        </div>
                        <div class="agent-details">
                            <h4><?php echo htmlspecialchars($property['agent_name']); ?></h4>
                            <p class="agent-listings"><?php echo $property['agent_listings_count']; ?> Properties</p>
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
                </div>
                
                <div class="property-share">
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
                <?php if ($similar['featured']): ?>
                <div class="property-featured"></div>
                <?php endif; ?>
                
                <div class="property-status status-<?php echo $similar['status']; ?>">
                    <?php echo ucfirst($similar['status']); ?>
                </div>
                
                <div class="property-images">
                    <img src="<?php echo !empty($similar['primary_image']) ? $similar['primary_image'] : 'assets/images/property-placeholder.jpg'; ?>" 
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

<!-- Custom CSS for Property Details Page -->
<style>
/* Property Details Page Styles */
.property-details-section {
    padding: 60px 0;
}

.property-gallery-container {
    margin-bottom: 40px;
}

.property-gallery {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 320px 150px;
    gap: 15px;
    margin-bottom: 15px;
}

.property-image {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    cursor: pointer;
}

.property-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-image:hover img {
    transform: scale(1.05);
}

.main-image {
    grid-column: 1 / 2;
    grid-row: 1 / 3;
}

.gallery-thumbs {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.gallery-thumb {
    width: 80px;
    height: 60px;
    border-radius: 5px;
    overflow: hidden;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-thumb.active {
    opacity: 1;
    border: 2px solid var(--primary-color);
}

.property-details-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.property-title-price h2 {
    margin-bottom: 5px;
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

.agent-info {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.agent-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 15px;
}

.agent-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agent-details h4 {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.agent-listings {
    color: var(--primary-color);
    font-weight: 500;
    margin-bottom: 10px;
}

.agent-contact p {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.agent-contact p i {
    color: var(--primary-color);
    margin-right: 10px;
    width: 15px;
}

.view-profile-btn {
    margin-top: 10px;
    width: 100%;
}

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

.similar-properties {
    padding: 60px 0;
    background-color: var(--gray-light);
}

/* Responsive Styles */
@media (max-width: 992px) {
    .property-gallery {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 280px 130px;
    }
    
    .property-details-container {
        grid-template-columns: 1fr;
    }
    
    .property-sidebar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .agent-card,
    .contact-form-card,
    .property-share {
        margin-bottom: 0;
    }
}

@media (max-width: 768px) {
    .property-gallery {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .property-image {
        height: 250px;
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
}

@media (max-width: 576px) {
    .property-image {
        height: 200px;
    }
    
    .property-highlights {
        grid-template-columns: repeat(2, 1fr);
        padding: 15px;
        gap: 15px;
    }
    
    .gallery-thumb {
        width: 70px;
        height: 50px;
    }
}
</style>

<!-- JavaScript for Property Gallery -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery image switching
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const galleryImages = document.querySelectorAll('.property-image');
    
    galleryThumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            
            // Remove active class from all thumbs
            galleryThumbs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumb
            this.classList.add('active');
            
            // Handle main display logic
            if (index === '0') {
                // First image is main image
                galleryImages[0].className = 'property-image main-image';
                
                // Reset other images
                for (let i = 1; i < galleryImages.length; i++) {
                    if (i === 1) {
                        galleryImages[i].className = 'property-image secondary-image';
                    } else {
                        galleryImages[i].className = 'property-image';
                    }
                }
            } else {
                // Make clicked image the main image
                galleryImages.forEach(img => {
                    const imgIndex = img.getAttribute('data-index');
                    
                    if (imgIndex === index) {
                        img.className = 'property-image main-image';
                    } else if (imgIndex === '0') {
                        img.className = 'property-image secondary-image';
                    } else {
                        img.className = 'property-image';
                    }
                });
            }
        });
    });
    
    
    
    // Share button functionality
    const shareBtn = document.querySelector('.share-btn');
    if (shareBtn && navigator.share) {
        shareBtn.addEventListener('click', function() {
            navigator.share({
                title: '<?php echo htmlspecialchars($property['title']); ?>',
                text: 'Check out this property: <?php echo htmlspecialchars($property['title']); ?>',
                url: window.location.href
            })
            .then(() => console.log('Share successful'))
            .catch((error) => console.log('Share failed', error));
        });
    }
    
    // Contact form submission
    const contactForm = document.getElementById('propertyInquiryForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Here you would normally send data to server
            // For demo, just show alert
            alert('Your enquiry has been sent. We will contact you soon!');
            
            // Reset form
            contactForm.reset();
        });
    }
});
</script>

<?php include 'footer.php'; ?>
