
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
        <p>Meet our team of dedicated real estate professionals ready to help you</p>
    </div>
</section>

<!-- Error Message (if any) -->
<?php if (!empty($error_message)): ?>
<div class="container" style="margin-top: 30px; margin-bottom: 30px;">
    <div class="alert alert-danger">
        <?php echo $error_message; ?>
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
                    <div class="agent-card">
                        <div class="agent-image">
                            <img src="<?php echo !empty($agent['profile_image']) ? $agent['profile_image'] : 'assets/images/agent-placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($agent['name']); ?> - Real Estate Agent">
                        </div>
                        <div class="agent-info">
                            <h3><?php echo htmlspecialchars($agent['name']); ?></h3>
                            <p class="agent-position"><?php echo htmlspecialchars($agent['position']); ?></p>
                            <div class="agent-contact">
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($agent['phone']); ?></p>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($agent['email']); ?></p>
                            </div>
                            <p class="agent-description">
                                <?php 
                                $desc = !empty($agent['description']) ? $agent['description'] : 'Experienced real estate professional ready to assist you with your property needs.';
                                echo htmlspecialchars(substr($desc, 0, 120) . (strlen($desc) > 120 ? '...' : '')); 
                                ?>
                            </p>
                            
                            <?php if (!empty($agent_specializations[$agent['id']])): ?>
                            <div class="agent-specializations">
                                <?php 
                                $specs = array_slice($agent_specializations[$agent['id']], 0, 2); 
                                echo htmlspecialchars(implode(', ', $specs));
                                if (count($agent_specializations[$agent['id']]) > 2) {
                                    echo ' & more';
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="agent-properties">
                                <span><?php echo $agent['property_count']; ?>+ Properties</span>
                            </div>
                            <div class="agent-social">
                                <?php if (!empty($agent['facebook_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['facebook_url']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['instagram_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['instagram_url']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['linkedin_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['linkedin_url']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['twitter_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['twitter_url']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                                <?php endif; ?>
                            </div>
                            <a href="agent-details.php?id=<?php echo $agent['id']; ?>" class="btn btn-outline view-profile-btn">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">No agents found. Please check back later.</div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php 
            // Previous page link
            if ($current_page > 1) {
                echo '<a href="?page='.($current_page-1).'"><i class="fas fa-chevron-left"></i> Previous</a>';
            }
            
            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                $active_class = ($i == $current_page) ? 'class="active"' : '';
                echo '<a href="?page='.$i.'" '.$active_class.'>'.$i.'</a>';
            }
            
            // Next page link
            if ($current_page < $total_pages) {
                echo '<a href="?page='.($current_page+1).'" class="next">Next <i class="fas fa-chevron-right"></i></a>';
            }
            ?>
        </div>
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
                <p>Are you passionate about real estate and helping people find their dream homes? We're always looking for talented individuals to join our team.</p>
                
                <ul class="benefits-list">
                    <li><i class="fas fa-check"></i> Competitive commission structure</li>
                    <li><i class="fas fa-check"></i> Professional development opportunities</li>
                    <li><i class="fas fa-check"></i> Supportive team environment</li>
                    <li><i class="fas fa-check"></i> Access to exclusive listings</li>
                    <li><i class="fas fa-check"></i> Marketing and administrative support</li>
                </ul>
                <a href="mailto:21jr1a43c3@gmail.com" class="btn btn-primary">Contact Us</a>
            </div>
            <div class="join-team-image">
                <img src="assets/images/join-team.jpg" alt="Join Our Team">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php include 'footer.php'; ?>