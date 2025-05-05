<?php
/**
 * Edit Agent Page
 * Form to edit an existing agent
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Set page title
$page_title = 'Edit Agent';

// Only admins can edit agents
requireAdmin();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid agent ID.');
    redirect('index.php');
}

$agent_id = $_GET['id'];

// Get agent data
$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id AND role = 'agent'");
$db->bind(':id', $agent_id);
$agent = $db->single();

if (!$agent) {
    setFlashMessage('error', 'Agent not found.');
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
    } elseif ($email !== $agent['email']) {
        // Check if email already exists
        $db->query("SELECT id FROM users WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $agent_id);
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
        $upload_result = uploadFile($file, $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/assets/images/agents/', ['jpg', 'jpeg', 'png'], 2000000);
        
        if (!$upload_result) {
            $errors[] = 'Failed to upload profile image. Please ensure it is a valid image file and size is less than 2MB.';
        } else {
            $update_image = true;
        }
    }
    
    // Process if no errors
    if (empty($errors)) {
        // Update agent data
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
        $db->bind(':id', $agent_id);
        
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
        
        if ($db->execute()) {
            $success = true;
            setFlashMessage('success', 'Agent updated successfully!');
            redirect('index.php');
        } else {
            $errors[] = 'Failed to update agent.';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-actions">
            <h2>Edit Agent</h2>
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
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $agent_id); ?>" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($agent['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($agent['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile_pic">Profile Picture</label>
                    <input type="file" id="profile_pic" name="profile_pic" class="image-input" data-preview="profile-preview">
                    <div class="image-preview-container">
                        <img id="profile-preview" src="<?php echo !empty($agent['profile_pic']) ? '../../' . $agent['profile_pic'] : '../../assets/images/default-profile.jpg'; ?>" alt="Profile Preview">
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
                <input type="checkbox" id="status" name="status" <?php echo $agent['status'] ? 'checked' : ''; ?>>
                <label for="status">Active</label>
            </div>
            
            <!-- Display agent's property count -->
            <div class="agent-stats">
                <?php
                // Get property count
                $db->query("SELECT COUNT(*) as count FROM properties WHERE agent_id = :agent_id");
                $db->bind(':agent_id', $agent_id);
                $property_count = $db->single()['count'];
                ?>
                <p>This agent has <strong><?php echo $property_count; ?></strong> properties assigned.</p>
                <?php if ($property_count > 0): ?>
                    <a href="../properties/index.php?agent=<?php echo $agent_id; ?>" class="btn btn-outline">View Properties</a>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Agent</button>
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