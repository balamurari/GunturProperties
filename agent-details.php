<?php
/**
 * Agent Details Page
 * Display detailed information about a specific agent
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Agent Profile';

// Get database connection
$db = new Database();

// Check if agent ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: agents.php');
    exit;
}

$agent_id = (int)$_GET['id'];

// Get agent details
$db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic as profile_image
           FROM agents a
           JOIN users u ON a.user_id = u.id
           WHERE a.id = :agent_id AND u.status = 1");
$db->bind(':agent_id', $agent_id);
$agent = $db->single();

// If agent not found, redirect to agents page
if (!$agent) {
    header('Location: agents.php');
    exit;
}

// Get agent specializations
$db->query("SELECT s.name 
           FROM agent_specialization_mapping m
           JOIN agent_specializations s ON m.specialization_id = s.id
           WHERE m.agent_id = :agent_id");
$db->bind(':agent_id', $agent_id);
$specializations = $db->resultSet();

// Get agent properties
$db->query("SELECT p.*, pt.name as property_type, 
           (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
           FROM properties p
           LEFT JOIN property_types pt ON p.type_id = pt.id
           WHERE p.agent_id = :agent_id
           ORDER BY p.featured DESC, p.created_at DESC
           LIMIT 6");
$db->bind(':agent_id', $agent_id);
$properties = $db->resultSet();

// Get agent reviews
$db->query("SELECT * FROM agent_reviews 
           WHERE agent_id = :agent_id AND status = 'approved'
           ORDER BY created_at DESC");
$db->bind(':agent_id', $agent_id);
$reviews = $db->resultSet();

// Calculate average rating
$total_rating = 0;
$review_count = count($reviews);
if ($review_count > 0) {
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
    }
    $average_rating = $total_rating / $review_count;
} else {
    $average_rating = 0;
}

// Get agent certifications
$db->query("SELECT * FROM agent_certifications WHERE agent_id = :agent_id ORDER BY issue_date DESC");
$db->bind(':agent_id', $agent_id);
$certifications = $db->resultSet();

// Get agent awards
$db->query("SELECT * FROM agent_awards WHERE agent_id = :agent_id ORDER BY year DESC");
$db->bind(':agent_id', $agent_id);
$awards = $db->resultSet();

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
        <h1><?php echo htmlspecialchars($agent['name']); ?></h1>
        <p><?php echo htmlspecialchars($agent['position']); ?></p>
    </div>
</section>

<!-- Agent Profile Section -->
<section class="agent-profile-section">
    <div class="container">
        <div class="agent-profile-container">
            <div class="agent-profile-main">
                <div class="agent-profile-header">
                    <div class="agent-profile-image">
                        <img src="<?php echo !empty($agent['profile_image']) ? $agent['profile_image'] : 'assets/images/agent-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($agent['name']); ?>">
                    </div>
                    <div class="agent-profile-info">
                        <h2><?php echo htmlspecialchars($agent['name']); ?></h2>
                        <p class="agent-position"><?php echo htmlspecialchars($agent['position']); ?></p>
                        
                        <?php if ($average_rating > 0): ?>
                        <div class="agent-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($average_rating)): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $average_rating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span><?php echo number_format($average_rating, 1); ?> (<?php echo $review_count; ?> reviews)</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="agent-stats">
                            <div class="agent-stat">
                                <div class="stat-number"><?php echo $agent['experience']; ?>+</div>
                                <div class="stat-label">Years Experience</div>
                            </div>
                            <div class="agent-stat">
                                <div class="stat-number"><?php echo $agent['properties_sold']; ?>+</div>
                                <div class="stat-label">Properties Sold</div>
                            </div>
                            <div class="agent-stat">
                                <div class="stat-number"><?php echo count($properties); ?></div>
                                <div class="stat-label">Active Listings</div>
                            </div>
                        </div>
                        
                        <?php if (!empty($specializations)): ?>
                        <div class="agent-specializations">
                            <?php foreach ($specializations as $spec): ?>
                            <span class="specialization-tag"><?php echo htmlspecialchars($spec['name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="agent-social">
                            <?php if (!empty($agent['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['facebook_url']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['twitter_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['twitter_url']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['instagram_url']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['linkedin_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['linkedin_url']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['website_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['website_url']); ?>" target="_blank"><i class="fas fa-globe"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="agent-about">
                    <h3>About <?php echo htmlspecialchars($agent['name']); ?></h3>
                    <div class="agent-description">
                        <?php echo nl2br(htmlspecialchars($agent['description'])); ?>
                    </div>
                </div>
                
                <?php if (!empty($certifications) || !empty($awards)): ?>
                <div class="agent-credentials">
                    <?php if (!empty($certifications)): ?>
                    <div class="agent-certifications">
                        <h3>Certifications</h3>
                        <ul class="credentials-list">
                            <?php foreach ($certifications as $cert): ?>
                            <li>
                                <div class="credential-icon"><i class="fas fa-certificate"></i></div>
                                <div class="credential-details">
                                    <h4><?php echo htmlspecialchars($cert['name']); ?></h4>
                                    <p>
                                        <?php echo htmlspecialchars($cert['issuing_organization']); ?>
                                        <?php if (!empty($cert['issue_date'])): ?>
                                        • <?php echo date('Y', strtotime($cert['issue_date'])); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($cert['description'])): ?>
                                    <p class="credential-description"><?php echo htmlspecialchars($cert['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($awards)): ?>
                    <div class="agent-awards">
                        <h3>Awards & Recognition</h3>
                        <ul class="credentials-list">
                            <?php foreach ($awards as $award): ?>
                            <li>
                                <div class="credential-icon"><i class="fas fa-award"></i></div>
                                <div class="credential-details">
                                    <h4><?php echo htmlspecialchars($award['name']); ?></h4>
                                    <p>
                                        <?php if (!empty($award['issuing_organization'])): ?>
                                        <?php echo htmlspecialchars($award['issuing_organization']); ?> • 
                                        <?php endif; ?>
                                        <?php echo $award['year']; ?>
                                    </p>
                                    <?php if (!empty($award['description'])): ?>
                                    <p class="credential-description"><?php echo htmlspecialchars($award['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($reviews)): ?>
                <div class="agent-reviews-section">
                    <h3>Client Reviews</h3>
                    <div class="reviews-container">
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                        <p><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php if (!empty($review['title'])): ?>
                            <h5 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h5>
                            <?php endif; ?>
                            <div class="review-content">
                                <?php echo nl2br(htmlspecialchars($review['review'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- <div class="agent-profile-sidebar">
                <div class="contact-agent-card">
                    <h3>Contact <?php echo htmlspecialchars($agent['name']); ?></h3>
                    <div class="agent-contact-info">
                        <?php if (!empty($agent['phone'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <p><?php echo htmlspecialchars($agent['phone']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['email'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <p><?php echo htmlspecialchars($agent['email']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['office_address'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <p><?php echo nl2br(htmlspecialchars($agent['office_address'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['office_hours'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <p><?php echo htmlspecialchars($agent['office_hours']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <form class="contact-form" id="agentContactForm" method="post" action="process-contact.php">
                        <input type="hidden" name="agent_id" value="<?php echo $agent_id; ?>">
                        <input type="hidden" name="form_type" value="agent_contact">
                        
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
                            <textarea name="message" rows="4" placeholder="Your Message" required>I'm interested in your properties. Please contact me.</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                
                <div class="write-review-card">
                    <h3>Write a Review</h3>
                    <p>Share your experience working with <?php echo htmlspecialchars($agent['name']); ?></p>
                    <form class="review-form" id="agentReviewForm" method="post" action="process-review.php">
                        <input type="hidden" name="agent_id" value="<?php echo $agent_id; ?>">
                        <input type="hidden" name="form_type" value="agent_review">
                        
                        <div class="form-group">
                            <label>Your Rating</label>
                            <div class="rating-select">
                                <div class="rating-options">
                                    <input type="radio" id="star5" name="rating" value="5" required>
                                    <label for="star5" title="5 stars"><i class="far fa-star"></i></label>
                                    
                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4" title="4 stars"><i class="far fa-star"></i></label>
                                    
                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3" title="3 stars"><i class="far fa-star"></i></label>
                                    
                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2" title="2 stars"><i class="far fa-star"></i></label>
                                    
                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1" title="1 star"><i class="far fa-star"></i></label>
                                </div>
                                <span class="rating-text">Select Rating</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="title" placeholder="Review Title" required>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="review" rows="4" placeholder="Your Review" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div> -->
        </div>
    </div>
</section>

<!-- Agent Properties Section -->
<?php if (!empty($properties)): ?>
<section class="agent-properties-section">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <div class="dot"></div>
                <span>Properties</span>
            </div>
            <h2><?php echo htmlspecialchars($agent['name']); ?>'s Listings</h2>
        </div>
        
        <div class="grid-container">
            <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <?php if ($property['featured']): ?>
                <div class="property-featured"></div>
                <?php endif; ?>
                
                <div class="property-status status-<?php echo $property['status']; ?>">
                    <?php echo ucfirst($property['status']); ?>
                </div>
                
                <div class="property-images">
                    <img src="<?php echo !empty($property['primary_image']) ? $property['primary_image'] : 'assets/images/property-placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>">
                </div>
                
                <div class="property-info">
                    <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                    <div class="property-price"><?php echo formatIndianPrice($property['price']); ?></div>
                    <div class="property-details-row">
                        <?php if (!empty($property['bedrooms'])): ?>
                        <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['bathrooms'])): ?>
                        <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['area'])): ?>
                        <span><i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> <?php echo $property['area_unit']; ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="property-location">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['city']); ?>, <?php echo htmlspecialchars($property['state']); ?>
                    </p>
                    <a href="property-details.php?id=<?php echo $property['id']; ?>" class="view-details-btn">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($properties) > 6): ?>
        <div class="view-all">
            <a href="properties.php?agent_id=<?php echo $agent_id; ?>" class="btn btn-outline">View All Properties</a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Custom Styles for Agent Details Page -->
<style>
/* Agent Profile Styles */
.agent-profile-section {
    padding: 60px 0;
}

