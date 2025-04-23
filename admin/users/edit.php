<?php
/**
 * Edit User Page
 * Form to edit an existing admin user
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Edit Admin User';

// Only admins can edit users
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid user ID.');
    redirect('index.php');
}

$user_id = $_GET['id'];

// Get user data
$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id AND role = 'admin'");
$db->bind(':id', $user_id);
$user = $db->single();

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect('index.php');
}

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
    } elseif ($email !== $user['email']) {
        // Check if email already exists
        $db->query("SELECT id FROM users WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $user_id);
        if ($db->single()) {
            $errors[] = 'Email already in use';
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
        $upload_result = uploadFile($file, $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/assets/images/users/', ['jpg', 'jpeg', 'png'], 2000000);
        
        if (!$upload_result) {
            $errors[] = 'Failed to upload profile image. Please ensure it is a valid image file and size is less than 2MB.';
        } else {
            $update_image = true;
        }
    }
    
    // Special validation for current user
    if ($user_id == $_SESSION['user_id'] && $status == 0) {
        $errors[] = 'You cannot deactivate your own account.';
    }
    
    // Process if no errors
    if (empty($errors)) {
        // Update user data
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
        $db->bind(':id', $user_id);
        
        if ($update_password) {
            $db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
        }
        
        if ($update_image) {
            $db->bind(':profile_pic', $upload_result['path']);
            
            // Delete old profile image if exists
            if (!empty($user['profile_pic'])) {
                $old_image_path = $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/' . $user['profile_pic'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        }
        
        if ($db->execute()) {
            // Update session variables if editing current user
            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                if ($update_image) {
                    $_SESSION['user_profile_pic'] = $upload_result['path'];
                }
            }
            
            $success = true;
            setFlashMessage('success', 'User updated successfully!');
            redirect('index.php');
        } else {
            $errors[] = 'Failed to update user.';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2>Edit Admin User</h2>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Users
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
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $user_id); ?>" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile_pic">Profile Picture</label>
                    <input type="file" id="profile_pic" name="profile_pic" class="image-input" data-preview="profile-preview">
                    <div class="image-preview-container">
                        <img id="profile-preview" src="<?php echo !empty($user['profile_pic']) ? '../../' . $user['profile_pic'] : '../../assets/images/default-profile.jpg'; ?>" alt="Profile Preview">
                    </div>
                    <small class="form-text">Allowed formats: JPG, JPEG, PNG. Max size: 2MB.</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                    <small class="form-text">Leave blank to keep current password. Password must be at least 6 characters long.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="status" name="status" <?php echo $user['status'] ? 'checked' : ''; ?> <?php echo $user_id == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                <label for="status">Active</label>
                <?php if ($user_id == $_SESSION['user_id']): ?>
                    <small class="form-text">You cannot deactivate your own account.</small>
                    <input type="hidden" name="status" value="1">
                <?php endif; ?>
            </div>
            
            <?php if ($user_id == $_SESSION['user_id']): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You are editing your own account. Some restrictions apply.
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update User</button>
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