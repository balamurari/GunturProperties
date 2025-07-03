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
           WHERE p.agent_id = :agent_id AND p.status != 'sold'
           ORDER BY p.featured DESC, p.created_at DESC
           LIMIT 6");
$db->bind(':agent_id', $agent_id);
$properties = $db->resultSet();

// Get agent reviews
$db->query("SELECT * FROM agent_reviews 
           WHERE agent_id = :agent_id AND status = 'approved'
           ORDER BY created_at DESC
           LIMIT 10");
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
$db->query("SELECT * FROM agent_certifications WHERE agent_id = :agent_id ORDER BY issue_date DESC LIMIT 5");
$db->bind(':agent_id', $agent_id);
$certifications = $db->resultSet();

// Get agent awards
$db->query("SELECT * FROM agent_awards WHERE agent_id = :agent_id ORDER BY year DESC LIMIT 5");
$db->bind(':agent_id', $agent_id);
$awards = $db->resultSet();

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
        <h1><?php echo htmlspecialchars($agent['name'] ?? 'Agent Profile'); ?></h1>
        <?php if (!empty($agent['position'])): ?>
        <p><?php echo htmlspecialchars($agent['position']); ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Agent Profile Section -->
<section class="agent-profile-section">
    <div class="container">
        <div class="agent-profile-container">
            <div class="agent-profile-main">
                <div class="agent-profile-header">
                    <div class="agent-profile-image">
                        <img src="<?php echo htmlspecialchars(getAgentImageUrl($agent['profile_image'])); ?>" 
                             alt="<?php echo htmlspecialchars($agent['name'] ?? 'Agent'); ?> - Real Estate Professional"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='assets/images/agents/agent-placeholder.jpg';">
                    </div>
                    
                    <div class="agent-profile-info">
                        <h2><?php echo htmlspecialchars($agent['name'] ?? 'Agent Name'); ?></h2>
                        
                        <?php if (!empty($agent['position'])): ?>
                        <p class="agent-position"><?php echo htmlspecialchars($agent['position']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($average_rating > 0): ?>
                        <div class="agent-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($average_rating)): ?>
                                    <i class="fas fa-star" aria-hidden="true"></i>
                                <?php elseif ($i - 0.5 <= $average_rating): ?>
                                    <i class="fas fa-star-half-alt" aria-hidden="true"></i>
                                <?php else: ?>
                                    <i class="far fa-star" aria-hidden="true"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span><?php echo number_format($average_rating, 1); ?> (<?php echo $review_count; ?> reviews)</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="agent-stats">
                            <div class="agent-stat">
                                <span class="stat-number"><?php echo (int)($agent['experience'] ?? 0); ?>+</span>
                                <span class="stat-label">Years Experience</span>
                            </div>
                            <div class="agent-stat">
                                <span class="stat-number"><?php echo (int)($agent['properties_sold'] ?? 0); ?>+</span>
                                <span class="stat-label">Properties Sold</span>
                            </div>
                            <div class="agent-stat">
                                <span class="stat-number"><?php echo count($properties); ?></span>
                                <span class="stat-label">Active Listings</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($specializations)): ?>
                        <div class="agent-specializations">
                            <?php foreach ($specializations as $spec): ?>
                            <span class="specialization-tag"><?php echo htmlspecialchars($spec['name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        $has_social = !empty($agent['facebook_url']) || !empty($agent['twitter_url']) || 
                                     !empty($agent['instagram_url']) || !empty($agent['linkedin_url']) || 
                                     !empty($agent['website_url']);
                        ?>
                        
                        <?php if ($has_social): ?>
                        <div class="agent-social">
                            <?php if (!empty($agent['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['facebook_url']); ?>" 
                               target="_blank" rel="noopener noreferrer"
                               aria-label="Visit <?php echo htmlspecialchars($agent['name']); ?>'s Facebook profile">
                               <i class="fab fa-facebook-f" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['twitter_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['twitter_url']); ?>" 
                               target="_blank" rel="noopener noreferrer"
                               aria-label="Visit <?php echo htmlspecialchars($agent['name']); ?>'s Twitter profile">
                               <i class="fab fa-twitter" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['instagram_url']); ?>" 
                               target="_blank" rel="noopener noreferrer"
                               aria-label="Visit <?php echo htmlspecialchars($agent['name']); ?>'s Instagram profile">
                               <i class="fab fa-instagram" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['linkedin_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['linkedin_url']); ?>" 
                               target="_blank" rel="noopener noreferrer"
                               aria-label="Visit <?php echo htmlspecialchars($agent['name']); ?>'s LinkedIn profile">
                               <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['website_url'])): ?>
                            <a href="<?php echo htmlspecialchars($agent['website_url']); ?>" 
                               target="_blank" rel="noopener noreferrer"
                               aria-label="Visit <?php echo htmlspecialchars($agent['name']); ?>'s website">
                               <i class="fas fa-globe" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="agent-about">
                    <h3>About <?php echo htmlspecialchars($agent['name'] ?? 'This Agent'); ?></h3>
                    <div class="agent-description">
                        <?php 
                        // Add fallback description text if the agent description is empty
                        $description = !empty($agent['description']) 
                            ? $agent['description'] 
                            : 'Experienced real estate professional dedicated to helping clients find their perfect property. With years of expertise in the local market, this agent provides personalized service and expert guidance throughout the buying or selling process.';
                        
                        echo nl2br(htmlspecialchars($description)); 
                        ?>
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
                                <div class="credential-icon">
                                    <i class="fas fa-certificate" aria-hidden="true"></i>
                                </div>
                                <div class="credential-details">
                                    <h4><?php echo htmlspecialchars($cert['name']); ?></h4>
                                    <p>
                                        <?php echo htmlspecialchars($cert['issuing_organization'] ?? 'Professional Organization'); ?>
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
                                <div class="credential-icon">
                                    <i class="fas fa-award" aria-hidden="true"></i>
                                </div>
                                <div class="credential-details">
                                    <h4><?php echo htmlspecialchars($award['name']); ?></h4>
                                    <p>
                                        <?php if (!empty($award['issuing_organization'])): ?>
                                        <?php echo htmlspecialchars($award['issuing_organization']); ?> • 
                                        <?php endif; ?>
                                        <?php echo (int)$award['year']; ?>
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
                        <article class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <i class="fas fa-user" aria-hidden="true"></i>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                        <p><time datetime="<?php echo date('Y-m-d', strtotime($review['created_at'])); ?>">
                                           <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        </time></p>
                                    </div>
                                </div>
                                <div class="review-rating" aria-label="Rating: <?php echo (int)$review['rating']; ?> out of 5 stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star" aria-hidden="true"></i>
                                        <?php else: ?>
                                            <i class="far fa-star" aria-hidden="true"></i>
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
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="agent-profile-sidebar">
                <div class="contact-agent-card">
                    <h3>Contact <?php echo htmlspecialchars($agent['name'] ?? 'Agent'); ?></h3>
                    <div class="agent-contact-info">
                        <?php if (!empty($agent['phone'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <p><a href="tel:<?php echo htmlspecialchars($agent['phone']); ?>"><?php echo htmlspecialchars($agent['phone']); ?></a></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['email'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <p><a href="mailto:<?php echo htmlspecialchars($agent['email']); ?>"><?php echo htmlspecialchars($agent['email']); ?></a></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['office_address'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <p><?php echo nl2br(htmlspecialchars($agent['office_address'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['office_hours'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <p><?php echo htmlspecialchars($agent['office_hours']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Agent Properties Section -->
<?php if (!empty($properties)): ?>
<section class="agent-properties-section">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <span class="dot"></span>
                <span>Properties</span>
            </div>
            <h2><?php echo htmlspecialchars($agent['name'] ?? 'Agent'); ?>'s Listings</h2>
        </div>
        
        <div class="grid-container">
            <?php foreach ($properties as $property): ?>
            <article class="property-card">
                <div class="property-images">
                    <img src="<?php echo htmlspecialchars(getPropertyImageUrl($property['primary_image'])); ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='assets/images/properties/property-placeholder.jpg';">
                </div>
                
                <?php if (!empty($property['status'])): ?>
                    <?php $status_info = getStatusInfo($property['status']); ?>
                    <div class="property-status status-<?php echo $status_info['class']; ?>">
                        <?php echo $status_info['text']; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property['featured']) && $property['featured'] == 1): ?>
                <div class="property-featured" aria-label="Featured property"></div>
                <?php endif; ?>
                
                <div class="property-info">
                    <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                    <div class="property-price"><?php echo formatIndianPrice($property['price']); ?></div>
                    
                    <div class="property-details-row">
                        <?php if (!empty($property['bedrooms'])): ?>
                        <span><i class="fas fa-bed" aria-hidden="true"></i> <?php echo (int)$property['bedrooms']; ?> Beds</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['bathrooms'])): ?>
                        <span><i class="fas fa-bath" aria-hidden="true"></i> <?php echo (int)$property['bathrooms']; ?> Baths</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['area'])): ?>
                        <span><i class="fas fa-ruler-combined" aria-hidden="true"></i> <?php echo htmlspecialchars($property['area']); ?> <?php echo htmlspecialchars($property['area_unit'] ?? 'sq ft'); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($property['city']) && !empty($property['state'])): ?>
                    <p class="property-location">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i> 
                        <?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <a href="property-details.php?id=<?php echo (int)$property['id']; ?>" 
                       class="view-details-btn"
                       aria-label="View details for <?php echo htmlspecialchars($property['title']); ?>">
                       <span>View Details</span>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($properties) >= 6): ?>
        <div class="view-all">
            <a href="properties.php?agent_id=<?php echo $agent_id; ?>" 
               class="btn btn-outline"
               aria-label="View all properties by <?php echo htmlspecialchars($agent['name']); ?>">
               <span>View All Properties</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<style>
/* =================================
   AGENT DETAILS PAGE - COMPLETE RESPONSIVE CSS
   ================================= */

/* Base Reset & Variables */
:root {
    --primary-color: #007bff;
    --secondary-color:rgb(248, 250, 248);
    --text-dark: #333;
    --text-light:  #28a745;
    --gray: #dee2e6;
    --gray-light: #f8f9fa;
    --white: #ffffff;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

* {
    box-sizing: border-box;
}

body {
    overflow-x: hidden;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--text-dark);
    background-color: #f8f9fa;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    width: 100%;
}

@media (min-width: 1400px) {
    .container {
        max-width: 1400px;
    }
}

/* =================================
   PAGE HEADER
   ================================= */

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    pointer-events: none;
}

.page-header .container {
    position: relative;
    z-index: 2;
}

.page-header h1 {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
    font-weight: 700;
    line-height: 1.2;
    word-wrap: break-word;
}

.page-header p {
    font-size: 1.1rem;
    margin: 0;
    opacity: 0.9;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .page-header {
        padding: 2rem 0;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .page-header p {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.75rem;
    }
    
    .page-header p {
        font-size: 0.9rem;
    }
}

/* =================================
   AGENT PROFILE SECTION
   ================================= */

.agent-profile-section {
    padding: 3rem 0;
    background: var(--gray-light);
}

@media (min-width: 768px) {
    .agent-profile-section {
        padding: 4rem 0;
    }
}

.agent-profile-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .agent-profile-container {
        grid-template-columns: 2fr 1fr;
        gap: 3rem;
    }
}

/* =================================
   AGENT PROFILE MAIN
   ================================= */

.agent-profile-main {
    background: var(--white);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    height: fit-content;
}

@media (min-width: 768px) {
    .agent-profile-main {
        padding: 2rem;
    }
}

/* =================================
   AGENT PROFILE HEADER
   ================================= */

.agent-profile-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 2rem;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .agent-profile-header {
        flex-direction: row;
        align-items: flex-start;
        text-align: left;
        gap: 2rem;
    }
}

/* =================================
   AGENT PROFILE IMAGE
   ================================= */

.agent-profile-image {
    width: 180px;
    height: 180px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    position: relative;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

@media (max-width: 480px) {
    .agent-profile-image {
        width: 150px;
        height: 150px;
    }
}

.agent-profile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    transition: transform 0.3s ease;
    display: block;
}

.agent-profile-image:hover img {
    transform: scale(1.05);
}

/* Loading state for profile image */
.agent-profile-image.loading {
    display: flex;
    justify-content: center;
    align-items: center;
}

.agent-profile-image.loading::after {
    content: '';
    width: 40px;
    height: 40px;
    border: 4px solid #ddd;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* =================================
   AGENT PROFILE INFO
   ================================= */

.agent-profile-info {
    flex: 1;
    min-width: 0;
}

.agent-profile-info h2 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    word-wrap: break-word;
}

@media (max-width: 768px) {
    .agent-profile-info h2 {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .agent-profile-info h2 {
        font-size: 1.5rem;
    }
}

.agent-position {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 480px) {
    .agent-position {
        font-size: 1rem;
    }
}

/* =================================
   AGENT RATING
   ================================= */

.agent-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    color: var(--warning);
    font-size: 1.1rem;
}

@media (min-width: 768px) {
    .agent-rating {
        justify-content: flex-start;
    }
}

.agent-rating span {
    color: var(--text-light);
    font-size: 0.9rem;
    font-weight: 500;
}

/* =================================
   AGENT STATS
   ================================= */

.agent-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

@media (max-width: 480px) {
    .agent-stats {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.agent-stat {
    padding: 1rem;
    background: var(--secondary-color);
    border-radius: 8px;
    transition: var(--transition);
}

.agent-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
    display: block;
}

@media (max-width: 480px) {
    .stat-number {
        font-size: 1.5rem;
    }
}

.stat-label {
    font-size: 0.85rem;
    color: var(--text-dark);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* =================================
   AGENT SPECIALIZATIONS
   ================================= */

.agent-specializations {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .agent-specializations {
        justify-content: flex-start;
    }
}

.specialization-tag {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    transition: var(--transition);
}

.specialization-tag:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}

@media (max-width: 480px) {
    .specialization-tag {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }
}

/* =================================
   AGENT SOCIAL LINKS
   ================================= */

.agent-social {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

@media (min-width: 768px) {
    .agent-social {
        justify-content: flex-start;
    }
}

.agent-social a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--gray-light);
    color: var(--text-light);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    text-decoration: none;
}

.agent-social a::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    transform: scale(0);
    transition: transform 0.3s ease;
    border-radius: 50%;
}

.agent-social a:hover::before {
    transform: scale(1);
}

.agent-social a i {
    position: relative;
    z-index: 2;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.agent-social a:hover {
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

/* =================================
   CONTENT SECTIONS
   ================================= */

.agent-about,
.agent-credentials,
.agent-reviews-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--gray);
}

.agent-about:last-child,
.agent-credentials:last-child,
.agent-reviews-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.agent-about h3,
.agent-certifications h3,
.agent-awards h3,
.agent-reviews-section h3 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 0.75rem;
    color: var(--text-dark);
    font-weight: 600;
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
    height: 3px;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    border-radius: 2px;
}

@media (max-width: 768px) {
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
}

.agent-description {
    line-height: 1.8;
    color: var(--text-dark);
    font-size: 1rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

@media (max-width: 480px) {
    .agent-description {
        font-size: 0.9rem;
    }
}

/* =================================
   CREDENTIALS SECTION
   ================================= */

.agent-credentials {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) {
    .agent-credentials {
        grid-template-columns: repeat(2, 1fr);
    }
}

.credentials-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.credentials-list li {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--white);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border-left: 4px solid var(--primary-color);
    transition: var(--transition);
}

.credentials-list li:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .credentials-list li {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

.credential-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.credential-details {
    flex: 1;
    min-width: 0;
}

.credential-details h4 {
    font-size: 1.1rem;
    margin: 0 0 0.5rem 0;
    color: var(--text-dark);
    font-weight: 600;
    word-wrap: break-word;
}

.credential-details p {
    font-size: 0.9rem;
    color: var(--text-light);
    margin: 0 0 0.5rem 0;
    line-height: 1.5;
}

.credential-description {
    font-style: italic;
    color: var(--text-light);
    font-size: 0.85rem;
}

/* =================================
   REVIEWS SECTION
   ================================= */

.reviews-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    max-height: 600px;
    overflow-y: auto;
    padding-right: 0.5rem;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) var(--gray-light);
}

@media (min-width: 768px) {
    .reviews-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

.reviews-container::-webkit-scrollbar {
    width: 6px;
}

.reviews-container::-webkit-scrollbar-track {
    background: var(--gray-light);
    border-radius: 3px;
}

.reviews-container::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.reviews-container::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

.review-card {
    background: var(--white);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: var(--transition);
    border-left: 4px solid var(--primary-color);
}

.review-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 1rem;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.reviewer-avatar {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.reviewer-details {
    flex: 1;
    min-width: 0;
}

.reviewer-details h4 {
    font-size: 1rem;
    margin: 0 0 0.25rem 0;
    color: var(--text-dark);
    font-weight: 600;
    word-wrap: break-word;
}

.reviewer-details p {
    font-size: 0.8rem;
    color: var(--text-light);
    margin: 0;
}

.review-rating {
    color: var(--warning);
    font-size: 1rem;
    flex-shrink: 0;
}

.review-title {
    font-size: 1.1rem;
    margin: 0 0 0.75rem 0;
    color: var(--text-dark);
    font-weight: 600;
    word-wrap: break-word;
}

.review-content {
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--text-light);
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* =================================
   SIDEBAR STYLES
   ================================= */

.agent-profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.contact-agent-card,
.write-review-card {
    background: var(--white);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    border-top: 4px solid var(--primary-color);
}

.contact-agent-card h3,
.write-review-card h3 {
    font-size: 1.3rem;
    margin: 0 0 1.5rem 0;
    text-align: center;
    color: var(--text-dark);
    font-weight: 600;
}

.write-review-card p {
    text-align: center;
    margin-bottom: 1.5rem;
    color: var(--text-light);
    font-size: 0.9rem;
    line-height: 1.5;
}

.agent-contact-info {
    margin-bottom: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: var(--secondary-color);
    border-radius: 8px;
    transition: var(--transition);
}

.contact-item:hover {
    background: #e9ecef;
    transform: translateX(3px);
}

.contact-item i {
    color: var(--primary-color);
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.contact-item p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-dark);
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.4;
}

.contact-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}

/* =================================
   AGENT PROPERTIES SECTION
   ================================= */

.agent-properties-section {
    padding: 3rem 0;
    background: var(--white);
}

@media (min-width: 768px) {
    .agent-properties-section {
        padding: 4rem 0;
    }
}

.section-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary-color);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
}

