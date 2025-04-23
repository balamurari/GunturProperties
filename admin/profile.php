<?php
/**
 * User Profile Page
 * Allows users to update their profile information
 */
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'My Profile';

// Get database connection
$db = new Database();

// Get user data
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect('dashboard.php');
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
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
        $db->bind(':id', $user['id']);
        if ($db->single()) {
            $errors[] = 'Email already in use';
        }
    }
    
    // Handle password change if requested
    $update_password = false;
    
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        if (empty($new_password)) {
            $errors[] = 'New password is required';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        
        if ($new_password !== $confirm_password) {
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
    
    // Process if no errors
    if (empty($errors)) {
        // Update user data
        $sql = "UPDATE users SET name = :name, email = :email, phone = :phone";
        
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
        $db->bind(':id', $user['id']);
        
        if ($update_password) {
            $db->bind(':password', password_hash($new_password, PASSWORD_DEFAULT));
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
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            if ($update_image) {
                $_SESSION['user_profile_pic'] = $upload_result['path'];
            }
            
            $success = true;
            setFlashMessage('success', 'Profile updated successfully!');
            redirect('profile.php');
        } else {
            $errors[] = 'Failed to update profile.';
        }
    }
}

// Include header
include_once 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>My Profile</h2>
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
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-image">
                    <img id="profile-preview" src="<?php echo !empty($user['profile_pic']) ? '../' . $user['profile_pic'] : '../assets/images/default-profile.jpg'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="role-badge"><?php echo ucfirst($user['role']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['phone'])): ?>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                    <p><i class="fas fa-calendar"></i> Joined <?php echo formatDate($user['created_at']); ?></p>
                </div>
            </div>
            
            <div class="profile-content">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="profile-form">
                    <h3>Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_pic">Profile Picture</label>
                        <input type="file" id="profile_pic" name="profile_pic" class="image-input" data-preview="profile-preview">
                        <small class="form-text">Allowed formats: JPG, JPEG, PNG. Max size: 2MB.</small>
                    </div>
                    
                    <h3>Change Password</h3>
                    <p class="form-text">Leave these fields empty if you don't want to change your password.</p>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}

.profile-sidebar {
    text-align: center;
}

.profile-image {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 20px;
    border: 5px solid var(--bg-light);
    box-shadow: var(--shadow);
}

.profile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h3 {
    margin-bottom: 5px;
}

.role-badge {
    display: inline-block;
    background-color: var(--primary-color);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 15px;
}

.profile-info p {
    margin-bottom: 10px;
}

.profile-info i {
    margin-right: 5px;
    color: var(--text-light);
}

.profile-form h3 {
    margin-top: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        margin-bottom: 30px;
    }
}
</style>

<script>
// Profile image preview
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
include_once 'includes/footer.php';
?>