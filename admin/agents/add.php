<?php
/**
 * Admin - Add New Agent
 */

// --- Dependencies ---
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- Session & Authentication ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
requireAdmin(); // Ensure only admins can add agents

// --- Page Setup ---
$page_title = 'Add New Agent';
$db = new Database();

// --- Fetch Data for Form ---
try {
    $db->query("SELECT id, name FROM agent_specializations ORDER BY name ASC");
    $all_specializations = $db->resultSet();
} catch (Exception $e) {
    error_log("Error fetching specializations: " . $e->getMessage());
    $all_specializations = [];
    setFlashMessage('error', 'Could not load specializations list.');
}


// --- Variables for Form ---
$input_values = [
    'name' => '', 'email' => '', 'phone' => '', 'position' => 'Real Estate Agent',
    'description' => '', 'experience' => 0, 'facebook_url' => '', 'twitter_url' => '',
    'instagram_url' => '', 'linkedin_url' => '', 'youtube_url' => '', 'website_url' => '',
    'office_address' => '', 'office_hours' => '', 'featured' => 0, 'status' => 1 // Default to active
];
$selected_specializations = [];
$errors = [];

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve input data
    $input_values['name'] = sanitize($_POST['name'] ?? '');
    $input_values['email'] = sanitize($_POST['email'] ?? '');
    $input_values['phone'] = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't sanitize password before hashing
    $password_confirm = $_POST['password_confirm'] ?? '';
    $input_values['position'] = sanitize($_POST['position'] ?? 'Real Estate Agent');
    $input_values['description'] = sanitize($_POST['description'] ?? '');
    $input_values['experience'] = filter_var($_POST['experience'] ?? 0, FILTER_VALIDATE_INT, ["options" => ["min_range"=>0]]);
    $input_values['facebook_url'] = filter_var($_POST['facebook_url'] ?? '', FILTER_SANITIZE_URL);
    $input_values['twitter_url'] = filter_var($_POST['twitter_url'] ?? '', FILTER_SANITIZE_URL);
    $input_values['instagram_url'] = filter_var($_POST['instagram_url'] ?? '', FILTER_SANITIZE_URL);
    $input_values['linkedin_url'] = filter_var($_POST['linkedin_url'] ?? '', FILTER_SANITIZE_URL);
    $input_values['youtube_url'] = filter_var($_POST['youtube_url'] ?? '', FILTER_SANITIZE_URL);
    $input_values['website_url'] = filter_var($_POST['website_url'] ?? '', FILTER_SANITIZE_URL);
    $input_values['office_address'] = sanitize($_POST['office_address'] ?? '');
    $input_values['office_hours'] = sanitize($_POST['office_hours'] ?? '');
    $input_values['featured'] = isset($_POST['featured']) ? 1 : 0;
    $input_values['status'] = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $selected_specializations = isset($_POST['specializations']) && is_array($_POST['specializations']) ? $_POST['specializations'] : [];

    // --- Validation ---
    if (empty($input_values['name'])) { $errors['name'] = 'Agent name is required.'; }
    if (empty($input_values['email'])) { $errors['email'] = 'Email is required.'; }
    elseif (!filter_var($input_values['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Invalid email format.'; }
    else {
        // Check if email already exists
        $db->query("SELECT id FROM users WHERE email = :email");
        $db->bind(':email', $input_values['email']);
        if ($db->single()) {
            $errors['email'] = 'Email address is already registered.';
        }
    }
    if (empty($password)) { $errors['password'] = 'Password is required.'; }
    elseif (strlen($password) < 6) { $errors['password'] = 'Password must be at least 6 characters long.'; } // Basic length check
    if ($password !== $password_confirm) { $errors['password_confirm'] = 'Passwords do not match.'; }
    if ($input_values['experience'] === false) { $errors['experience'] = 'Experience must be a valid number (0 or more).'; }
    // Add more validation for URLs if needed

    // --- Profile Picture Upload ---
    $profile_pic_path_for_db = null; // Relative path for DB
    $new_pic_full_path = null; // Full path for potential deletion on error

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK && !empty($_FILES['profile_pic']['name'])) {
        // Use the constant defined in your config.php
        $upload_dir = AGENT_IMG_PATH; // Use AGENT_IMG_PATH directly

        // Check if defined correctly
        if (!defined('AGENT_IMG_PATH')) {
            $errors['profile_pic'] = "Upload path configuration error.";
        }
        // Make sure the directory exists and is writable
        elseif (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
            $errors['profile_pic'] = "Failed to create upload directory: " . $upload_dir;
        } elseif (!is_writable($upload_dir)){
            $errors['profile_pic'] = "Upload directory is not writable: " . $upload_dir;
        } else {
            // Use constants from config.php for validation
            $allowed_ext = ALLOWED_EXTENSIONS ?? ['jpg', 'jpeg', 'png', 'gif']; // Use defined or default
            $max_size = MAX_FILE_SIZE ?? 2 * 1024 * 1024; // Use defined or default (2MB)

            $file_tmp = $_FILES['profile_pic']['tmp_name'];
            $file_name = basename($_FILES['profile_pic']['name']);
            $file_size = $_FILES['profile_pic']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_ext)) {
                $errors['profile_pic'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_ext);
            } elseif ($file_size > $max_size) {
                $errors['profile_pic'] = 'File size exceeds limit (' . round($max_size / 1024 / 1024, 1) . 'MB).';
            } else {
                $new_filename = uniqid('agent_', true) . '.' . $file_ext;
                $destination_full_path = rtrim($upload_dir, '/') . '/' . $new_filename; // Full server path

                if (move_uploaded_file($file_tmp, $destination_full_path)) {
                    // Store RELATIVE path for DB consistency
                    $profile_pic_path_for_db = 'assets/images/agents/' . $new_filename;
                    $new_pic_full_path = $destination_full_path; // Keep track for potential deletion
                } else {
                    $errors['profile_pic'] = 'Failed to move uploaded file.';
                    error_log("Failed move_uploaded_file to: " . $destination_full_path);
                }
            }
        }
    }

    // --- If No Errors, Proceed to Database ---
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // 1. Create User Record
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $db->query("INSERT INTO users (name, email, password, phone, profile_pic, status, role, created_at, updated_at)
                        VALUES (:name, :email, :password, :phone, :profile_pic, :status, 'agent', NOW(), NOW())");

            // *** Complete Bindings for User Insert ***
            $db->bind(':name', $input_values['name']);
            $db->bind(':email', $input_values['email']);
            $db->bind(':password', $hashed_password);
            $db->bind(':phone', $input_values['phone'] ?: null); // Use null for empty phone
            $db->bind(':profile_pic', $profile_pic_path_for_db); // Bind relative path or NULL
            $db->bind(':status', $input_values['status']);
            // *** End Complete Bindings ***

            if (!$db->execute()) {
                throw new Exception("Failed to execute user creation statement.");
            }
            $new_user_id = $db->lastInsertId();

            if (!$new_user_id) { throw new Exception("Failed to get new user ID after insertion."); }

            // 2. Create Agent Record - UPDATED to use NULL for empty values
            $db->query("INSERT INTO agents (user_id, position, description, experience, facebook_url, twitter_url, instagram_url, linkedin_url, youtube_url, website_url, office_address, office_hours, featured, created_at, updated_at)
                       VALUES (:user_id, :position, :description, :experience,
                              NULLIF(:facebook_url, ''), NULLIF(:twitter_url, ''), NULLIF(:instagram_url, ''),
                              NULLIF(:linkedin_url, ''), NULLIF(:youtube_url, ''), NULLIF(:website_url, ''),
                              NULLIF(:office_address, ''), NULLIF(:office_hours, ''), 
                              :featured, NOW(), NOW())");

            $db->bind(':user_id', $new_user_id);
            $db->bind(':position', $input_values['position'] ?: 'Real Estate Agent');
            $db->bind(':description', $input_values['description'] ?: null);
            $db->bind(':experience', $input_values['experience']);
            $db->bind(':facebook_url', $input_values['facebook_url']);
            $db->bind(':twitter_url', $input_values['twitter_url']);
            $db->bind(':instagram_url', $input_values['instagram_url']);
            $db->bind(':linkedin_url', $input_values['linkedin_url']);
            $db->bind(':youtube_url', $input_values['youtube_url']);
            $db->bind(':website_url', $input_values['website_url']);
            $db->bind(':office_address', $input_values['office_address']);
            $db->bind(':office_hours', $input_values['office_hours']);
            $db->bind(':featured', $input_values['featured']);

            if (!$db->execute()) {
                throw new Exception("Failed to execute agent creation statement.");
            }
            $new_agent_id = $db->lastInsertId();

            if (!$new_agent_id) { throw new Exception("Failed to get new agent ID after insertion."); }

            // 3. Insert Specialization Mappings
            if (!empty($selected_specializations)) {
                // Prepare statement outside the loop for efficiency
                $db->query("INSERT INTO agent_specialization_mapping (agent_id, specialization_id) VALUES (:agent_id, :spec_id)");
                foreach ($selected_specializations as $spec_id) {
                    $db->bind(':agent_id', $new_agent_id);
                    $db->bind(':spec_id', (int)$spec_id); // Ensure it's an integer
                    if (!$db->execute()) {
                        throw new Exception("Failed to insert specialization mapping for spec_id: " . $spec_id);
                    }
                }
            }

            // Commit transaction if all steps succeeded
            if (!$db->endTransaction()) { // Use your commit method name
                throw new Exception("Failed to commit database transaction.");
            }

            setFlashMessage('success', 'New agent (ID: ' . $new_agent_id . ') created successfully!');
            redirect('index.php');
            exit;

        } catch (Exception $e) {
            $db->cancelTransaction(); // Use your rollback method name
            error_log("Agent creation failed: " . $e->getMessage());
            setFlashMessage('error', 'Failed to create agent. Error: ' . $e->getMessage());

            // *** Corrected Unlink Logic ***
            // Delete newly uploaded file if DB action failed, using the correct full path variable
            if ($new_pic_full_path && file_exists($new_pic_full_path)) {
                unlink($new_pic_full_path);
                error_log("Deleted uploaded file due to DB error: " . $new_pic_full_path); // Log deletion
            }
            // *** End Corrected Unlink Logic ***
        }
    } else {
        // Errors exist from validation, set flash message
        setFlashMessage('error', 'Please correct the errors highlighted below.');
    }
} // End of POST handling block