.section-tag .dot {
    width: 8px;
    height: 8px;
    background: var(--white);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
}

.section-header h2 {
    font-size: 2.2rem;
    color: var(--text-dark);
    margin: 0;
    font-weight: 700;
    word-wrap: break-word;
}

@media (max-width: 768px) {
    .section-header h2 {
        font-size: 1.8rem;
    }
}

@media (max-width: 480px) {
    .section-header h2 {
        font-size: 1.5rem;
    }
}

/* =================================
   PROPERTY GRID
   ================================= */

.grid-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (min-width: 480px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 768px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
}

@media (min-width: 1024px) {
    .grid-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* =================================
   PROPERTY CARD STYLES
   ================================= */

.property-card {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.property-images {
    height: 200px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

@media (max-width: 768px) {
    .property-images {
        height: 180px;
    }
}

@media (max-width: 480px) {
    .property-images {
        height: 160px;
    }
}

.property-images img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
    display: block;
}

.property-card:hover .property-images img {
    transform: scale(1.05);
}

/* Property Status */
.property-status {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 5;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--white);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.status-buy { background: var(--success); }
.status-rent { background: var(--primary-color); }
.status-sold { background: var(--danger); }
.status-pending { background: var(--warning); color: var(--text-dark); }

/* Featured Property */
.property-featured {
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-left: 60px solid transparent;
    border-top: 60px solid var(--danger);
    z-index: 3;
}

.property-featured::after {
    content: 'Featured';
    position: absolute;
    top: -55px;
    right: -35px;
    color: var(--white);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    transform: rotate(45deg);
    width: 70px;
    text-align: center;
}

/* Property Info */
.property-info {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

@media (max-width: 480px) {
    .property-info {
        padding: 1.25rem;
    }
}

.property-info h3 {
    margin: 0 0 0.75rem 0;
    font-size: 1.1rem;
    line-height: 1.3;
    color: var(--text-dark);
    font-weight: 600;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 2.6em;
    word-wrap: break-word;
}

.property-price {
    color: var(--success);
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

@media (max-width: 480px) {
    .property-price {
        font-size: 1.2rem;
    }
}

.property-details-row {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: green;
    gap: 0.5rem;
}

.property-details-row span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex: 1;
    min-width: 70px;
}

.property-details-row span i {
    color: var(--primary-color);
    font-size: 0.9rem;
    width: 14px;
    text-align: center;
}

.property-location {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    line-height: 1.4;
}

.property-location i {
    color: var(--primary-color);
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.view-details-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: var(--white);
    text-decoration: none;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: var(--transition);
    margin-top: auto;
    position: relative;
    overflow: hidden;
}

.view-details-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0056b3, #003d82);
    transition: left 0.3s ease;
}

.view-details-btn:hover::before {
    left: 0;
}

.view-details-btn span {
    position: relative;
    z-index: 2;
}

.view-details-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
}

/* No Properties Found */
.no-properties-found {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-light);
    font-size: 1.1rem;
    background: var(--white);
    border-radius: 12px;
    box-shadow: var(--box-shadow);
}

