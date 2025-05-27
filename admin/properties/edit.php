<?php
/**
 * Edit Property Page
 * Form to edit an existing property
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Enable better debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug function
function debug_log($message) {
    error_log('[PROPERTY DEBUG] ' . $message);
}

// Set page title
$page_title = 'Edit Property';

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

debug_log("Editing property with ID: $property_id, agent_id: " . ($property['agent_id'] ? $property['agent_id'] : 'NULL'));

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
    debug_log("Form submitted: " . print_r($_POST, true));
    
    // Sanitize and validate inputs
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip_code = sanitize($_POST['zip_code']);
    $type_id = (int)$_POST['type_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    
    // Process Instagram URL and Phone Number
    $instagram_url = !empty($_POST['instagram_url']) ? sanitize($_POST['instagram_url']) : '';
    $phone_number = !empty($_POST['phone_number']) ? sanitize($_POST['phone_number']) : '';

    // Validate Instagram URL
    if (!empty($instagram_url)) {
        // Ensure it's a valid URL format
        if (!filter_var($instagram_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Instagram URL must be a valid URL';
        }
        
        // Check if the URL contains instagram.com
        if (strpos($instagram_url, 'instagram.com') === false) {
            $errors[] = 'Instagram URL must be from instagram.com';
        }
        
        // Check length - Instagram URLs shouldn't exceed 255 chars
        if (strlen($instagram_url) > 255) {
            $errors[] = 'Instagram URL is too long (max 255 characters)';
        }
    }

    // Validate Phone Number
    if (!empty($phone_number)) {
        // Strip non-numeric characters for validation
        $stripped_phone = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Check phone number length (India: typically 10 digits)
        if (strlen($stripped_phone) < 10 || strlen($stripped_phone) > 12) {
            $errors[] = 'Phone number must be 10-12 digits';
        }
        
        // Verify string length for database
        if (strlen($phone_number) > 20) {
            $errors[] = 'Phone number is too long (max 20 characters)';
        }
    }
    
    // Optional fields - careful NULL handling
    // Bedrooms
    if (isset($_POST['bedrooms']) && $_POST['bedrooms'] !== '') {
        $bedrooms = (int)$_POST['bedrooms'];
        debug_log("Bedrooms set to: $bedrooms");
    } else {
        $bedrooms = null;
        debug_log("Bedrooms set to NULL");
    }
    
    // Bathrooms
    if (isset($_POST['bathrooms']) && $_POST['bathrooms'] !== '') {
        $bathrooms = (float)$_POST['bathrooms'];
        debug_log("Bathrooms set to: $bathrooms");
    } else {
        $bathrooms = null;
        debug_log("Bathrooms set to NULL");
    }
    
    // Facing
    if (isset($_POST['facing']) && !empty($_POST['facing'])) {
        $facing = sanitize($_POST['facing']);
        debug_log("Facing set to: $facing");
    } else {
        $facing = null;
        debug_log("Facing set to NULL");
    }
    
    // Area
    if (isset($_POST['area']) && $_POST['area'] !== '') {
        $area = (float)$_POST['area'];
        debug_log("Area set to: $area");
    } else {
        $area = null;
        debug_log("Area set to NULL");
    }
    
    // Area Unit
    $area_unit = !empty($_POST['area_unit']) ? sanitize($_POST['area_unit']) : 'sq ft';
    debug_log("Area unit set to: $area_unit");
    
    // Agent ID - special handling
    if (!empty($_POST['agent_id'])) {
        $agent_id = (int)$_POST['agent_id'];
        debug_log("Agent ID from form: $agent_id");
        
        // Verify agent exists
        $db->query("SELECT id FROM agents WHERE id = :id");
        $db->bind(':id', $agent_id);
        $agent_exists = $db->single();
        
        if ($agent_exists) {
            debug_log("Agent ID set to: $agent_id (agent exists)");
        } else {
            $agent_id = null;
            debug_log("Agent ID set to NULL (agent not found)");
        }
    } else {
        $agent_id = null;
        debug_log("Agent ID set to NULL (empty selection)");
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
            
            // Improved query construction with NULL handling
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
            
            debug_log("SQL Query: " . $query);
            
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
                debug_log("Binding bedrooms: $bedrooms");
            }
            
            if ($bathrooms !== null) {
                $db->bind(':bathrooms', $bathrooms);
                debug_log("Binding bathrooms: $bathrooms");
            }
            
            if ($facing !== null) {
                $db->bind(':facing', $facing);
                debug_log("Binding facing: $facing");
            }
            
            if ($area !== null) {
                $db->bind(':area', $area);
                debug_log("Binding area: $area");
            }
            
            if ($agent_id !== null) {
                $db->bind(':agent_id', $agent_id);
            }
            
            // Execute the query
            $result = $db->execute();
            
            if (!$result) {
                throw new Exception("Failed to update property");
            }
            
            debug_log("Property updated successfully");
            
            // Handle property images
            $has_primary = false;
            $primary_image_id = isset($_POST['primary_image']) ? $_POST['primary_image'] : null;
            
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
                debug_log("Primary image set to ID: $primary_image_id");
            }
            
            // Handle new image uploads
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
                    
                    // Upload image
                    $upload_result = uploadFile($file, PROPERTY_IMG_PATH, ALLOWED_EXTENSIONS, MAX_FILE_SIZE);
                    
                    if ($upload_result) {
                        // Set as primary if no primary image yet
                        $is_primary = 0;
                        if (!$has_primary) {
                            $is_primary = 1;
                            $has_primary = true;
                            
                            // Reset all other images to non-primary
                            $db->query("UPDATE property_images SET is_primary = 0 WHERE property_id = :property_id");
                            $db->bind(':property_id', $property_id);
                            $db->execute();
                        }
                        
                        // Get highest sort order
                        $db->query("SELECT MAX(sort_order) as max_order FROM property_images WHERE property_id = :property_id");
                        $db->bind(':property_id', $property_id);
                        $max_order = $db->single()['max_order'] ?? 0;
                        
                        // Insert image
                        $db->query("INSERT INTO property_images (property_id, image_path, is_primary, sort_order) 
                                  VALUES (:property_id, :image_path, :is_primary, :sort_order)");
                        
                        $db->bind(':property_id', $property_id);
                        $db->bind(':image_path', $upload_result['path']);
                        $db->bind(':is_primary', $is_primary);
                        $db->bind(':sort_order', $max_order + 1);
                        
                        $db->execute();
                        debug_log("New image uploaded: " . $upload_result['path']);
                    } else {
                        debug_log("Failed to upload image: " . $name);
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
                        // Delete image file
                        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/gunturProperties/' . $image['image_path'];
                        if (file_exists($image_path)) {
                            unlink($image_path);
                            debug_log("Deleted image file: $image_path");
                        }
                        
                        // Delete image record
                        $db->query("DELETE FROM property_images WHERE id = :id");
                        $db->bind(':id', $image_id);
                        $db->execute();
                        debug_log("Deleted image record ID: $image_id");
                    }
                }
            }
            
            // Handle property features
            // First, delete all existing feature mappings
            $db->query("DELETE FROM property_feature_mapping WHERE property_id = :property_id");
            $db->bind(':property_id', $property_id);
            $db->execute();
            debug_log("Deleted all existing feature mappings");
            
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
                    debug_log("Added feature mapping: property $property_id, feature $feature_id, value $value");
                }
            }
            
            // Commit transaction
            $db->endTransaction();
            debug_log("Transaction committed successfully");
            
            $success = true;
            setFlashMessage('success', 'Property updated successfully!');
            redirect('index.php');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            debug_log("Error updating property: " . $e->getMessage());
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
            <h2>Edit Property</h2>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Properties
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
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $property_id); ?>" enctype="multipart/form-data">
            <!-- Basic Information -->
            <h3 class="form-section-title">Basic Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Property Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="type_id">Property Type <span class="required">*</span></label>
                    <select id="type_id" name="type_id" required>
                        <option value="">Select Property Type</option>
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo $property['type_id'] == $type['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description <span class="required">*</span></label>
                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($property['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-rupee-sign"></i>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $property['price']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>


                        <option value="buy" <?php echo $property['status'] == 'buy    ' ? 'selected' : ''; ?>>Available To Buy</option>
                        <option value="rent" <?php echo $property['status'] == 'rent' ? 'selected' : '';?>>Available To Rent</option>

                        <option value="pending" <?php echo $property['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            
                        <option value="sold" <?php echo $property['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>

                        <option value="rented" <?php echo $property['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="agent_id">Assign Agent</label>
                    <select id="agent_id" name="agent_id">
                        <option value="">Select Agent</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" 
                                    <?php echo (isset($property['agent_id']) && $property['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Property Details -->
            <h3 class="form-section-title">Property Details</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo $property['bedrooms']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" value="<?php echo $property['bathrooms']; ?>">
                </div>
                <div class="form-group">
                    <label for="facing">Facing</label>
                    <input type="text" id="facing" name="facing" value="<?php echo htmlspecialchars($property['facing']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="area">Proeprty-size</label>
                    <input type="number" id="area" name="area" min="0" step="0.01" value="<?php echo $property['area']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="area_unit">Area Unit</label>
                    <select id="area_unit" name="area_unit">
                        <option value="sq ft" <?php echo $property['area_unit'] == 'sq ft' ? 'selected' : ''; ?>>Square Feet</option>
                        <option value="sq m" <?php echo $property['area_unit'] == 'sq m' ? 'selected' : ''; ?>>Square Meters</option>
                        <option value="acres" <?php echo $property['area_unit'] == 'acres' ? 'selected' : ''; ?>>Acres</option>
                        <option value="hectares" <?php echo $property['area_unit'] == 'hectares' ? 'selected' : ''; ?>>Hectares</option>
                    </select>
                </div>
            </div>
            
            <!-- Location -->
            <h3 class="form-section-title">Location</h3>
            
            <div class="form-group">
                <label for="address">Address <span class="required">*</span></label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($property['address']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city">Locality <span class="required">*</span></label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($property['city']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State </label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($property['state']); ?>" >
                </div>
                
                <div class="form-group">
                    <label for="zip_code">ZIP Code </label>
                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($property['zip_code']); ?>" >
                </div>
            </div>
            
            <!-- Contact Information -->
            <h3 class="form-section-title">Contact Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" 
                        placeholder="e.g., +91 1234567890" 
                        pattern="[0-9+\s()-]{10,20}"
                        value="<?php echo htmlspecialchars($property['phone_number']); ?>">
                    <small class="form-text text-muted">Enter a valid phone number (10-12 digits)</small>
                </div>
                
                <div class="form-group">
                    <label for="instagram_url">Instagram Profile</label>
                    <div class="input-with-icon">
                        <i class="fab fa-instagram"></i>
                        <input type="url" id="instagram_url" name="instagram_url" 
                            placeholder="https://www.instagram.com/username/"
                            value="<?php echo htmlspecialchars($property['instagram_url']); ?>">
                    </div>
                    <small class="form-text text-muted">Enter a valid Instagram URL</small>
                </div>
            </div>
            
            <!-- Features -->
            <h3 class="form-section-title">Features</h3>
            
            <div class="features-container">
                <?php foreach ($features as $feature): ?>
                    <div class="feature-item">
                        <label>
                            <input type="checkbox" class="feature-checkbox" data-toggle="<?php echo $feature['id']; ?>" 
                                <?php echo isset($feature_values[$feature['id']]) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($feature['name']); ?>
                        </label>
                        <input type="text" name="features[<?php echo $feature['id']; ?>]" class="feature-value" 
                               id="feature-<?php echo $feature['id']; ?>" placeholder="Yes" 
                               value="<?php echo isset($feature_values[$feature['id']]) ? htmlspecialchars($feature_values[$feature['id']]) : ''; ?>" 
                               <?php echo !isset($feature_values[$feature['id']]) ? 'disabled' : ''; ?>>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Property Images -->
            <h3 class="form-section-title">Property Images</h3>
            
            <div class="image-upload-container">
                <div class="image-upload-instruction">
                    <p>Current property images. Check images to delete them. Select one image as primary.</p>
                </div>
                
                <!-- Current Images -->
                <div class="current-images-grid">
                    <?php if (!empty($property['images'])): ?>
                        <?php foreach ($property['images'] as $index => $image): ?>
                            <div class="current-image-item">
                                <div class="image-preview">
                                    <img src="../../<?php echo $image['image_path']; ?>" alt="Property Image">
                                </div>
                                <div class="image-controls">
                                    <label class="delete-label">
                                        <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>">
                                        Delete
                                    </label>
                                    <label class="primary-label">
                                        <input type="radio" name="primary_image" value="<?php echo $image['id']; ?>" <?php echo $image['is_primary'] ? 'checked' : ''; ?>>
                                        Primary
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No images for this property. Upload new ones below.</p>
                    <?php endif; ?>
                </div>
                
                <div class="image-upload-instruction">
                    <p>Upload new images for this property.</p>
                </div>
                
                <!-- New Image Upload -->
                <div class="image-upload-grid" id="imageUploadGrid">
                    <div class="image-upload-slot">
                        <input type="file" name="new_images[]" class="image-input" accept="image/jpeg, image/png, image/webp" data-preview="newPreviewImage0">
                        <div class="image-preview">
                            <img id="newPreviewImage0" src="../../assets/images/upload-placeholder.jpg" alt="Preview">
                        </div>
                    </div>
                    
                    <div class="image-upload-slot">
                        <input type="file" name="new_images[]" class="image-input" accept="image/jpeg, image/png, image/webp" data-preview="newPreviewImage1">
                        <div class="image-preview">
                            <img id="newPreviewImage1" src="../../assets/images/upload-placeholder.jpg" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Settings -->
            <h3 class="form-section-title">Additional Settings</h3>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="featured" name="featured" <?php echo $property['featured'] ? 'checked' : ''; ?>>
                <label for="featured">Mark as Featured Property</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Property</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Feature checkboxes toggle
document.querySelectorAll('.feature-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const featureId = this.getAttribute('data-toggle');
        const valueInput = document.getElementById('feature-' + featureId);
        
        if (this.checked) {
            valueInput.disabled = false;
            valueInput.focus();
        } else {
            valueInput.disabled = true;
            valueInput.value = '';
        }
    });
});

// Image preview on file selection
document.querySelectorAll('.image-input').forEach(function(input) {
    input.addEventListener('change', function() {
        const previewId = this.getAttribute('data-preview');
        const preview = document.getElementById(previewId);
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.parentElement.parentElement.classList.remove('empty');
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Debug agent ID selection
document.getElementById('agent_id').addEventListener('change', function() {
    console.log('Agent ID selected:', this.value);
});

// Validate Instagram URL and Phone Number
document.getElementById('instagram_url').addEventListener('blur', function() {
    const input = this;
    const value = input.value.trim();
    
    if (value && (!value.includes('instagram.com') || !value.startsWith('http'))) {
        input.setCustomValidity('Please enter a valid Instagram URL starting with http:// or https:// and containing instagram.com');
    } else {
        input.setCustomValidity('');
    }
});

document.getElementById('phone_number').addEventListener('blur', function() {
    const input = this;
    const value = input.value.trim();
    
    // Remove non-numeric characters for validation
    const numericValue = value.replace(/[^0-9]/g, '');
    
    if (value && (numericValue.length < 10 || numericValue.length > 12)) {
        input.setCustomValidity('Phone number must be 10-12 digits');
    } else {
        input.setCustomValidity('');
    }
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>
<style>
    .current-images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .current-image-item {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .current-image-item .image-preview {
        height: 150px;
        overflow: hidden;
    }

    .current-image-item .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .current-image-item .image-controls {
        padding: 10px;
        display: flex;
        justify-content: space-between;
        background-color: #f5f5f5;
    }

    .delete-label {
        color: var(--error-color);
    }

    .primary-label {
        color: var(--primary-color);
    }

    .features-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .feature-item {
        display: flex;
        flex-direction: column;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }

    .feature-value {
        margin-top: 5px;
    }

    .image-upload-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .image-upload-slot {
        position: relative;
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius);
        padding: 10px;
        text-align: center;
    }

    .image-upload-slot input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .image-preview {
        width: 100%;
        height: 150px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-upload-instruction {
        margin: 15px 0;
    }
</style>
