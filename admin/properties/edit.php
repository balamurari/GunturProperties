<?php
/**
 * Edit Property Page
 * Form to edit an existing property
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// --- FALLBACK CONSTANTS ---
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 10000000); // 10MB
}
if (!defined('ALLOWED_EXTENSIONS')) {
    define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
}
if (!defined('PROPERTY_IMG_PATH')) {
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        define('PROPERTY_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/assets/images/properties/');
    } else {
        define('PROPERTY_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/properties/');
    }
}

// Only admins can edit properties
requireAdmin();

// --- Helper Function for Safe HTML Escaping ---
function safeHtmlEscape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// --- Property File Upload Function ---
function uploadPropertyImage($file) {
    // Validate file upload
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false, 
            'message' => 'File upload error: ' . ($file['error'] ?? 'Unknown error')
        ];
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        return [
            'success' => false, 
            'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)
        ];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false, 
            'message' => 'File too large. Maximum size: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'
        ];
    }
    
    // Generate unique filename
    $filename = uniqid() . '.' . $file_extension;
    $destination_file = PROPERTY_IMG_PATH . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists(PROPERTY_IMG_PATH)) {
        if (!mkdir(PROPERTY_IMG_PATH, 0755, true)) {
            return [
                'success' => false, 
                'message' => 'Failed to create upload directory'
            ];
        }
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination_file)) {
        // Return relative path for database storage
        $relative_path = 'assets/images/properties/' . $filename;
        
        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'filename' => $filename,
            'path' => $relative_path,
            'full_path' => $destination_file
        ];
    } else {
        return [
            'success' => false, 
            'message' => 'Failed to move uploaded file'
        ];
    }
}

// Set page title
$page_title = 'Edit Property';

// --- CSRF Protection ---
$csrf_token = generateCSRFToken();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid property ID.');
    redirect('index.php');
}

$property_id = $_GET['id'];

// Get database connection
$db = new Database();

// Get property details
$property = getPropertyById($property_id);

if (!$property) {
    setFlashMessage('error', 'Property not found.');
    redirect('index.php');
}

// Get property types
$db->query("SELECT * FROM property_types ORDER BY name ASC");
$property_types = $db->resultSet();

// Get agents
$agents = getAgents();

// Get features
$db->query("SELECT * FROM property_features ORDER BY name ASC");
$features = $db->resultSet();

// Get property features
$db->query("SELECT feature_id, value FROM property_feature_mapping WHERE property_id = :property_id");
$db->bind(':property_id', $property_id);
$property_features = $db->resultSet();

// Convert property features to associative array
$feature_values = [];
foreach ($property_features as $feature) {
    $feature_values[$feature['feature_id']] = $feature['value'];
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- CSRF Validation ---
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Security token mismatch. Please try again.');
        redirect('edit.php?id=' . $property_id);
    }
    
    // Sanitize and validate inputs with null safety
    $title = sanitize($_POST['title'] ?? '') ?: '';
    $description = sanitize($_POST['description'] ?? '') ?: '';
    $price = sanitize($_POST['price'] ?? '') ?: '';
    $address = sanitize($_POST['address'] ?? '') ?: '';
    $city = sanitize($_POST['city'] ?? '') ?: '';
    $state = sanitize($_POST['state'] ?? '') ?: '';
    $zip_code = sanitize($_POST['zip_code'] ?? '') ?: '';
    $type_id = isset($_POST['type_id']) ? (int)$_POST['type_id'] : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? '') ?: '';
    
    // Process Instagram URL and Phone Number
    $instagram_url = filter_var($_POST['instagram_url'] ?? '', FILTER_SANITIZE_URL) ?: '';
    $phone_number = sanitize($_POST['phone_number'] ?? '') ?: '';

    // Validate Instagram URL
    if (!empty($instagram_url)) {
        if (!filter_var($instagram_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Instagram URL must be a valid URL';
        } elseif (strpos($instagram_url, 'instagram.com') === false) {
            $errors[] = 'Instagram URL must be from instagram.com';
        } elseif (strlen($instagram_url) > 255) {
            $errors[] = 'Instagram URL is too long (max 255 characters)';
        }
    }

    // Validate Phone Number
    if (!empty($phone_number)) {
        $stripped_phone = preg_replace('/[^0-9]/', '', $phone_number);
        
        if (strlen($stripped_phone) < 10 || strlen($stripped_phone) > 12) {
            $errors[] = 'Phone number must be 10-12 digits';
        } elseif (strlen($phone_number) > 20) {
            $errors[] = 'Phone number is too long (max 20 characters)';
        }
    }
    
    // Optional fields - careful NULL handling
    $bedrooms = null;
    if (isset($_POST['bedrooms']) && $_POST['bedrooms'] !== '') {
        $bedrooms = (int)$_POST['bedrooms'];
    }
    
    $bathrooms = null;
    if (isset($_POST['bathrooms']) && $_POST['bathrooms'] !== '') {
        $bathrooms = (float)$_POST['bathrooms'];
    }
    
    $facing = null;
    if (isset($_POST['facing']) && !empty($_POST['facing'])) {
        $facing = sanitize($_POST['facing']);
    }
    
    $area = null;
    if (isset($_POST['area']) && $_POST['area'] !== '') {
        $area = (float)$_POST['area'];
    }
    
    $area_unit = !empty($_POST['area_unit']) ? sanitize($_POST['area_unit']) : 'sq ft';
    
    // Agent ID - special handling
    $agent_id = null;
    if (!empty($_POST['agent_id'])) {
        $agent_id_temp = (int)$_POST['agent_id'];
        
        // Verify agent exists
        $db->query("SELECT id FROM agents WHERE id = :id");
        $db->bind(':id', $agent_id_temp);
        $agent_exists = $db->single();
        
        if ($agent_exists) {
            $agent_id = $agent_id_temp;
        }
    }
    
    // Validate required fields
    if (empty($title)) {
        $errors[] = 'Property title is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Property description is required';
    }
    
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = 'Valid property price is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Property address is required';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    
    if (!$type_id) {
        $errors[] = 'Property type is required';
    }
    
    // Process if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $db->beginTransaction();
            
            // Build the update query with proper NULL handling
            $query = "UPDATE properties SET 
                title = :title, 
                description = :description, 
                price = :price, 
                address = :address, 
                city = :city, 
                state = :state, 
                zip_code = :zip_code, 
                bedrooms = " . ($bedrooms === null ? "NULL" : ":bedrooms") . ", 
                bathrooms = " . ($bathrooms === null ? "NULL" : ":bathrooms") . ",
                facing = " . ($facing === null ? "NULL" : ":facing") . ",
                area = " . ($area === null ? "NULL" : ":area") . ", 
                area_unit = :area_unit, 
                type_id = :type_id, 
                agent_id = " . ($agent_id === null ? "NULL" : ":agent_id") . ", 
                instagram_url = :instagram_url,
                phone_number = :phone_number,
                featured = :featured, 
                status = :status,
                updated_at = NOW()
                WHERE id = :id";
            
            $db->query($query);
           
            // Bind required parameters
            $db->bind(':title', $title);
            $db->bind(':description', $description);
            $db->bind(':price', $price);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':state', $state);
            $db->bind(':zip_code', $zip_code);
            $db->bind(':area_unit', $area_unit);
            $db->bind(':type_id', $type_id);
            $db->bind(':instagram_url', $instagram_url);
            $db->bind(':phone_number', $phone_number);
            $db->bind(':featured', $featured);
            $db->bind(':status', $status);
            $db->bind(':id', $property_id);
            
            // Selectively bind optional parameters
            if ($bedrooms !== null) {
                $db->bind(':bedrooms', $bedrooms);
            }
            
            if ($bathrooms !== null) {
                $db->bind(':bathrooms', $bathrooms);
            }
            
            if ($facing !== null) {
                $db->bind(':facing', $facing);
            }
            
            if ($area !== null) {
                $db->bind(':area', $area);
            }
            
            if ($agent_id !== null) {
                $db->bind(':agent_id', $agent_id);
            }
            
            // Execute the query
            if (!$db->execute()) {
                throw new Exception("Failed to update property");
            }
            
            // Handle property images
            $has_primary = false;
            $primary_image_id = isset($_POST['primary_image']) ? $_POST['primary_image'] : null;
            $new_primary_index = isset($_POST['new_primary_image']) ? (int)$_POST['new_primary_image'] : null;
            
            // Update existing images primary status
            if ($primary_image_id) {
                $db->query("UPDATE property_images SET is_primary = CASE 
                            WHEN id = :primary_id THEN 1 
                            ELSE 0 END 
                            WHERE property_id = :property_id");
                $db->bind(':primary_id', $primary_image_id);
                $db->bind(':property_id', $property_id);
                $db->execute();
                $has_primary = true;
            }
            
            // Handle new image uploads
            $uploaded_images = [];
            if (!empty($_FILES['new_images']['name'][0])) {
                foreach ($_FILES['new_images']['name'] as $key => $name) {
                    // Skip empty files
                    if (empty($name)) {
                        continue;
                    }
                    
                    $file = [
                        'name' => $_FILES['new_images']['name'][$key],
                        'type' => $_FILES['new_images']['type'][$key],
                        'tmp_name' => $_FILES['new_images']['tmp_name'][$key],
                        'error' => $_FILES['new_images']['error'][$key],
                        'size' => $_FILES['new_images']['size'][$key]
                    ];
                    
                    // Upload image using our custom function
                    $upload_result = uploadPropertyImage($file);
                    
                    if ($upload_result['success']) {
                        // Determine if this should be primary
                        $is_primary = 0;
                        
                        // If this is the selected new primary image
                        if ($new_primary_index !== null && $key === $new_primary_index) {
                            $is_primary = 1;
                            $has_primary = true;
                            
                            // Reset all existing images to non-primary
                            $db->query("UPDATE property_images SET is_primary = 0 WHERE property_id = :property_id");
                            $db->bind(':property_id', $property_id);
                            $db->execute();
                        }
                        // If no primary is set yet and this is the first uploaded image
                        elseif (!$has_primary && empty($uploaded_images)) {
                            $is_primary = 1;
                            $has_primary = true;
                            
                            // Reset all existing images to non-primary
                            $db->query("UPDATE property_images SET is_primary = 0 WHERE property_id = :property_id");
                            $db->bind(':property_id', $property_id);
                            $db->execute();
                        }
                        
                        // Get highest sort order
                        $db->query("SELECT MAX(sort_order) as max_order FROM property_images WHERE property_id = :property_id");
                        $db->bind(':property_id', $property_id);
                        $max_order_result = $db->single();
                        $max_order = $max_order_result ? $max_order_result['max_order'] ?? 0 : 0;
                        
                        // Insert image
                        $db->query("INSERT INTO property_images (property_id, image_path, is_primary, sort_order) 
                                  VALUES (:property_id, :image_path, :is_primary, :sort_order)");
                        
                        $db->bind(':property_id', $property_id);
                        $db->bind(':image_path', $upload_result['path']);
                        $db->bind(':is_primary', $is_primary);
                        $db->bind(':sort_order', $max_order + 1);
                        
                        $db->execute();
                        
                        $uploaded_images[] = [
                            'path' => $upload_result['path'],
                            'is_primary' => $is_primary
                        ];
                    } else {
                        error_log("Failed to upload image: " . $upload_result['message']);
                    }
                }
            }
            
            // Handle deleted images
            if (!empty($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    // Get image path
                    $db->query("SELECT image_path FROM property_images WHERE id = :id AND property_id = :property_id");
                    $db->bind(':id', $image_id);
                    $db->bind(':property_id', $property_id);
                    $image = $db->single();
                    
                    if ($image) {
                        // Delete image file - try multiple paths
                        $possible_paths = [
                            $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $image['image_path'],
                            $_SERVER['DOCUMENT_ROOT'] . '/' . $image['image_path'],
                            PROPERTY_IMG_PATH . basename($image['image_path'])
                        ];
                        
                        foreach ($possible_paths as $image_path) {
                            if (file_exists($image_path)) {
                                unlink($image_path);
                                break;
                            }
                        }
                        
                        // Delete image record
                        $db->query("DELETE FROM property_images WHERE id = :id");
                        $db->bind(':id', $image_id);
                        $db->execute();
                    }
                }
            }
            
            // Handle property features
            // First, delete all existing feature mappings
            $db->query("DELETE FROM property_feature_mapping WHERE property_id = :property_id");
            $db->bind(':property_id', $property_id);
            $db->execute();
            
            // Then, add new feature mappings
            if (!empty($_POST['features'])) {
                foreach ($_POST['features'] as $feature_id => $value) {
                    if (empty($value)) {
                        $value = "Yes"; // Default value
                    }
                    
                    $db->query("INSERT INTO property_feature_mapping (property_id, feature_id, value) 
                              VALUES (:property_id, :feature_id, :value)");
                    
                    $db->bind(':property_id', $property_id);
                    $db->bind(':feature_id', $feature_id);
                    $db->bind(':value', $value);
                    
                    $db->execute();
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            setFlashMessage('success', 'Property updated successfully!');
            redirect('index.php');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            error_log("Error updating property: " . $e->getMessage());
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
                    <p class="text-muted mb-0">Edit property information and details</p>
                </div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Properties
                </a>
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
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Property</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo safeHtmlEscape($_SERVER['PHP_SELF'] . '?id=' . $property_id); ?>" enctype="multipart/form-data" id="propertyEditForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <!-- Basic Information -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                            <hr>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label">Property Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo safeHtmlEscape($property['title']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="type_id" class="form-label">Property Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type_id" name="type_id" required>
                                    <option value="">Select Property Type</option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" 
                                                <?php echo $property['type_id'] == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo safeHtmlEscape($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo safeHtmlEscape($property['description']); ?></textarea>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           step="0.01" min="0" value="<?php echo $property['price']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="buy" <?php echo $property['status'] == 'buy' ? 'selected' : ''; ?>>Available To Buy</option>
                                    <option value="rent" <?php echo $property['status'] == 'rent' ? 'selected' : ''; ?>>Available To Rent</option>
                                    <option value="pending" <?php echo $property['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="sold" <?php echo $property['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                                    <option value="rented" <?php echo $property['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="agent_id" class="form-label">Assign Agent</label>
                                <select class="form-select" id="agent_id" name="agent_id">
                                    <option value="">Select Agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>" 
                                                <?php echo (isset($property['agent_id']) && $property['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                            <?php echo safeHtmlEscape($agent['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Property Details -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-home me-2"></i>Property Details</h6>
                            <hr>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms" 
                                       min="0" value="<?php echo safeHtmlEscape($property['bedrooms']); ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" 
                                       min="0" step="0.5" value="<?php echo safeHtmlEscape($property['bathrooms']); ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="facing" class="form-label">Facing</label>
                                <input type="text" class="form-control" id="facing" name="facing" 
                                       value="<?php echo safeHtmlEscape($property['facing']); ?>"
                                       placeholder="e.g., North, South">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="area" class="form-label">Property Size</label>
                                <input type="number" class="form-control" id="area" name="area" 
                                       min="0" step="0.01" value="<?php echo safeHtmlEscape($property['area']); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="area_unit" class="form-label">Area Unit</label>
                                <select class="form-select" id="area_unit" name="area_unit">
                                    <option value="sq ft" <?php echo $property['area_unit'] == 'sq ft' ? 'selected' : ''; ?>>Square Feet</option>
                                    <option value="sq m" <?php echo $property['area_unit'] == 'sq m' ? 'selected' : ''; ?>>Square Meters</option>
                                    <option value="acres" <?php echo $property['area_unit'] == 'acres' ? 'selected' : ''; ?>>Acres</option>
                                    <option value="hectares" <?php echo $property['area_unit'] == 'hectares' ? 'selected' : ''; ?>>Hectares</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-map-marker-alt me-2"></i>Location</h6>
                            <hr>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo safeHtmlEscape($property['address']); ?>" required>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="city" class="form-label">Locality <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo safeHtmlEscape($property['city']); ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?php echo safeHtmlEscape($property['state']); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="zip_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                       value="<?php echo safeHtmlEscape($property['zip_code']); ?>">
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-phone me-2"></i>Contact Information</h6>
                            <hr>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                       placeholder="e.g., +91 1234567890" 
                                       pattern="[0-9+\s()-]{10,20}"
                                       value="<?php echo safeHtmlEscape($property['phone_number']); ?>">
                                <small class="form-text text-muted">Enter a valid phone number (10-12 digits)</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="instagram_url" class="form-label">
                                    <i class="fab fa-instagram me-1"></i>Instagram Profile
                                </label>
                                <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                       placeholder="https://www.instagram.com/username/"
                                       value="<?php echo safeHtmlEscape($property['instagram_url']); ?>">
                                <small class="form-text text-muted">Enter a valid Instagram URL</small>
                            </div>
                        </div>
                        
                        <!-- Features -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-star me-2"></i>Features</h6>
                            <hr>
                        </div>
                        
                        <div class="row mb-4">
                            <?php foreach ($features as $feature): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input feature-checkbox" 
                                               id="feature_<?php echo $feature['id']; ?>"
                                               data-toggle="<?php echo $feature['id']; ?>" 
                                               <?php echo isset($feature_values[$feature['id']]) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="feature_<?php echo $feature['id']; ?>">
                                            <?php echo safeHtmlEscape($feature['name']); ?>
                                        </label>
                                    </div>
                                    <input type="text" name="features[<?php echo $feature['id']; ?>]" 
                                           class="form-control mt-2 feature-value" 
                                           id="feature-value-<?php echo $feature['id']; ?>" 
                                           placeholder="Yes" 
                                           value="<?php echo isset($feature_values[$feature['id']]) ? safeHtmlEscape($feature_values[$feature['id']]) : ''; ?>" 
                                           <?php echo !isset($feature_values[$feature['id']]) ? 'disabled' : ''; ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Property Images -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-images me-2"></i>Property Images</h6>
                            <hr>
                        </div>
                        
                        <!-- Current Images -->
                        <div class="mb-4">
                            <h6 class="mb-3">Current Images</h6>
                            <?php if (!empty($property['images'])): ?>
                                <div class="row" id="currentImagesContainer">
                                    <?php foreach ($property['images'] as $index => $image): ?>
                                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3" id="currentImage_<?php echo $image['id']; ?>">
                                            <div class="card image-card">
                                                <div class="image-wrapper">
                                                    <img src="<?php echo getPropertyImageUrl($image['image_path']); ?>" 
                                                         class="card-img-top" alt="Property Image" 
                                                         style="height: 200px; object-fit: cover; cursor: pointer;"
                                                         data-bs-toggle="modal" data-bs-target="#imageModal" 
                                                         data-image-src="<?php echo getPropertyImageUrl($image['image_path']); ?>">
                                                    <?php if ($image['is_primary']): ?>
                                                        <div class="primary-badge">
                                                            <i class="fas fa-star"></i> Primary
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check">
                                                            <input type="radio" class="form-check-input primary-radio" 
                                                                   name="primary_image" value="<?php echo $image['id']; ?>" 
                                                                   <?php echo $image['is_primary'] ? 'checked' : ''; ?>
                                                                   id="primary_<?php echo $image['id']; ?>">
                                                            <label class="form-check-label text-success small" for="primary_<?php echo $image['id']; ?>">
                                                                Set Primary
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input delete-checkbox" 
                                                                   name="delete_images[]" value="<?php echo $image['id']; ?>"
                                                                   id="delete_<?php echo $image['id']; ?>">
                                                            <label class="form-check-label text-danger small" for="delete_<?php echo $image['id']; ?>">
                                                                Delete
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No images for this property. Upload new ones below.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- New Image Upload -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Add New Images</h6>
                                <small class="text-muted">Max 10 images total | Max <?php echo round(MAX_FILE_SIZE / 1024 / 1024, 1); ?>MB per image</small>
                            </div>
                            
                            <div class="row" id="newImagesContainer">
                                <?php for ($i = 0; $i < 3; $i++): ?>
                                    <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                                        <div class="card image-upload-card" id="uploadSlot_<?php echo $i; ?>">
                                            <div class="image-upload-wrapper">
                                                <input type="file" class="image-input" name="new_images[]" 
                                                       accept="image/jpeg,image/jpg,image/png,image/webp" 
                                                       id="newImage_<?php echo $i; ?>" 
                                                       data-preview="preview_<?php echo $i; ?>">
                                                <div class="upload-placeholder" id="placeholder_<?php echo $i; ?>">
                                                    <i class="fas fa-plus-circle fa-3x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">Click to upload</p>
                                                    <small class="text-muted">JPG, PNG, WEBP</small>
                                                </div>
                                                <div class="image-preview-wrapper" id="previewWrapper_<?php echo $i; ?>" style="display: none;">
                                                    <img id="preview_<?php echo $i; ?>" class="preview-image" alt="Preview">
                                                    <div class="image-overlay">
                                                        <button type="button" class="btn btn-sm btn-danger remove-image" 
                                                                data-slot="<?php echo $i; ?>">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <div class="form-check mt-2">
                                                            <input type="radio" class="form-check-input new-primary-radio" 
                                                                   name="new_primary_image" value="<?php echo $i; ?>" 
                                                                   id="newPrimary_<?php echo $i; ?>">
                                                            <label class="form-check-label text-white small" for="newPrimary_<?php echo $i; ?>">
                                                                Set as Primary
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tips:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Upload high-quality images for better visibility</li>
                                    <li>First image will be set as primary if no primary is selected</li>
                                    <li>Click on any image to view it in full size</li>
                                    <li>You can upload up to 10 images total</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Image Modal -->
                        <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Property Image</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img id="modalImage" src="" alt="Property Image" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Settings -->
                        <div class="section-header">
                            <h6 class="text-primary"><i class="fas fa-cog me-2"></i>Additional Settings</h6>
                            <hr>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="featured" name="featured" 
                                   <?php echo $property['featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="featured">
                                <i class="fas fa-star me-1 text-warning"></i>Mark as Featured Property
                            </label>
                            <small class="form-text text-muted d-block">Featured properties appear prominently on the website</small>
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
                                <button type="submit" class="btn btn-success btn-lg" id="updatePropertyBtn">
                                    <i class="fas fa-save me-2"></i>Update Property
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
    // Feature checkboxes toggle
    document.querySelectorAll('.feature-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const featureId = this.getAttribute('data-toggle');
            const valueInput = document.getElementById('feature-value-' + featureId);
            
            if (this.checked) {
                valueInput.disabled = false;
                valueInput.focus();
            } else {
                valueInput.disabled = true;
                valueInput.value = '';
            }
        });
    });

    // Image upload handling with preview
    document.querySelectorAll('.image-input').forEach(function(input, index) {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById('placeholder_' + index);
            const previewWrapper = document.getElementById('previewWrapper_' + index);
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Validate file size
                if (file.size > <?php echo MAX_FILE_SIZE; ?>) {
                    alert('File size too large. Maximum allowed: <?php echo round(MAX_FILE_SIZE / 1024 / 1024, 1); ?>MB');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Please select JPG, PNG, or WEBP files only.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    placeholder.style.display = 'none';
                    previewWrapper.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Remove image functionality
    document.querySelectorAll('.remove-image').forEach(function(button) {
        button.addEventListener('click', function() {
            const slotIndex = this.getAttribute('data-slot');
            const input = document.getElementById('newImage_' + slotIndex);
            const placeholder = document.getElementById('placeholder_' + slotIndex);
            const previewWrapper = document.getElementById('previewWrapper_' + slotIndex);
            const primaryRadio = document.getElementById('newPrimary_' + slotIndex);
            
            // Clear input and reset display
            input.value = '';
            placeholder.style.display = 'block';
            previewWrapper.style.display = 'none';
            primaryRadio.checked = false;
        });
    });

    // Primary image selection for existing images
    document.querySelectorAll('.primary-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Remove primary badge from all images
            document.querySelectorAll('.primary-badge').forEach(function(badge) {
                badge.style.display = 'none';
            });
            
            // Add primary badge to selected image
            if (this.checked) {
                const imageId = this.value;
                const imageCard = document.getElementById('currentImage_' + imageId);
                const badge = imageCard.querySelector('.primary-badge');
                if (badge) {
                    badge.style.display = 'block';
                } else {
                    // Create badge if it doesn't exist
                    const newBadge = document.createElement('div');
                    newBadge.className = 'primary-badge';
                    newBadge.innerHTML = '<i class="fas fa-star"></i> Primary';
                    imageCard.querySelector('.image-wrapper').appendChild(newBadge);
                }
                
                // Uncheck all new primary radios
                document.querySelectorAll('.new-primary-radio').forEach(function(newRadio) {
                    newRadio.checked = false;
                });
            }
        });
    });

    // Primary image selection for new images
    document.querySelectorAll('.new-primary-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                // Uncheck all existing primary radios
                document.querySelectorAll('.primary-radio').forEach(function(existingRadio) {
                    existingRadio.checked = false;
                });
                
                // Hide all primary badges
                document.querySelectorAll('.primary-badge').forEach(function(badge) {
                    badge.style.display = 'none';
                });
            }
        });
    });

    // Delete image confirmation
    document.querySelectorAll('.delete-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const imageCard = this.closest('.image-card');
            if (this.checked) {
                imageCard.style.opacity = '0.5';
                imageCard.style.border = '2px solid #dc3545';
                
                // Uncheck primary if this image is being deleted
                const primaryRadio = imageCard.querySelector('.primary-radio');
                if (primaryRadio && primaryRadio.checked) {
                    primaryRadio.checked = false;
                }
            } else {
                imageCard.style.opacity = '1';
                imageCard.style.border = '';
            }
        });
    });

    // Image modal functionality
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            const imageSrc = this.getAttribute('data-image-src');
            modalImage.src = imageSrc;
        });
    });

    // Form submission handler
    const form = document.getElementById('propertyEditForm');
    const updateBtn = document.getElementById('updatePropertyBtn');
    
    form.addEventListener('submit', function(e) {
        // Check if at least one image will remain (not all deleted)
        const currentImages = document.querySelectorAll('.delete-checkbox');
        const allCurrentDeleted = Array.from(currentImages).every(cb => cb.checked);
        const hasNewImages = Array.from(document.querySelectorAll('.image-input')).some(input => input.files.length > 0);
        
        if (allCurrentDeleted && !hasNewImages) {
            if (!confirm('You are deleting all existing images and not adding new ones. The property will have no images. Continue?')) {
                e.preventDefault();
                return;
            }
        }
        
        // Check for primary image selection
        const hasPrimaryExisting = Array.from(document.querySelectorAll('.primary-radio')).some(radio => radio.checked);
        const hasPrimaryNew = Array.from(document.querySelectorAll('.new-primary-radio')).some(radio => radio.checked);
        
        if (!hasPrimaryExisting && !hasPrimaryNew && (hasNewImages || !allCurrentDeleted)) {
            if (!confirm('No primary image selected. The first available image will be set as primary. Continue?')) {
                e.preventDefault();
                return;
            }
        }
        
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    });

    // Validate Instagram URL
    document.getElementById('instagram_url').addEventListener('blur', function() {
        const input = this;
        const value = input.value.trim();
        
        if (value && (!value.includes('instagram.com') || !value.startsWith('http'))) {
            input.setCustomValidity('Please enter a valid Instagram URL starting with http:// or https:// and containing instagram.com');
        } else {
            input.setCustomValidity('');
        }
    });

    // Validate phone number
    document.getElementById('phone_number').addEventListener('blur', function() {
        const input = this;
        const value = input.value.trim();
        
        const numericValue = value.replace(/[^0-9]/g, '');
        
        if (value && (numericValue.length < 10 || numericValue.length > 12)) {
            input.setCustomValidity('Phone number must be 10-12 digits');
        } else {
            input.setCustomValidity('');
        }
    });
});
</script>

<style>
.section-header {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.section-header:first-child {
    margin-top: 0;
}

.image-card {
    transition: all 0.3s ease;
    position: relative;
}

.image-wrapper {
    position: relative;
    overflow: hidden;
}

.primary-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.image-upload-card {
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
    height: 280px;
}

.image-upload-card:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.image-upload-wrapper {
    position: relative;
    height: 100%;
    cursor: pointer;
}

.image-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 3;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
}

.image-preview-wrapper {
    position: relative;
    height: 100%;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 6px;
}

.image-preview-wrapper:hover .image-overlay {
    opacity: 1;
}

.remove-image {
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-check-label.small {
    font-size: 0.875rem;
}

/* Modal styles */
.modal-lg {
    max-width: 800px;
}

#modalImage {
    max-height: 70vh;
    border-radius: 8px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-xl-3 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .col-xl-3 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .image-upload-card {
        height: 200px;
    }
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>