// --- Rest of your add.php file (HTML Form, includes etc.) ---
// --- Include Header ---
include_once '../includes/header.php';
?>

<div class="admin-container">
    <?php // include_once '../includes/sidebar.php'; ?>

    <div class="admin-content">
        <div class="container-fluid">
            <div class="admin-content-header d-flex justify-content-between align-items-center mb-4">
                <h2><?php echo htmlspecialchars($page_title); ?></h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Agents List
                </a>
            </div>

            <?php displayFlashMessage(); // Display general flash messages if any ?>

            <div class="card shadow-sm">
                <div class="card-header">
                     <h5 class="mb-0">Agent Details</h5>
                </div>
                <div class="card-body">
                    <form action="add.php" method="POST" enctype="multipart/form-data" novalidate>

                        <h6 class="text-primary">Login Information</h6>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($input_values['name']); ?>" required>
                                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($input_values['email']); ?>" required>
                                 <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                                <span>Password must be at least 6 characters long</span>
                                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-6">
                                <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>" id="password_confirm" name="password_confirm" required>
                                <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?php echo $errors['password_confirm']; ?></div><?php endif; ?>
                            </div>
                        </div>
                         <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($input_values['phone']); ?>">
                                <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?php echo $errors['phone']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-6">
                                <label for="profile_pic" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control <?php echo isset($errors['profile_pic']) ? 'is-invalid' : ''; ?>" id="profile_pic" name="profile_pic" accept="image/png, image/jpeg, image/gif">
                                <?php if (isset($errors['profile_pic'])): ?><div class="invalid-feedback"><?php echo $errors['profile_pic']; ?></div><?php endif; ?>
                            </div>
                        </div>

                        <h6 class="text-primary mt-4">Agent Profile Information</h6>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control <?php echo isset($errors['position']) ? 'is-invalid' : ''; ?>" id="position" name="position" value="<?php echo htmlspecialchars($input_values['position']); ?>">
                                <?php if (isset($errors['position'])): ?><div class="invalid-feedback"><?php echo $errors['position']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-6">
                                <label for="experience" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control <?php echo isset($errors['experience']) ? 'is-invalid' : ''; ?>" id="experience" name="experience" min="0" value="<?php echo htmlspecialchars($input_values['experience']); ?>">
                                <?php if (isset($errors['experience'])): ?><div class="invalid-feedback"><?php echo $errors['experience']; ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                             <label for="description" class="form-label">Description / Bio</label>
                             <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="4"><?php echo htmlspecialchars($input_values['description']); ?></textarea>
                             <?php if (isset($errors['description'])): ?><div class="invalid-feedback"><?php echo $errors['description']; ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                             <label for="specializations" class="form-label">Specializations</label>
                             <select multiple class="form-select <?php echo isset($errors['specializations']) ? 'is-invalid' : ''; ?>" id="specializations" name="specializations[]" size="5">
                                 <?php foreach ($all_specializations as $spec): ?>
                                     <option value="<?php echo $spec['id']; ?>" <?php echo in_array($spec['id'], $selected_specializations) ? 'selected' : ''; ?>>
                                         <?php echo htmlspecialchars($spec['name']); ?>
                                     </option>
                                 <?php endforeach; ?>
                             </select>
                             <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple.</small>
                             <?php if (isset($errors['specializations'])): ?><div class="invalid-feedback"><?php echo $errors['specializations']; ?></div><?php endif; ?>
                        </div>

                        <h6 class="text-primary mt-4">Contact & Social Links</h6>
                         <hr>
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="office_address" class="form-label">Office Address</label>
                                <textarea class="form-control <?php echo isset($errors['office_address']) ? 'is-invalid' : ''; ?>" id="office_address" name="office_address" rows="2"><?php echo htmlspecialchars($input_values['office_address']); ?></textarea>
                                <?php if (isset($errors['office_address'])): ?><div class="invalid-feedback"><?php echo $errors['office_address']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-6">
                                <label for="office_hours" class="form-label">Office Hours</label>
                                <input type="text" class="form-control <?php echo isset($errors['office_hours']) ? 'is-invalid' : ''; ?>" id="office_hours" name="office_hours" value="<?php echo htmlspecialchars($input_values['office_hours']); ?>" placeholder="e.g., Mon-Fri: 9 AM - 5 PM">
                                <?php if (isset($errors['office_hours'])): ?><div class="invalid-feedback"><?php echo $errors['office_hours']; ?></div><?php endif; ?>
                            </div>
                        </div>
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="website_url" class="form-label">Website URL</label>
                                <input type="url" class="form-control <?php echo isset($errors['website_url']) ? 'is-invalid' : ''; ?>" id="website_url" name="website_url" value="<?php echo htmlspecialchars($input_values['website_url']); ?>" placeholder="https://...">
                                <?php if (isset($errors['website_url'])): ?><div class="invalid-feedback"><?php echo $errors['website_url']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-6">
                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                <input type="url" class="form-control <?php echo isset($errors['facebook_url']) ? 'is-invalid' : ''; ?>" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($input_values['facebook_url']); ?>" placeholder="https://facebook.com/...">
                                 <?php if (isset($errors['facebook_url'])): ?><div class="invalid-feedback"><?php echo $errors['facebook_url']; ?></div><?php endif; ?>
                            </div>
                        </div>
                         <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control <?php echo isset($errors['twitter_url']) ? 'is-invalid' : ''; ?>" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($input_values['twitter_url']); ?>" placeholder="https://twitter.com/...">
                                <?php if (isset($errors['twitter_url'])): ?><div class="invalid-feedback"><?php echo $errors['twitter_url']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-4">
                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control <?php echo isset($errors['instagram_url']) ? 'is-invalid' : ''; ?>" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($input_values['instagram_url']); ?>" placeholder="https://instagram.com/...">
                                <?php if (isset($errors['instagram_url'])): ?><div class="invalid-feedback"><?php echo $errors['instagram_url']; ?></div><?php endif; ?>
                            </div>
                             <div class="col-md-4">
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control <?php echo isset($errors['linkedin_url']) ? 'is-invalid' : ''; ?>" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($input_values['linkedin_url']); ?>" placeholder="https://linkedin.com/in/...">
                                <?php if (isset($errors['linkedin_url'])): ?><div class="invalid-feedback"><?php echo $errors['linkedin_url']; ?></div><?php endif; ?>
                            </div>
                             </div>

                         <h6 class="text-primary mt-4">Settings</h6>
                         <hr>
                          <div class="row mb-4">
                             <div class="col-md-6">
                                 <label class="form-label">Account Status</label>
                                 <div class="form-check">
                                     <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" <?php echo ($input_values['status'] == 1) ? 'checked' : ''; ?>>
                                     <label class="form-check-label" for="statusActive">Active</label>
                                 </div>
                                 <div class="form-check">
                                     <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" <?php echo ($input_values['status'] == 0) ? 'checked' : ''; ?>>
                                     <label class="form-check-label" for="statusInactive">Inactive</label>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                <label class="form-label">Featured Agent</label>
                                 <div class="form-check form-switch">
                                     <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" <?php echo $input_values['featured'] ? 'checked' : ''; ?>>
                                     <label class="form-check-label" for="featured">Mark as featured</label>
                                 </div>
                             </div>
                         </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                             <a href="index.php" class="btn btn-secondary me-2">Cancel</a>
                             <button type="submit" class="btn btn-success">
                                 <i class="fas fa-save me-2"></i>Create Agent
                             </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include Footer
include_once '../includes/footer.php';
?>