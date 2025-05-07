<?php
/**
 * Edit Agent Profile Page
 * Form to edit an existing agent's profile information
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Edit Agent Profile';

// Only admins can edit agents
requireAdmin();

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
    // Get basic info
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Get agent specific info
    $position = sanitize($_POST['position']);
    $description = sanitize($_POST['description']);
    $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
    $specialization = sanitize($_POST['specialization'] ?? '');
    $facebook_url = sanitize($_POST['facebook_url']);
    $twitter_url = sanitize($_POST['twitter_url']);
    $instagram_url = sanitize($_POST['instagram_url']);
    $linkedin_url = sanitize($_POST['linkedin_url']);
    $youtube_url = sanitize($_POST['youtube_url']);
    $website_url = sanitize($_POST['website_url']);
    $office_address = sanitize($_POST['office_address']);
    $office_hours = sanitize($_POST['office_hours']);
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
    
    // Handle password change if requested
    $update_password = false;
    if (!empty($password) || !empty($confirm_password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        $update_password = true;
    }
    
    // Handle profile image upload
    $upload_result = false;
    $update_image = false;
    
    if (!empty($_FILES['profile_pic']['name'])) {
        $file = $_FILES['profile_pic'];
        $upload_result = uploadFile($file, AGENT_IMG_PATH, ALLOWED_EXTENSIONS, MAX_FILE_SIZE);
        
        if (!$upload_result) {
            $errors[] = 'Failed to upload profile image. Please ensure it is a valid image file and size is less than 2MB.';
        } else {
            $update_image = true;
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
            
            if ($update_image) {
                $sql .= ", profile_pic = :profile_pic";
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
            
            if ($update_image) {
                $db->bind(':profile_pic', $upload_result['path']);
                
                // Delete old profile image if exists
                if (!empty($agent['profile_pic'])) {
                    $old_image_path = $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $agent['profile_pic'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
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
                    $db->query("INSERT INTO agent_specialization_mapping (agent_id, specialization_id) VALUES (:agent_id, :spec_id)");
                    $db->bind(':agent_id', $agent_id);
                    $db->bind(':spec_id', $spec_id);
                    $db->execute();
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            setFlashMessage('success', 'Agent profile updated successfully!');
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

<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2>Edit Agent Profile</h2>
            <div>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Agents
                </a>
                <a href="agent-details.php?id=<?php echo $agent_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View Profile
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $agent_id); ?>" enctype="multipart/form-data" id="agentEditForm">            <div class="form-tabs">
                <ul class="nav nav-tabs" id="agentFormTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane" type="button" role="tab" aria-controls="basic-tab-pane" aria-selected="true">Basic Information</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Professional Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Contact & Social</button>
                    </li>
                </ul>
                
                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="agentFormTabContent">
                    <!-- Basic Information Tab -->
                    <div class="tab-pane fade show active" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="required">*</span></label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($agent['name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($agent['phone']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Position/Title</label>
                                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($agent['position']); ?>">
                                    <small class="form-text">e.g., "Senior Real Estate Agent", "Property Consultant"</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="profile_pic">Profile Picture</label>
                                    <input type="file" id="profile_pic" name="profile_pic" class="image-input" data-preview="profile-preview">
                                    <div class="image-preview-container">
                                        <img id="profile-preview" src="<?php echo !empty($agent['profile_pic']) ? '../../' . $agent['profile_pic'] : '../../assets/images/default-profile.jpg'; ?>" alt="Profile Preview" class="rounded-circle">
                                    </div>
                                    <small class="form-text">Allowed formats: JPG, JPEG, PNG. Max size: 2MB.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password">
                                    <small class="form-text">Leave blank to keep current password. Password must be at least 6 characters long.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="status" name="status" <?php echo $agent['status'] ? 'checked' : ''; ?>>
                                    <label for="status">Account Active</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="featured" name="featured" <?php echo $agent['featured'] ? 'checked' : ''; ?>>
                                    <label for="featured">Featured Agent</label>
                                    <small class="form-text d-block">Featured agents appear on the homepage and top of listings</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Details Tab -->
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Bio/Description</label>
                                    <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($agent['description']); ?></textarea>
                                    <small class="form-text">Tell potential clients about yourself, your background, and expertise</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="experience">Years of Experience</label>
                                    <input type="number" id="experience" name="experience" min="0" value="<?php echo (int)$agent['experience']; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="specialization">Main Specialization</label>
                                    <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($agent['specialization']); ?>">
                                    <small class="form-text">e.g., "Residential Properties", "Commercial Real Estate"</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Areas of Specialization</label>
                                    <div class="specialization-grid">
                                        <?php foreach ($specializations as $spec): ?>
                                            <div class="specialization-item">
                                                <input type="checkbox" id="spec_<?php echo $spec['id']; ?>" name="specializations[]" value="<?php echo $spec['id']; ?>" <?php echo in_array($spec['id'], $selected_specializations) ? 'checked' : ''; ?>>
                                                <label for="spec_<?php echo $spec['id']; ?>"><?php echo htmlspecialchars($spec['name']); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact & Social Tab -->
                    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="office_address">Office Address</label>
                                    <textarea id="office_address" name="office_address" rows="3"><?php echo htmlspecialchars($agent['office_address']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="office_hours">Office Hours</label>
                                    <input type="text" id="office_hours" name="office_hours" value="<?php echo htmlspecialchars($agent['office_hours']); ?>">
                                    <small class="form-text">e.g., "Mon-Fri 9AM-5PM, Sat 10AM-2PM"</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website_url">Website</label>
                                    <input type="url" id="website_url" name="website_url" value="<?php echo htmlspecialchars($agent['website_url']); ?>">
                                </div>
                            </div>
                            
                            <h5 class="mt-4">Social Media Profiles</h5>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook_url"><i class="fab fa-facebook me-2"></i>Facebook</label>
                                    <input type="url" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($agent['facebook_url']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="twitter_url"><i class="fab fa-twitter me-2"></i>Twitter</label>
                                    <input type="url" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($agent['twitter_url']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instagram_url"><i class="fab fa-instagram me-2"></i>Instagram</label>
                                    <input type="url" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($agent['instagram_url']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="linkedin_url"><i class="fab fa-linkedin me-2"></i>LinkedIn</label>
                                    <input type="url" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($agent['linkedin_url']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="youtube_url"><i class="fab fa-youtube me-2"></i>YouTube</label>
                                    <input type="url" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($agent['youtube_url']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Display agent's property count -->
            <div class="agent-stats mt-4 p-3 bg-light rounded">
                <?php
                // Get property count
                $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
                $db->bind(':agent_id', $agent_id);
                $property_count = $db->single()['count'];
                ?>
                <p><i class="fas fa-home me-2"></i>This agent has <strong><?php echo $property_count; ?></strong> properties assigned.</p>
                <?php if ($property_count > 0): ?>
                    <a href="../properties/index.php?agent=<?php echo $agent_id; ?>" class="btn btn-outline">
                        <i class="fas fa-list"></i> View Agent's Properties
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary" id="updateAgentBtn">
                    <i class="fas fa-save me-2"></i>Update Agent Profile
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo me-2"></i>Reset Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.image-preview-container {
    width: 150px;
    height: 150px;
    overflow: hidden;
    margin: 10px 0;
    border-radius: 50%;
    border: 3px solid var(--border-color);
}

#profile-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.specialization-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.specialization-item {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.specialization-item input[type="checkbox"] {
    margin-right: 8px;
}

/* Bootstrap-like tabs styling if not using Bootstrap */
.nav-tabs {
    display: flex;
    flex-wrap: wrap;
    border-bottom: 1px solid #dee2e6;
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
}

.nav-item {
    margin-bottom: -1px;
}

.nav-link {
    display: block;
    padding: 0.5rem 1rem;
    color: #495057;
    text-decoration: none;
    background-color: transparent;
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
    cursor: pointer;
}

.nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-content {
    background-color: #fff;
}

.tab-pane {
    display: none;
}

.tab-pane.active,
.tab-pane.show {
    display: block;
}

.fade {
    transition: opacity 0.15s linear;
}

.fade:not(.show) {
    opacity: 0;
}
</style>

<script>
    // Add this to your script section
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handler
    const form = document.querySelector('form');
    const updateBtn = document.getElementById('updateAgentBtn');
    
    if (updateBtn) {
        updateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Validate form fields here if needed
            
            // Submit the form
            form.submit();
        });
    }
    
    // Backup direct form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            // You can add additional validation here if needed
            // If you don't need validation, you can remove this listener
            console.log('Form submitted');
        });
    }
});
</script>
<script>

// Image preview
document.getElementById('profile_pic').addEventListener('change', function() {
    const preview = document.getElementById('profile-preview');
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        
        reader.readAsDataURL(this.files[0]);
    }
});

// Tab functionality if not using Bootstrap
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>