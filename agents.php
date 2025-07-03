<?php
/**
 * Agents Page
 * Lists all real estate agents with pagination
 */

// Include required files
$config_file = 'includes/config.php';
$database_file = 'includes/database.php';
$functions_file = 'includes/functions.php';

$error_message = '';

// Check if required files exist
if (!file_exists($config_file)) {
    $error_message = "Error: Configuration file not found.";
} elseif (!file_exists($database_file)) {
    $error_message = "Error: Database file not found.";
} elseif (!file_exists($functions_file)) {
    $error_message = "Error: Functions file not found.";
} else {
    require_once $config_file;
    require_once $database_file;
    require_once $functions_file;
}

// Set page title
$page_title = 'Our Agents';

// Initialize variables
$total_agents = 0;
$total_pages = 1;
$agents = [];
$agent_specializations = [];

// Only proceed with database operations if required files exist
if (empty($error_message)) {
    try {
        // Get database connection
        $db = new Database();
        
        // Pagination settings
        $agents_per_page = 6;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $agents_per_page;
        
        // Get total agents count
        $db->query("SELECT COUNT(*) as total FROM agents a JOIN users u ON a.user_id = u.id WHERE u.status = 1");
        $result = $db->single();
        $total_agents = $result['total'];
        $total_pages = ceil($total_agents / $agents_per_page);
        
        // Get agents data with their property counts
        $db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic as profile_image, 
                   (SELECT COUNT(*) FROM properties WHERE agent_id = a.id AND status != 'sold') as property_count
                   FROM agents a
                   JOIN users u ON a.user_id = u.id
                   WHERE u.status = 1
                   ORDER BY a.featured DESC, a.display_order ASC, a.id DESC
                   LIMIT :offset, :limit");
        $db->bind(':offset', $offset);
        $db->bind(':limit', $agents_per_page);
        $agents = $db->resultSet();
        
        // Get agent specializations for all agents
        if (!empty($agents)) {
            $agent_ids = array_column($agents, 'id');
            $agent_ids_str = implode(',', $agent_ids);
            
            $db->query("SELECT m.agent_id, s.name 
                       FROM agent_specialization_mapping m
                       JOIN agent_specializations s ON m.specialization_id = s.id
                       WHERE m.agent_id IN ({$agent_ids_str})");
            $specializations = $db->resultSet();
            
            // Group specializations by agent ID
            foreach ($specializations as $spec) {
                $agent_specializations[$spec['agent_id']][] = $spec['name'];
            }
        }
    } catch (Exception $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}


// Include header
include "header.php";
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Our Agents</h1>
        <p>Meet our team of dedicated real estate professionals ready to help you find your perfect home</p>
    </div>
</section>

<!-- Error Message (if any) -->
<?php if (!empty($error_message)): ?>
<div class="container" style="margin-top: 30px; margin-bottom: 30px;">
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
    <p>Please contact the administrator for assistance.</p>
</div>
<?php else: ?>

<!-- Agents Grid Section -->
<section class="agents-section">
    <div class="container">
        <div class="agents-grid">
            <?php if (!empty($agents)): ?>
                <?php foreach ($agents as $agent): ?>
                    <!-- Agent Card -->
                    <article class="agent-card">
                        <div class="agent-image">
                            <img src="<?php echo htmlspecialchars(getAgentImageUrl($agent['profile_image'])); ?>" 
                                 alt="<?php echo htmlspecialchars($agent['name']); ?> - Real Estate Agent"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='assets/images/agents/agent-placeholder.jpg';">
                        </div>
                        
                        <div class="agent-info">
                            <h3><?php echo htmlspecialchars($agent['name']); ?></h3>
                            
                            <?php if (!empty($agent['position'])): ?>
                            <p class="agent-position"><?php echo htmlspecialchars($agent['position']); ?></p>
                            <?php endif; ?>
                            
                            <div class="agent-contact">
                                <?php if (!empty($agent['phone'])): ?>
                                <p><i class="fas fa-phone" aria-hidden="true"></i> 
                                   <span><?php echo htmlspecialchars($agent['phone']); ?></span></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['email'])): ?>
                                <p><i class="fas fa-envelope" aria-hidden="true"></i> 
                                   <span><?php echo htmlspecialchars($agent['email']); ?></span></p>
                                <?php endif; ?>
                            </div>
                            
                            <p class="agent-description">
                                <?php 
                                $desc = !empty($agent['description']) ? $agent['description'] : 'Experienced real estate professional dedicated to helping you find your perfect property with personalized service and expert market knowledge.';
                                echo htmlspecialchars(substr($desc, 0, 150) . (strlen($desc) > 150 ? '...' : '')); 
                                ?>
                            </p>
                            
                            <?php if (!empty($agent_specializations[$agent['id']])): ?>
                            <div class="agent-specializations">
                                <i class="fas fa-star" aria-hidden="true"></i>
                                <?php 
                                $specs = array_slice($agent_specializations[$agent['id']], 0, 2); 
                                echo htmlspecialchars(implode(', ', $specs));
                                if (count($agent_specializations[$agent['id']]) > 2) {
                                    echo ' & ' . (count($agent_specializations[$agent['id']]) - 2) . ' more';
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="agent-properties">
                                <span><i class="fas fa-home" aria-hidden="true"></i> <?php echo (int)$agent['property_count']; ?>+ Properties</span>
                            </div>
                            
                            <?php 
                            $has_social = !empty($agent['facebook_url']) || !empty($agent['instagram_url']) || 
                                         !empty($agent['linkedin_url']) || !empty($agent['twitter_url']);
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
                                
                                <?php if (!empty($agent['twitter_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['twitter_url']); ?>" 
                                   target="_blank" rel="noopener noreferrer"
                                   aria-label="Visit <?php echo htmlspecialchars($agent['name']); ?>'s Twitter profile">
                                   <i class="fab fa-twitter" aria-hidden="true"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <a href="agent-details.php?id=<?php echo (int)$agent['id']; ?>" 
                               class="view-profile-btn" 
                               aria-label="View <?php echo htmlspecialchars($agent['name']); ?>'s profile">
                               <span>View Profile</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>No agents found at the moment. Please check back later or contact us for assistance.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="pagination" aria-label="Agents pagination navigation">
            <?php 
            // Previous page link
            if ($current_page > 1): ?>
                <a href="?page=<?php echo ($current_page-1); ?>" 
                   class="prev" 
                   aria-label="Go to previous page">
                   <i class="fas fa-chevron-left" aria-hidden="true"></i> <span>Previous</span>
                </a>
            <?php endif; ?>
            
            <?php
            // Page numbers with smart pagination
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            // Show first page if we're not starting from 1
            if ($start_page > 1): ?>
                <a href="?page=1" aria-label="Go to page 1">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php
            // Show page numbers
            for ($i = $start_page; $i <= $end_page; $i++): 
                $active_class = ($i == $current_page) ? 'active' : '';
                $aria_current = ($i == $current_page) ? 'aria-current="page"' : '';
            ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="<?php echo $active_class; ?>"
                   <?php echo $aria_current; ?>
                   aria-label="<?php echo ($i == $current_page) ? 'Current page, page ' . $i : 'Go to page ' . $i; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php
            // Show last page if we're not ending at the last page
            if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
                <a href="?page=<?php echo $total_pages; ?>" 
                   aria-label="Go to page <?php echo $total_pages; ?>">
                   <?php echo $total_pages; ?>
                </a>
            <?php endif; ?>
            
            <?php
            // Next page link
            if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo ($current_page+1); ?>" 
                   class="next"
                   aria-label="Go to next page">
                   <span>Next</span> <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>

<!-- Join Our Team Section -->
<section class="join-team-section">
    <div class="container">
        <div class="join-team-content">
            <div class="join-team-text">
                <div class="section-tag">
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <h2>Join Our Team of Professionals</h2>
                <p>Are you passionate about real estate and helping people find their dream homes? We're always looking for talented, dedicated individuals to join our growing team of real estate professionals.</p>
                
                <ul class="benefits-list">
                    <li><i class="fas fa-check" aria-hidden="true"></i> Competitive commission structure and bonus opportunities</li>
                    <li><i class="fas fa-check" aria-hidden="true"></i> Comprehensive training and professional development programs</li>
                    <li><i class="fas fa-check" aria-hidden="true"></i> Supportive team environment with experienced mentorship</li>
                    <li><i class="fas fa-check" aria-hidden="true"></i> Access to exclusive listings and premium marketing tools</li>
                    <li><i class="fas fa-check" aria-hidden="true"></i> Full marketing and administrative support services</li>
                </ul>
                
                <a href="mailto:careers@yourcompany.com" 
                   class="btn btn-primary"
                   aria-label="Contact us about career opportunities">
                   <span>Contact Us Today</span>
                </a>
            </div>
            
            <div class="join-team-image">
                <img src="assets/images/join-team.jpg" 
                     alt="Professional real estate team meeting" 
                     loading="lazy"
                     onerror="this.onerror=null; this.src='assets/images/placeholder-team.jpg';">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
/* =================================
   AGENTS PAGE - COMPLETE RESPONSIVE CSS
   ================================= */

/* Base Reset & Container */
* {
    box-sizing: border-box;
}

body {
    overflow-x: hidden;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
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
    margin: 0 0 1rem 0;
    font-weight: 700;
    line-height: 1.2;
}

.page-header p {
    font-size: 1.1rem;
    margin: 0;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
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
   ERROR MESSAGE STYLING
   ================================= */

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    font-size: 0.9rem;
    line-height: 1.4;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert + p {
    color: #666;
    font-size: 0.9rem;
    margin-top: 1rem;
}

/* =================================
   AGENTS SECTION
   ================================= */

.agents-section {
    padding: 3rem 0;
    background: #f8f9fa;
}

@media (min-width: 768px) {
    .agents-section {
        padding: 4rem 0;
    }
}

/* =================================
   AGENTS GRID
   ================================= */

.agents-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

@media (min-width: 480px) {
    .agents-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (min-width: 768px) {
    .agents-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
}

@media (min-width: 1024px) {
    .agents-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
}

@media (min-width: 1200px) {
    .agents-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* =================================
   AGENT CARD STYLES
   ================================= */

.agent-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}

.agent-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

/* =================================
   AGENT IMAGE STYLES
   ================================= */

.agent-image {
    width: 100%;
    height: 280px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

@media (max-width: 768px) {
    .agent-image {
        height: 250px;
    }
}

@media (max-width: 480px) {
    .agent-image {
        height: 220px;
    }
}

.agent-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    transition: transform 0.5s ease;
    display: block;
}

.agent-card:hover .agent-image img {
    transform: scale(1.05);
}

/* =================================
   AGENT INFO STYLES
   ================================= */

.agent-info {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

@media (max-width: 480px) {
    .agent-info {
        padding: 1.25rem;
    }
}

.agent-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    color: #333;
    font-weight: 600;
    line-height: 1.3;
    word-wrap: break-word;
}

@media (max-width: 480px) {
    .agent-info h3 {
        font-size: 1.1rem;
    }
}

.agent-position {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 480px) {
    .agent-position {
        font-size: 0.85rem;
    }
}

/* =================================
   AGENT CONTACT STYLES
   ================================= */

.agent-contact {
    margin-bottom: 1rem;
}

.agent-contact p {
    margin: 0.5rem 0;
    font-size: 0.9rem;
    color: #555;
    display: flex;
    align-items: center;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.agent-contact i {
    color: #007bff;
    margin-right: 0.75rem;
    width: 16px;
    flex-shrink: 0;
    font-size: 0.9rem;
}

@media (max-width: 480px) {
    .agent-contact p {
        font-size: 0.85rem;
    }
}

/* =================================
   AGENT DESCRIPTION
   ================================= */

.agent-description {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #666;
    margin-bottom: 1rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
    flex-grow: 1;
}

@media (max-width: 480px) {
    .agent-description {
        font-size: 0.85rem;
    }
}

/* =================================
   AGENT SPECIALIZATIONS
   ================================= */

.agent-specializations {
    font-size: 0.85rem;
    color: #777;
    margin-bottom: 1rem;
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #007bff;
    word-wrap: break-word;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.agent-specializations i {
    color: #ffc107;
    flex-shrink: 0;
}

@media (max-width: 480px) {
    .agent-specializations {
        font-size: 0.8rem;
    }
}

/* =================================
   AGENT PROPERTIES
   ================================= */

.agent-properties {
    margin-bottom: 1rem;
    text-align: center;
}

.agent-properties span {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

@media (max-width: 480px) {
    .agent-properties span {
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
    margin-bottom: 1.5rem;
}

.agent-social a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f5f5f5;
    color: #555;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.agent-social a::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #007bff, #0056b3);
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
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.agent-social a:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

@media (max-width: 480px) {
    .agent-social {
        gap: 0.5rem;
    }
    
    .agent-social a {
        width: 36px;
        height: 36px;
    }
    
    .agent-social a i {
        font-size: 0.8rem;
    }
}

/* =================================
   VIEW PROFILE BUTTON
   ================================= */

.view-profile-btn {
    margin-top: auto;
    width: 100%;
    text-align: center;
    padding: 0.75rem 1rem;
    border: 2px solid #007bff;
    border-radius: 8px;
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    background: white;
}

.view-profile-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    transition: left 0.3s ease;
}

.view-profile-btn span {
    position: relative;
    z-index: 2;
}

.view-profile-btn:hover::before {
    left: 0;
}

.view-profile-btn:hover {
    color: white;
    border-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

@media (max-width: 480px) {
    .view-profile-btn {
        font-size: 0.85rem;
        padding: 0.6rem 0.8rem;
    }
}

/* =================================
   NO RESULTS MESSAGE
   ================================= */

.no-results {
    text-align: center;
    padding: 3rem 1rem;
    color: #666;
    font-size: 1.1rem;
    grid-column: 1 / -1;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.no-results::before {
    content: '🏘️';
    display: block;
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* =================================
   PAGINATION STYLES
   ================================= */

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.pagination a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0.5rem 0.75rem;
    background: white;
    color: #007bff;
    text-decoration: none;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.pagination a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    transition: left 0.3s ease;
}

.pagination a span,
.pagination a i {
    position: relative;
    z-index: 2;
}

.pagination a:hover::before,
.pagination a.active::before {
    left: 0;
}

.pagination a:hover,
.pagination a.active {
    color: white;
    border-color: #007bff;
    transform: translateY(-1px);
}

.pagination a.active {
    background: #007bff;
    color: white;
}

.pagination .next,
.pagination .prev {
    gap: 0.5rem;
}

.pagination-ellipsis {
    padding: 0.5rem;
    color: #666;
}

@media (max-width: 480px) {
    .pagination {
        gap: 0.25rem;
    }
    
    .pagination a {
        min-width: 40px;
        height: 40px;
        font-size: 0.85rem;
    }
}

/* =================================
   JOIN TEAM SECTION
   ================================= */

.join-team-section {
    padding: 4rem 0;
    background: white;
    position: relative;
    overflow: hidden;
}

.join-team-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    pointer-events: none;
}

@media (max-width: 768px) {
    .join-team-section {
        padding: 3rem 0;
    }
}

.join-team-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
    align-items: center;
    position: relative;
    z-index: 2;
}

@media (min-width: 768px) {
    .join-team-content {
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
    }
}

.join-team-text {
    order: 2;
}

@media (min-width: 768px) {
    .join-team-text {
        order: 1;
    }
}

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.section-tag .dot {
    width: 12px;
    height: 12px;
    background: #007bff;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.section-tag .dot:nth-child(2) {
    animation-delay: 0.5s;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
}

.join-team-text h2 {
    font-size: 2.5rem;
    margin: 0 0 1.5rem 0;
    color: #333;
    font-weight: 700;
    line-height: 1.2;
}

@media (max-width: 768px) {
    .join-team-text h2 {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .join-team-text h2 {
        font-size: 1.75rem;
    }
}

.join-team-text p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
    line-height: 1.6;
}

@media (max-width: 480px) {
    .join-team-text p {
        font-size: 1rem;
    }
}

/* =================================
   BENEFITS LIST
   ================================= */

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0 0 2rem 0;
}

.benefits-list li {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    font-size: 1rem;
    color: #555;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.benefits-list li:last-child {
    border-bottom: none;
}

.benefits-list li:hover {
    color: #007bff;
    transform: translateX(5px);
}

.benefits-list i {
    color: #28a745;
    margin-right: 1rem;
    font-size: 1.1rem;
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 50%;
}

@media (max-width: 480px) {
    .benefits-list li {
        font-size: 0.9rem;
    }
    
    .benefits-list i {
        margin-right: 0.75rem;
    }
}

/* =================================
   JOIN TEAM BUTTON
   ================================= */

.join-team-text .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.join-team-text .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0056b3, #003d82);
    transition: left 0.3s ease;
}

.join-team-text .btn span {
    position: relative;
    z-index: 2;
}

.join-team-text .btn:hover::before {
    left: 0;
}

.join-team-text .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
}

@media (max-width: 480px) {
    .join-team-text .btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }
}

/* =================================
   JOIN TEAM IMAGE
   ================================= */

.join-team-image {
    order: 1;
    position: relative;
}

@media (min-width: 768px) {
    .join-team-image {
        order: 2;
    }
}

.join-team-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    object-position: center;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.join-team-image:hover img {
    transform: scale(1.02);
}

@media (max-width: 768px) {
    .join-team-image img {
        height: 300px;
    }
}

@media (max-width: 480px) {
    .join-team-image img {
        height: 250px;
    }
}

/* =================================
   ACCESSIBILITY & TOUCH OPTIMIZATIONS
   ================================= */

/* Focus styles */
.view-profile-btn:focus,
.agent-social a:focus,
.pagination a:focus,
.join-team-text .btn:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .agent-card:hover {
        transform: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .agent-card:hover .agent-image img {
        transform: none;
    }
    
    .agent-social a:hover {
        transform: none;
        box-shadow: none;
    }
    
    .view-profile-btn:hover {
        transform: none;
        box-shadow: none;
    }
    
    .pagination a:hover {
        transform: none;
    }
    
    .join-team-text .btn:hover {
        transform: none;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }
    
    .benefits-list li:hover {
        transform: none;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .agent-card:hover {
        transform: none;
    }
    
    .agent-image img {
        transition: none;
    }
}
</style>

<?php include 'footer.php'; ?>