<?php
/**
 * Add Agent Page
 * Form to add a new agent
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Add New Agent';

// Only admins can add agents
requireAdmin();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email already exists
        $db = new Database();
        $db->query("SELECT id FROM users WHERE email = :email");
        $db->bind(':email', $email);
        if ($db->single()) {
            $errors[] = 'Email already in use';
        }
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Handle profile image upload
    $upload_result = false;
    $has_image = false;
    
    if (!empty($_FILES['profile_pic']['name'])) {
        $file = $_FILES['profile_pic'];
        $upload_result = uploadFile($file, $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/assets/images/agents/', ['jpg', 'jpeg', 'png'], 2000000);
        
        if (!$upload_result) {
            $errors[] = 'Failed to upload profile image. Please ensure it is a valid image file and size is less than 2MB.';
        } else {
            $has_image = true;
        }
    }
    
    // Process if no errors
    if (empty($errors)) {
        // Create agent
        $db = new Database();
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert agent
        $sql = "INSERT INTO users (name, email, password, phone, role, profile_pic, status, created_at) 
                VALUES (:name, :email, :password, :phone, 'agent', :profile_pic, :status, NOW())";
        
        $db->query($sql);
        $db->bind(':name', $name);
        $db->bind(':email', $email);
        $db->bind(':password', $hashed_password);
        $db->bind(':phone', $phone);
        $db->bind(':profile_pic', $has_image ? $upload_result['path'] : null);
        $db->bind(':status', $status);
        
        if ($db->execute()) {
            $success = true;
            setFlashMessage('success', 'Agent added successfully!');
            redirect('index.php');
        } else {
            $errors[] = 'Failed to add agent.';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2>Add New Agent</h2>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Agents
            </a>
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
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="profile_pic">Profile Picture</label>
                    <input type="file" id="profile_pic" name="profile_pic" class="image-input" data-preview="profile-preview">
                    <div class="image-preview-container">
                        <img id="profile-preview" src="../assets/images/default-profile.jpg" alt="Profile Preview">
                    </div>
                    <small class="form-text">Allowed formats: JPG, JPEG, PNG. Max size: 2MB.</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                    <small class="form-text">Password must be at least 6 characters long.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="status" name="status" checked>
                <label for="status">Active</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Agent</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>

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
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>