.agent-profile-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.agent-profile-header {
    display: flex;
    margin-bottom: 40px;
}

.agent-profile-image {
    width: 180px;
    height: 180px;
    border-radius: 10px;
    overflow: hidden;
    margin-right: 30px;
    flex-shrink: 0;
}

.agent-profile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agent-profile-info h2 {
    margin-bottom: 5px;
    font-size: 1.8rem;
}

.agent-position {
    color: var(--primary-color);
    font-weight: 500;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.agent-rating {
    color: #ffc107;
    margin-bottom: 20px;
}

.agent-rating span {
    color: var(--text-light);
    margin-left: 5px;
}

.agent-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
}

.agent-stat {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-light);
}

.agent-specializations {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.specialization-tag {
    background-color: var(--secondary-color);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: var(--primary-color);
}

.agent-social {
    display: flex;
    gap: 12px;
}

.agent-social a {
    width: 40px;
    height: 40px;
    background-color: var(--gray-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dark);
    transition: all 0.3s ease;
}

.agent-social a:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.agent-about,
.agent-credentials,
.agent-reviews-section {
    margin-bottom: 40px;
}

.agent-about h3,
.agent-certifications h3,
.agent-awards h3,
.agent-reviews-section h3 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.agent-about h3::after,
.agent-certifications h3::after,
.agent-awards h3::after,
.agent-reviews-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 2px;
    background-color: var(--primary-color);
}

