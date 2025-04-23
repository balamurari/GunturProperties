<?php
/**
 * Edit Property Page
 * Form to edit an existing property
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

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
    // Sanitize and validate inputs
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip_code = sanitize($_POST['zip_code']);
    $bedrooms = isset($_POST['bedrooms']) ? (int)$_POST['bedrooms'] : null;
    $bathrooms = isset($_POST['bathrooms']) ? (int)$_POST['bathrooms'] : null;
    $area = isset($_POST['area']) ? (float)$_POST['area'] : null;
    $area_unit = sanitize($_POST['area_unit'] ?? 'sq ft');
    $type_id = (int)$_POST['type_id'];
    $agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    
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
    
    if (empty($state)) {
        $errors[] = 'State is required';
    }
    
    if (empty($zip_code)) {
        $errors[] = 'ZIP code is required';
    }
    
    if (!$type_id) {
        $errors[] = 'Property type is required';
    }
    
    // Process if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $db->beginTransaction();
            
            // Update property
            $db->query("UPDATE properties SET 
                        title = :title, 
                        description = :description, 
                        price = :price, 
                        address = :address, 
                        city = :city, 
                        state = :state, 
                        zip_code = :zip_code, 
                        bedrooms = :bedrooms, 
                        bathrooms = :bathrooms, 
                        area = :area, 
                        area_unit = :area_unit, 
                        type_id = :type_id, 
                        agent_id = :agent_id, 
                        featured = :featured, 
                        status = :status,
                        updated_at = NOW()
                        WHERE id = :id");
            
            $db->bind(':title', $title);
            $db->bind(':description', $description);
            $db->bind(':price', $price);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':state', $state);
            $db->bind(':zip_code', $zip_code);
            $db->bind(':bedrooms', $bedrooms);
            $db->bind(':bathrooms', $bathrooms);
            $db->bind(':area', $area);
            $db->bind(':area_unit', $area_unit);
            $db->bind(':type_id', $type_id);
            $db->bind(':agent_id', $agent_id);
            $db->bind(':featured', $featured);
            $db->bind(':status', $status);
            $db->bind(':id', $property_id);
            
            $db->execute();
            
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
                        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/guntur-properties/' . $image['image_path'];
                        if (file_exists($image_path)) {
                            unlink($image_path);
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
                        continue;
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
                        <option value="available" <?php echo $property['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="sold" <?php echo $property['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="pending" <?php echo $property['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="rented" <?php echo $property['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="agent_id">Assign Agent</label>
                    <select id="agent_id" name="agent_id">
                        <option value="">Select Agent</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" <?php echo $property['agent_id'] == $agent['id'] ? 'selected' : ''; ?>>
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
                    <label for="area">Area</label>
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
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($property['city']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State <span class="required">*</span></label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($property['state']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="zip_code">ZIP Code <span class="required">*</span></label>
                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($property['zip_code']); ?>" required>
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
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>