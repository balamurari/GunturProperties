<?php
/**
 * Edit Agent Profile Page
 * Form to edit an existing agent's profile information
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- FALLBACK CONSTANTS (in case config.php doesn't load properly) ---
if (!defined('AGENT_MAX_FILE_SIZE')) {
    define('AGENT_MAX_FILE_SIZE', 5000000); // 5MB for agent photos
}
if (!defined('AGENT_ALLOWED_EXTENSIONS')) {
    define('AGENT_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
}

// Set page title
$page_title = 'Edit Agent Profile';

// Only admins can edit agents
requireAdmin();

// --- Helper Function for Safe HTML Escaping ---
function safeHtmlEscape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// --- CSRF Protection ---
$csrf_token = generateCSRFToken();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid agent ID.');
    redirect('index.php');
}

$agent_id = $_GET['id'];

// Get database connection
$db = new Database();

// Get agent data with joined user info
$db->query("SELECT a.*, u.name, u.email, u.phone, u.profile_pic, u.status 
           FROM agents a 
           JOIN users u ON a.user_id = u.id 
           WHERE a.id = :id");
$db->bind(':id', $agent_id);
$agent = $db->single();

if (!$agent) {
    setFlashMessage('error', 'Agent not found.');
    redirect('index.php');
}

// Get agent's specializations
$db->query("SELECT specialization_id FROM agent_specialization_mapping WHERE agent_id = :agent_id");
$db->bind(':agent_id', $agent_id);
$agent_specializations = $db->resultSet();
$selected_specializations = array_column($agent_specializations, 'specialization_id');

// Get all available specializations
$db->query("SELECT * FROM agent_specializations ORDER BY name ASC");
$specializations = $db->resultSet();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- CSRF Validation ---
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Security token mismatch. Please try again.');
        redirect('edit.php?id=' . $agent_id);
    }
    
    // Get basic info with safe sanitization
    $name = sanitize($_POST['name'] ?? '') ?: '';
    $email = sanitize($_POST['email'] ?? '') ?: '';
    $phone = sanitize($_POST['phone'] ?? '') ?: '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Get agent specific info
    $position = sanitize($_POST['position'] ?? '') ?: '';
    $description = sanitize($_POST['description'] ?? '') ?: '';
    $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
    $specialization = sanitize($_POST['specialization'] ?? '') ?: '';
    $facebook_url = filter_var($_POST['facebook_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $twitter_url = filter_var($_POST['twitter_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $instagram_url = filter_var($_POST['instagram_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $linkedin_url = filter_var($_POST['linkedin_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $youtube_url = filter_var($_POST['youtube_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $website_url = filter_var($_POST['website_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $office_address = sanitize($_POST['office_address'] ?? '') ?: '';
    $office_hours = sanitize($_POST['office_hours'] ?? '') ?: '';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Get selected specializations
    $new_specializations = isset($_POST['specializations']) ? $_POST['specializations'] : [];
    
    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email already exists (for another user)
        $db->query("SELECT u.id FROM users u JOIN agents a ON u.id = a.user_id WHERE u.email = :email AND a.id != :agent_id");
        $db->bind(':email', $email);
        $db->bind(':agent_id', $agent_id);
        if ($db->single()) {
            $errors[] = 'Email already in use by another user';
        }
    }
    
    // Validate URLs
    $url_fields = ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'website_url'];
    foreach ($url_fields as $field) {
        $url_value = ${$field}; // Get the variable value dynamically
        if (!empty($url_value) && !filter_var($url_value, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid ' . str_replace('_', ' ', $field) . ' format.';
        }
    }
    
    // Handle password change if requested
    $update_password = false;
    if (!empty($password) || !empty($confirm_password)) {
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        $update_password = true;
    }
    
    // Handle profile image upload using optimized function
    $image_upload_result = null;
    $update_image = false;
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profile_image = $_FILES['profile_pic'];
        
        // Pre-validate image before processing
        $file_extension = strtolower(pathinfo($profile_image['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, AGENT_ALLOWED_EXTENSIONS)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', AGENT_ALLOWED_EXTENSIONS);
        } elseif ($profile_image['size'] > AGENT_MAX_FILE_SIZE) {
            $errors[] = 'File too large. Maximum size: ' . (AGENT_MAX_FILE_SIZE / 1024 / 1024) . 'MB';
        } else {
            // Use the optimized uploadAgentImage function
            $image_upload_result = uploadAgentImage($profile_image, $agent['user_id']);
            if ($image_upload_result['success']) {
                $update_image = true;
            } else {
                $errors[] = $image_upload_result['message'];
            }
        }
    }
    
    // Process if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $db->beginTransaction();
            
            // 1. Update user data
            $sql = "UPDATE users SET name = :name, email = :email, phone = :phone, status = :status";
            
            if ($update_password) {
                $sql .= ", password = :password";
            }
            
            $sql .= " WHERE id = :id";
            
            $db->query($sql);
            $db->bind(':name', $name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':status', $status);
            $db->bind(':id', $agent['user_id']);
            
            if ($update_password) {
                $db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
            }
            
            $db->execute();
            
            // 2. Update agent data
            $db->query("UPDATE agents SET 
                       position = :position,
                       description = :description,
                       experience = :experience,
                       specialization = :specialization,
                       facebook_url = :facebook_url,
                       twitter_url = :twitter_url,
                       instagram_url = :instagram_url,
                       linkedin_url = :linkedin_url,
                       youtube_url = :youtube_url,
                       website_url = :website_url,
                       office_address = :office_address,
                       office_hours = :office_hours,
                       featured = :featured
                       WHERE id = :id");
            
            $db->bind(':position', $position);
            $db->bind(':description', $description);
            $db->bind(':experience', $experience);
            $db->bind(':specialization', $specialization);
            $db->bind(':facebook_url', $facebook_url);
            $db->bind(':twitter_url', $twitter_url);
            $db->bind(':instagram_url', $instagram_url);
            $db->bind(':linkedin_url', $linkedin_url);
            $db->bind(':youtube_url', $youtube_url);
            $db->bind(':website_url', $website_url);
            $db->bind(':office_address', $office_address);
            $db->bind(':office_hours', $office_hours);
            $db->bind(':featured', $featured);
            $db->bind(':id', $agent_id);
            
            $db->execute();
            
            // 3. Update agent specializations
            // First delete all existing mappings
            $db->query("DELETE FROM agent_specialization_mapping WHERE agent_id = :agent_id");
            $db->bind(':agent_id', $agent_id);
            $db->execute();
            
            // Then add new mappings
            if (!empty($new_specializations)) {
                foreach ($new_specializations as $spec_id) {
                    if (is_numeric($spec_id)) {
                        $db->query("INSERT INTO agent_specialization_mapping (agent_id, specialization_id) VALUES (:agent_id, :spec_id)");
                        $db->bind(':agent_id', $agent_id);
                        $db->bind(':spec_id', intval($spec_id));
                        $db->execute();
                    }
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            $success_message = 'Agent profile updated successfully!';
            if ($update_image && $image_upload_result) {
                $success_message .= ' Profile picture updated.';
            }
            
            setFlashMessage('success', $success_message);
            redirect('index.php');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="admin-content-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><?php echo safeHtmlEscape($page_title); ?></h2>
                    <p class="text-muted mb-0">Edit agent profile information and settings</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Agents
                    </a>
                    <a href="agent-details.php?id=<?php echo $agent_id; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Profile
                    </a>
                </div>
            </div>

            <?php displayFlashMessage(); ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo safeHtmlEscape($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Main Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Agent Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo safeHtmlEscape($_SERVER['PHP_SELF'] . '?id=' . $agent_id); ?>" enctype="multipart/form-data" id="agentEditForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <!-- Form Tabs -->
                        <ul class="nav nav-tabs mb-4" id="agentFormTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>Basic Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab">
                                    <i class="fas fa-id-card me-2"></i>Professional Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab">
                                    <i class="fas fa-address-book me-2"></i>Contact & Social
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="agentFormTabContent">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic-tab-pane" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo safeHtmlEscape($agent['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo safeHtmlEscape($agent['email']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?php echo safeHtmlEscape($agent['phone']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="position" class="form-label">Position/Title</label>
                                        <input type="text" class="form-control" id="position" name="position" 
                                               value="<?php echo safeHtmlEscape($agent['position']); ?>"
                                               placeholder="e.g., Senior Real Estate Agent">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="profile_pic" class="form-label">
                                            Profile Picture 
                                            <small class="text-muted">(Max: <?php echo round(AGENT_MAX_FILE_SIZE / 1024 / 1024, 1); ?>MB)</small>
                                        </label>
                                        <input type="file" class="form-control" id="profile_pic" name="profile_pic" 
                                               accept="image/jpeg,image/jpg,image/png,image/webp">
                                        
                                        <!-- Current Image Preview -->
                                        <div class="mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <img id="profile-preview" 
                                                         src="<?php echo getAgentImageUrl($agent['profile_pic']); ?>" 
                                                         alt="Profile Preview" 
                                                         class="rounded-circle border" 
                                                         style="width: 100px; height: 100px; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <small class="text-muted">Current profile picture</small>
                                                    <br>
                                                    <small class="text-muted">Choose a new file to replace it</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <small class="form-text text-muted">Leave blank to keep current password. Must be 8+ characters with uppercase, lowercase, and number.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="status" name="status" 
                                                   <?php echo $agent['status'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status">
                                                <i class="fas fa-check-circle me-1 text-success"></i>Account Active
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="featured" name="featured" 
                                                   <?php echo $agent['featured'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="featured">
                                                <i class="fas fa-star me-1 text-warning"></i>Featured Agent
                                            </label>
                                            <small class="form-text text-muted d-block">Featured agents appear prominently on the website</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Professional Details Tab -->
                            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label">Bio/Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="5" 
                                                  placeholder="Tell potential clients about yourself, your background, and expertise"><?php echo safeHtmlEscape($agent['description']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="experience" class="form-label">Years of Experience</label>
                                        <input type="number" class="form-control" id="experience" name="experience" 
                                               min="0" value="<?php echo intval($agent['experience']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="specialization" class="form-label">Main Specialization</label>
                                        <input type="text" class="form-control" id="specialization" name="specialization" 
                                               value="<?php echo safeHtmlEscape($agent['specialization']); ?>"
                                               placeholder="e.g., Residential Properties, Commercial Real Estate">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Areas of Specialization</label>
                                    <div class="row">
                                        <?php foreach ($specializations as $spec): ?>
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" 
                                                           id="spec_<?php echo $spec['id']; ?>" 
                                                           name="specializations[]" 
                                                           value="<?php echo $spec['id']; ?>" 
                                                           <?php echo in_array($spec['id'], $selected_specializations) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="spec_<?php echo $spec['id']; ?>">
                                                        <?php echo safeHtmlEscape($spec['name']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact & Social Tab -->
                            <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="office_address" class="form-label">Office Address</label>
                                        <textarea class="form-control" id="office_address" name="office_address" rows="3"><?php echo safeHtmlEscape($agent['office_address']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="office_hours" class="form-label">Office Hours</label>
                                        <input type="text" class="form-control" id="office_hours" name="office_hours" 
                                               value="<?php echo safeHtmlEscape($agent['office_hours']); ?>"
                                               placeholder="e.g., Mon-Fri 9AM-5PM, Sat 10AM-2PM">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website_url" class="form-label">
                                            <i class="fas fa-globe me-1"></i>Website
                                        </label>
                                        <input type="url" class="form-control" id="website_url" name="website_url" 
                                               value="<?php echo safeHtmlEscape($agent['website_url']); ?>"
                                               placeholder="https://www.example.com">
                                    </div>
                                </div>
                                
                                <h6 class="text-primary mb-3"><i class="fas fa-share-alt me-2"></i>Social Media Profiles</h6>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="facebook_url" class="form-label">
                                            <i class="fab fa-facebook me-2 text-primary"></i>Facebook
                                        </label>
                                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                               value="<?php echo safeHtmlEscape($agent['facebook_url']); ?>"
                                               placeholder="https://facebook.com/username">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="twitter_url" class="form-label">
                                            <i class="fab fa-twitter me-2 text-info"></i>Twitter
                                        </label>
                                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                               value="<?php echo safeHtmlEscape($agent['twitter_url']); ?>"
                                               placeholder="https://twitter.com/username">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="instagram_url" class="form-label">
                                            <i class="fab fa-instagram me-2 text-danger"></i>Instagram
                                        </label>
                                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                               value="<?php echo safeHtmlEscape($agent['instagram_url']); ?>"
                                               placeholder="https://instagram.com/username">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="linkedin_url" class="form-label">
                                            <i class="fab fa-linkedin me-2 text-primary"></i>LinkedIn
                                        </label>
                                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                               value="<?php echo safeHtmlEscape($agent['linkedin_url']); ?>"
                                               placeholder="https://linkedin.com/in/username">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="youtube_url" class="form-label">
                                            <i class="fab fa-youtube me-2 text-danger"></i>YouTube
                                        </label>
                                        <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                               value="<?php echo safeHtmlEscape($agent['youtube_url']); ?>"
                                               placeholder="https://youtube.com/channel/username">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Agent Statistics -->
                        <div class="alert alert-info mt-4">
                            <?php
                            // Get property count
                            $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
                            $db->bind(':agent_id', $agent_id);
                            $property_count = $db->single()['count'];
                            ?>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fas fa-home me-2"></i>
                                    This agent has <strong><?php echo $property_count; ?></strong> properties assigned.
                                </div>
                                <?php if ($property_count > 0): ?>
                                    <a href="../properties/index.php?agent=<?php echo $agent_id; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-list me-1"></i>View Properties
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small><span class="text-danger">*</span> Required fields</small>
                            </div>
                            <div>
                                <button type="reset" class="btn btn-secondary me-2">
                                    <i class="fas fa-undo me-2"></i>Reset Changes
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="updateAgentBtn">
                                    <i class="fas fa-save me-2"></i>Update Agent Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const profilePicInput = document.getElementById('profile_pic');
    const profilePreview = document.getElementById('profile-preview');
    
    profilePicInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the target tab
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            
            // Remove active class from all tabs and tab buttons
            document.querySelectorAll('.tab-pane').forEach(function(tab) {
                tab.classList.remove('show', 'active');
            });
            
            document.querySelectorAll('.nav-link').forEach(function(link) {
                link.classList.remove('active');
            });
            
            // Add active class to clicked tab and button
            target.classList.add('show', 'active');
            this.classList.add('active');
        });
    });
    
    // Form validation
    const form = document.getElementById('agentEditForm');
    const updateBtn = document.getElementById('updateAgentBtn');
    
    form.addEventListener('submit', function(e) {
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    });
    
    // Password validation
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    function validatePasswords() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        if (password && password !== confirmPassword) {
            confirmPasswordField.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    }
    
    passwordField.addEventListener('input', validatePasswords);
    confirmPasswordField.addEventListener('input', validatePasswords);
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>