.agent-description {
    line-height: 1.8;
    color: var(--text-light);
}

.credentials-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.credentials-list li {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.credential-icon {
    width: 40px;
    height: 40px;
    background-color: var(--secondary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 18px;
    flex-shrink: 0;
}

.credential-details h4 {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.credential-details p {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: 5px;
}

.credential-description {
    font-style: italic;
}

.reviews-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.review-card {
    background-color: var(--white);
    border-radius: 10px;
    padding: 20px;
    box-shadow: var(--box-shadow);
}

.review-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.reviewer-avatar {
    width: 40px;
    height: 40px;
    background-color: var(--gray-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
}

.reviewer-details h4 {
    font-size: 1rem;
    margin-bottom: 0;
}

.reviewer-details p {
    font-size: 0.8rem;
    color: var(--text-light);
    margin-bottom: 0;
}

.review-rating {
    color: #ffc107;
}

.review-title {
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.review-content {
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--text-light);
}

.contact-agent-card,
.write-review-card {
    background-color: var(--white);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: var(--box-shadow);
}

.contact-agent-card h3,
.write-review-card h3 {
    font-size: 1.2rem;
    margin-bottom: 15px;
    text-align: center;
}

.write-review-card p {
    text-align: center;
    margin-bottom: 20px;
}

.agent-contact-info {
    margin-bottom: 20px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 15px;
}

.contact-item i {
    color: var(--primary-color);
    margin-top: 3px;
    width: 15px;
    text-align: center;
}

.contact-item p {
    margin-bottom: 0;
    font-size: 0.9rem;
}

.contact-form .form-group,
.review-form .form-group {
    margin-bottom: 15px;
}

.contact-form input,
.contact-form textarea,
.review-form input,
.review-form textarea {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid var(--gray);
    border-radius: 5px;
    font-family: 'Urbanist', sans-serif;
    font-size: 14px;
}

.contact-form textarea,
.review-form textarea {
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
}

.contact-form button,
.review-form button {
    width: 100%;
}

.rating-select {
    margin-bottom: 15px;
}

.rating-options {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    margin-bottom: 5px;
}

.rating-options input {
    display: none;
}

.rating-options label {
    cursor: pointer;
    font-size: 24px;
    color: #ddd;
    margin: 0 2px;
}

.rating-options label:hover,
.rating-options label:hover ~ label,
.rating-options input:checked ~ label {
    color: #ffc107;
}

.rating-text {
    display: block;
    text-align: center;
    font-size: 0.9rem;
    color: var(--text-light);
}

.agent-properties-section {
    padding: 60px 0;
    background-color: var(--gray-light);
}

.view-all {
    text-align: center;
    margin-top: 30px;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .agent-profile-container {
        grid-template-columns: 1fr;
    }
    
    .reviews-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .agent-profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .agent-profile-image {
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .agent-stats {
        justify-content: center;
    }
    
    .agent-specializations {
        justify-content: center;
    }
    
    .agent-social {
        justify-content: center;
    }
    
    .agent-about h3::after,
    .agent-certifications h3::after,
    .agent-awards h3::after,
    .agent-reviews-section h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .agent-about h3,
    .agent-certifications h3,
    .agent-awards h3,
    .agent-reviews-section h3 {
        text-align: center;
    }
    
    .credentials-list li {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .credential-icon {
        margin-bottom: 10px;
    }
}

@media (max-width: 576px) {
    .agent-profile-image {
        width: 150px;
        height: 150px;
    }
    
    .agent-stats {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<!-- JavaScript for Agent Details Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating system for review form
    const ratingInputs = document.querySelectorAll('.rating-options input');
    const ratingText = document.querySelector('.rating-text');
    const ratingLabels = document.querySelectorAll('.rating-options label');
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            ratingText.textContent = this.value + ' Star' + (this.value > 1 ? 's' : '');
            
            // Update icon classes on all labels
            ratingLabels.forEach(label => {
                const labelFor = label.getAttribute('for');
                const labelValue = document.getElementById(labelFor).value;
                
                // Reset all icons first
                label.querySelector('i').className = 'far fa-star';
                
                // If this label's value is less than or equal to the selected value, fill the star
                if (labelValue <= this.value) {
                    label.querySelector('i').className = 'fas fa-star';
                }
            });
        });
    });
    
    // Form submissions
    const contactForm = document.getElementById('agentContactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            // Let form submit normally since it has an action
            // But you could add additional validation here
        });
    }
    
    const reviewForm = document.getElementById('agentReviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            // Let form submit normally since it has an action
            // But you could add additional validation here
        });
    }
});
</script>

<?php include 'footer.php'; ?>