.no-properties-found::before {
    content: '🏠';
    display: block;
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* View All Button */
.view-all {
    text-align: center;
    margin-top: 2rem;
}

.btn.btn-outline {
    background: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    font-weight: 600;
    padding: 0.875rem 2rem;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.btn.btn-outline::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    transition: left 0.3s ease;
}

.btn.btn-outline:hover::before {
    left: 0;
}

.btn.btn-outline span {
    position: relative;
    z-index: 2;
}

.btn.btn-outline:hover {
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
}

/* =================================
   ACCESSIBILITY & TOUCH OPTIMIZATIONS
   ================================= */

/* Focus styles */
.agent-social a:focus,
.view-details-btn:focus,
.btn:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .property-card:hover {
        transform: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .property-card:hover .property-images img {
        transform: none;
    }
    
    .agent-social a:hover {
        transform: none;
        box-shadow: none;
    }
    
    .view-details-btn:hover,
    .btn:hover {
        transform: none;
        box-shadow: none;
    }
    
    .agent-stat:hover {
        transform: none;
        box-shadow: none;
    }
    
    .specialization-tag:hover {
        transform: none;
        box-shadow: none;
    }
    
    .credentials-list li:hover {
        transform: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .review-card:hover {
        transform: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    
    .contact-item:hover {
        transform: none;
        background: var(--secondary-color);
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .property-card:hover {
        transform: none;
    }
    
    .property-images img {
        transition: none;
    }
}
</style>

<!-- JavaScript for Enhanced Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced image loading for agent profile
    const profileImage = document.querySelector('.agent-profile-image img');
    if (profileImage) {
        const container = profileImage.parentElement;
        container.classList.add('loading');
        
        profileImage.addEventListener('load', function() {
            container.classList.remove('loading');
            
            // Optimize image display based on aspect ratio
            const aspectRatio = this.naturalWidth / this.naturalHeight;
            if (aspectRatio < 0.8 || aspectRatio > 1.5) {
                this.style.objectFit = 'contain';
                container.style.backgroundColor = '#f8f8f8';
            }
        });
        
        profileImage.addEventListener('error', function() {
            container.classList.remove('loading');
            this.src = 'assets/images/agents/agent-placeholder.jpg';
        });
        
        if (profileImage.complete) {
            container.classList.remove('loading');
        }
    }
    
    // Enhanced property image loading
    const propertyImages = document.querySelectorAll('.property-images img');
    propertyImages.forEach(img => {
        const container = img.parentElement;
        
        img.addEventListener('load', function() {
            // Check aspect ratio and adjust object-fit accordingly
            const aspectRatio = this.naturalWidth / this.naturalHeight;
            if (aspectRatio < 0.7 || aspectRatio > 2) {
                this.style.objectFit = 'contain';
                container.style.backgroundColor = '#f8f8f8';
            }
        });
        
        img.addEventListener('error', function() {
            this.src = 'assets/images/properties/property-placeholder.jpg';
            this.alt = 'Property image not available';
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Enhanced contact info interactions
    const contactItems = document.querySelectorAll('.contact-item');
    contactItems.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            item.addEventListener('click', function(e) {
                if (e.target !== link) {
                    link.click();
                }
            });
            item.style.cursor = 'pointer';
        }
    });
    
    // Reviews container scroll enhancement
    const reviewsContainer = document.querySelector('.reviews-container');
    if (reviewsContainer) {
        let isScrolling = false;
        
        reviewsContainer.addEventListener('scroll', function() {
            if (!isScrolling) {
                // Add visual feedback for scrolling
                this.style.borderRadius = '10px';
                isScrolling = true;
                
                setTimeout(() => {
                    isScrolling = false;
                }, 150);
            }
        });
    }
    
    // Enhance social media link tracking
    document.querySelectorAll('.agent-social a').forEach(link => {
        link.addEventListener('click', function() {
            // Track social media clicks (you can add analytics here)
            console.log('Social media click:', this.href);
        });
    });
    
    // Property card equal heights (if needed)
    function equalizePropertyCardHeights() {
        const cards = document.querySelectorAll('.property-card');
        if (cards.length <= 1) return;
        
        // Reset heights
        cards.forEach(card => card.style.height = 'auto');
        
        // Group by rows
        const rows = {};
        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const rowTop = Math.round(rect.top);
            
            if (!rows[rowTop]) rows[rowTop] = [];
            rows[rowTop].push(card);
        });
        
        // Set equal heights per row
        Object.values(rows).forEach(row => {
            if (row.length > 1) {
                const maxHeight = Math.max(...row.map(card => card.offsetHeight));
                row.forEach(card => card.style.height = maxHeight + 'px');
            }
        });
    }
    
    // Run on load and resize
    window.addEventListener('load', equalizePropertyCardHeights);
    window.addEventListener('resize', debounce(equalizePropertyCardHeights, 250));
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>

<?php include 'footer.php'; ?>