<?php
/**
 * Admin - Add New Agent (COMPLETE OPTIMIZED VERSION)
 * Uses the new optimized functions for bulletproof agent creation
 */

// --- Dependencies ---
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- Authentication & Security ---
requireAdmin(); // Ensure only admins can add agents

// --- Page Setup ---
$page_title = 'Add New Agent';
$db = new Database();

// --- CSRF Protection ---
$csrf_token = generateCSRFToken();

// --- Helper Function for Safe HTML Escaping ---
function safeHtmlEscape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// --- Fetch Data for Form ---
try {
    $db->query("SELECT id, name FROM agent_specializations ORDER BY name ASC");
    $all_specializations = $db->resultSet();
} catch (Exception $e) {
    error_log("Error fetching specializations: " . $e->getMessage());
    $all_specializations = [];
    setFlashMessage('error', 'Could not load specializations list.');
}

// --- Form Variables ---
$input_values = [
    'name' => '', 'email' => '', 'phone' => '', 'password' => '',
    'position' => 'Real Estate Agent', 'description' => '', 'experience' => 0,
    'facebook_url' => '', 'twitter_url' => '', 'instagram_url' => '',
    'linkedin_url' => '', 'youtube_url' => '', 'website_url' => '',
    'office_address' => '', 'office_hours' => '', 'featured' => 0, 'status' => 1
];
$selected_specializations = [];
$errors = [];

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- CSRF Validation ---
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Security token mismatch. Please try again.');
        redirect('add.php');
    }
    
    // --- Sanitize Input Data with null handling ---
    $input_values['name'] = sanitize($_POST['name'] ?? '') ?: '';
    $input_values['email'] = sanitize($_POST['email'] ?? '') ?: '';
    $input_values['phone'] = sanitize($_POST['phone'] ?? '') ?: '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $input_values['position'] = sanitize($_POST['position'] ?? 'Real Estate Agent') ?: 'Real Estate Agent';
    $input_values['description'] = sanitize($_POST['description'] ?? '') ?: '';
    $input_values['experience'] = filter_var($_POST['experience'] ?? 0, FILTER_VALIDATE_INT, ["options" => ["min_range"=>0]]);
    $input_values['facebook_url'] = filter_var($_POST['facebook_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $input_values['twitter_url'] = filter_var($_POST['twitter_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $input_values['instagram_url'] = filter_var($_POST['instagram_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $input_values['linkedin_url'] = filter_var($_POST['linkedin_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $input_values['youtube_url'] = filter_var($_POST['youtube_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $input_values['website_url'] = filter_var($_POST['website_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $input_values['office_address'] = sanitize($_POST['office_address'] ?? '') ?: '';
    $input_values['office_hours'] = sanitize($_POST['office_hours'] ?? '') ?: '';
    $input_values['featured'] = isset($_POST['featured']) ? 1 : 0;
    $input_values['status'] = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $selected_specializations = isset($_POST['specializations']) && is_array($_POST['specializations']) ? $_POST['specializations'] : [];

    // --- Enhanced Validation ---
    if (empty($input_values['name'])) {
        $errors['name'] = 'Agent name is required.';
    } elseif (strlen($input_values['name']) < 2) {
        $errors['name'] = 'Agent name must be at least 2 characters long.';
    }

    if (empty($input_values['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($input_values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    } else {
        // Check if email already exists
        $db->query("SELECT id FROM users WHERE email = :email");
        $db->bind(':email', $input_values['email']);
        if ($db->single()) {
            $errors['email'] = 'Email address is already registered.';
        }
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    if (!empty($input_values['phone']) && !preg_match('/^[+]?[\d\s\-\(\)]{7,15}$/', $input_values['phone'])) {
        $errors['phone'] = 'Invalid phone number format.';
    }

    if ($input_values['experience'] === false || $input_values['experience'] < 0) {
        $errors['experience'] = 'Experience must be a valid number (0 or more).';
    }

    // Validate URLs
    $url_fields = ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'website_url'];
    foreach ($url_fields as $field) {
        if (!empty($input_values[$field]) && !filter_var($input_values[$field], FILTER_VALIDATE_URL)) {
            $errors[$field] = 'Invalid URL format.';
        }
    }

    // --- Image Upload Validation (Pre-check) ---
    $profile_image = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profile_image = $_FILES['profile_pic'];
        
        // Pre-validate image before processing
        $file_extension = strtolower(pathinfo($profile_image['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, AGENT_ALLOWED_EXTENSIONS)) {
            $errors['profile_pic'] = 'Invalid file type. Allowed: ' . implode(', ', AGENT_ALLOWED_EXTENSIONS);
        } elseif ($profile_image['size'] > AGENT_MAX_FILE_SIZE) {
            $errors['profile_pic'] = 'File too large. Maximum size: ' . (AGENT_MAX_FILE_SIZE / 1024 / 1024) . 'MB';
        }
    }

    // --- Create Agent (if no validation errors) ---
    if (empty($errors)) {
        
        // Prepare data for createAgent function
        $agent_data = [
            'name' => $input_values['name'],
            'email' => $input_values['email'],
            'password' => $password,
            'phone' => $input_values['phone'],
            'position' => $input_values['position'],
            'description' => $input_values['description'],
            'experience' => $input_values['experience'],
            'office_address' => $input_values['office_address'],
            'office_hours' => $input_values['office_hours'],
            'facebook_url' => $input_values['facebook_url'],
            'twitter_url' => $input_values['twitter_url'],
            'instagram_url' => $input_values['instagram_url'],
            'linkedin_url' => $input_values['linkedin_url'],
            'youtube_url' => $input_values['youtube_url'],
            'website_url' => $input_values['website_url'],
            'featured' => $input_values['featured'],
            'status' => $input_values['status'],
            'specializations' => $selected_specializations
        ];

        // Use the optimized createAgent function
        $result = createAgent($agent_data, $profile_image);

        if ($result['success']) {
                    
            setFlashMessage('success', $result['message'] . 
                ($result['image_upload'] ? ' Profile picture uploaded successfully.' : ''));
            redirect('index.php');
        } else {
            setFlashMessage('error', $result['message']);
            $errors['general'] = $result['message'];
        }
    } else {
        setFlashMessage('error', 'Please correct the errors highlighted below.');
    }
}

// Include Header
include_once '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="admin-content-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><?php echo safeHtmlEscape($page_title); ?></h2>
                    <p class="text-muted mb-0">Create a new real estate agent account with profile and specializations</p>
                </div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Agents List
                </a>
            </div>

            <?php displayFlashMessage(); ?>

            <!-- Main Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>New Agent Information</h5>
                </div>
                <div class="card-body">
                    <form action="add.php" method="POST" enctype="multipart/form-data" novalidate id="agentForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <!-- Login Information Section -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-key me-2"></i>Login Information</h6>
                            <hr>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="name" name="name" 
                                       value="<?php echo safeHtmlEscape($input_values['name']); ?>" 
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" 
                                       value="<?php echo safeHtmlEscape($input_values['email']); ?>" 
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Must be 8+ characters with uppercase, lowercase, and number
                                </small>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback d-block"><?php echo $errors['password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirm" class="form-label">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>" 
                                       id="password_confirm" name="password_confirm" required>
                                <?php if (isset($errors['password_confirm'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['password_confirm']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                       id="phone" name="phone" 
                                       value="<?php echo safeHtmlEscape($input_values['phone']); ?>"
                                       placeholder="+91 12345 67890">
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="profile_pic" class="form-label">
                                    Profile Picture 
                                    <small class="text-muted">(Max: <?php echo AGENT_MAX_FILE_SIZE / 1024 / 1024; ?>MB)</small>
                                </label>
                                <input type="file" 
                                       class="form-control <?php echo isset($errors['profile_pic']) ? 'is-invalid' : ''; ?>" 
                                       id="profile_pic" name="profile_pic" 
                                       accept="<?php echo implode(',', array_map(function($ext) { return 'image/'.$ext; }, AGENT_ALLOWED_EXTENSIONS)); ?>">
                                <?php if (isset($errors['profile_pic'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['profile_pic']; ?></div>
                                <?php endif; ?>
                                
                                <!-- Image Preview -->
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" id="removeImage">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Agent Profile Information Section -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-id-card me-2"></i>Agent Profile Information</h6>
                            <hr>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position/Title</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="position" name="position" 
                                       value="<?php echo safeHtmlEscape($input_values['position']); ?>"
                                       placeholder="e.g., Senior Real Estate Agent">
                            </div>
                            <div class="col-md-6">
                                <label for="experience" class="form-label">Years of Experience</label>
                                <input type="number" 
                                       class="form-control <?php echo isset($errors['experience']) ? 'is-invalid' : ''; ?>" 
                                       id="experience" name="experience" 
                                       min="0" max="50"
                                       value="<?php echo safeHtmlEscape($input_values['experience']); ?>">
                                <?php if (isset($errors['experience'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['experience']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description / Bio</label>
                            <textarea class="form-control" 
                                      id="description" name="description" 
                                      rows="4" 
                                      placeholder="Brief description about the agent, their expertise, and achievements..."><?php echo safeHtmlEscape($input_values['description']); ?></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/500 characters
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="specializations" class="form-label">Specializations</label>
                            <select multiple class="form-select" id="specializations" name="specializations[]" size="6">
                                <?php foreach ($all_specializations as $spec): ?>
                                    <option value="<?php echo $spec['id']; ?>" 
                                            <?php echo in_array($spec['id'], $selected_specializations) ? 'selected' : ''; ?>>
                                        <?php echo safeHtmlEscape($spec['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Hold Ctrl (or Cmd on Mac) to select multiple specializations.
                            </small>
                        </div>

                        <!-- Contact & Office Information Section -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-building me-2"></i>Contact & Office Information</h6>
                            <hr>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="office_address" class="form-label">Office Address</label>
                                <textarea class="form-control" 
                                          id="office_address" name="office_address" 
                                          rows="3"
                                          placeholder="Complete office address..."><?php echo safeHtmlEscape($input_values['office_address']); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="office_hours" class="form-label">Office Hours</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="office_hours" name="office_hours" 
                                       value="<?php echo safeHtmlEscape($input_values['office_hours']); ?>" 
                                       placeholder="e.g., Mon-Fri: 9 AM - 6 PM, Sat: 10 AM - 4 PM">
                            </div>
                        </div>

                        <!-- Social Links Section -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-share-alt me-2"></i>Social Media & Website</h6>
                            <hr>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="website_url" class="form-label">
                                    <i class="fas fa-globe me-1"></i>Website URL
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($errors['website_url']) ? 'is-invalid' : ''; ?>" 
                                       id="website_url" name="website_url" 
                                       value="<?php echo safeHtmlEscape($input_values['website_url']); ?>" 
                                       placeholder="https://www.example.com">
                                <?php if (isset($errors['website_url'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['website_url']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="facebook_url" class="form-label">
                                    <i class="fab fa-facebook me-1"></i>Facebook URL
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($errors['facebook_url']) ? 'is-invalid' : ''; ?>" 
                                       id="facebook_url" name="facebook_url" 
                                       value="<?php echo safeHtmlEscape($input_values['facebook_url']); ?>" 
                                       placeholder="https://facebook.com/username">
                                <?php if (isset($errors['facebook_url'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['facebook_url']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="twitter_url" class="form-label">
                                    <i class="fab fa-twitter me-1"></i>Twitter URL
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($errors['twitter_url']) ? 'is-invalid' : ''; ?>" 
                                       id="twitter_url" name="twitter_url" 
                                       value="<?php echo safeHtmlEscape($input_values['twitter_url']); ?>" 
                                       placeholder="https://twitter.com/username">
                                <?php if (isset($errors['twitter_url'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['twitter_url']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="instagram_url" class="form-label">
                                    <i class="fab fa-instagram me-1"></i>Instagram URL
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($errors['instagram_url']) ? 'is-invalid' : ''; ?>" 
                                       id="instagram_url" name="instagram_url" 
                                       value="<?php echo safeHtmlEscape($input_values['instagram_url']); ?>" 
                                       placeholder="https://instagram.com/username">
                                <?php if (isset($errors['instagram_url'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['instagram_url']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="linkedin_url" class="form-label">
                                    <i class="fab fa-linkedin me-1"></i>LinkedIn URL
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($errors['linkedin_url']) ? 'is-invalid' : ''; ?>" 
                                       id="linkedin_url" name="linkedin_url" 
                                       value="<?php echo safeHtmlEscape($input_values['linkedin_url']); ?>" 
                                       placeholder="https://linkedin.com/in/username">
                                <?php if (isset($errors['linkedin_url'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['linkedin_url']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- YouTube URL Field -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="youtube_url" class="form-label">
                                    <i class="fab fa-youtube me-1"></i>YouTube URL
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($errors['youtube_url']) ? 'is-invalid' : ''; ?>" 
                                       id="youtube_url" name="youtube_url" 
                                       value="<?php echo safeHtmlEscape($input_values['youtube_url']); ?>" 
                                       placeholder="https://youtube.com/channel/username">
                                <?php if (isset($errors['youtube_url'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['youtube_url']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Settings Section -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-cog me-2"></i>Account Settings</h6>
                            <hr>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Account Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" 
                                           <?php echo ($input_values['status'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-success" for="statusActive">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" 
                                           <?php echo ($input_values['status'] == 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-warning" for="statusInactive">
                                        <i class="fas fa-pause-circle me-1"></i>Inactive
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Featured Agent</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="featured" name="featured" value="1" 
                                           <?php echo $input_values['featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        <i class="fas fa-star me-1"></i>Mark as featured agent
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Featured agents appear prominently on the website
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small><span class="text-danger">*</span> Required fields</small>
                            </div>
                            <div>
                                <a href="index.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="fas fa-user-plus me-2"></i>Create Agent
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Enhanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const profilePicInput = document.getElementById('profile_pic');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');
    
    profilePicInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    removeImageBtn.addEventListener('click', function() {
        profilePicInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    });
    
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
    
    // Character counter for description
    const descriptionInput = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    
    descriptionInput.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        charCount.parentElement.className = count > 500 ? 'form-text text-danger' : 'form-text text-muted';
    });
    
    // Real-time password validation
    const passwordField = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    
    function validatePassword() {
        const password = passwordField.value;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasLength = password.length >= 8;
        
        const isValid = hasUpper && hasLower && hasNumber && hasLength;
        
        if (password && !isValid) {
            passwordField.classList.add('is-invalid');
        } else {
            passwordField.classList.remove('is-invalid');
        }
        
        // Check password match
        if (passwordConfirm.value && password !== passwordConfirm.value) {
            passwordConfirm.classList.add('is-invalid');
        } else {
            passwordConfirm.classList.remove('is-invalid');
        }
    }
    
    passwordField.addEventListener('input', validatePassword);
    passwordConfirm.addEventListener('input', validatePassword);
    
    // Form submission handling
    const form = document.getElementById('agentForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Agent...';
    });
    
    // Prevent double submission
    let isSubmitting = false;
    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        isSubmitting = true;
    });
});
</script>

<!-- Enhanced Styling -->
<style>
.section-header {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.section-header:first-child {
    margin-top: 0;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-label i {
    color: #6c757d;
}

.input-group .btn {
    border-color: #ced4da;
}

#imagePreview {
    padding: 10px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    text-align: center;
    background-color: #f8f9fa;
}

#imagePreview img {
    border-radius: 8px;
}

.form-check-label i {
    width: 16px;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-weight: 600;
}

.card-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.invalid-feedback {
    font-size: 0.875rem;
}

.text-danger {
    color: #dc3545 !important;
}

.text-success {
    color: #198754 !important;
}

.text-warning {
    color: #ffc107 !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .admin-content-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-content-header .btn {
        margin-top: 1rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: stretch;
    }
    
    .d-flex.justify-content-between > div:last-child {
        margin-top: 1rem;
        text-align: center;
    }
}
</style>

<?php
// Include Footer
include_once '../includes/footer.php';
?>